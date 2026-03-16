<?php

/*
 *
 *   _____       _                          _
 *  / ____|     | |                        (_)
 * | (___  _   _| |__  _ __ ___   __ _ _ __ _ _ __   ___
 *  \___ \| | | | '_ \| '_ ` _ \ / _` | '__| | '_ \ / _ \
 *  ____) | |_| | |_) | | | | | | (_| | |  | | | | |  __/
 * |_____/ \__,_|_.__/|_| |_| |_|\__,_|_|  |_|_| |_|\___|
 *
 * This program is private software. No license required.
 * Publication of this program is forbidden and will be punished.
 *
 * @author SEMENNEJO
 * @link vk.com/vk.snikers && t.me/semennejo
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe;

use InvalidArgumentException;
use pocketmine\entity\passive\AbstractHorse;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\ActorFallPacket;
use pocketmine\network\mcpe\protocol\ActorPickRequestPacket;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\BlockPickRequestPacket;
use pocketmine\network\mcpe\protocol\BookEditPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\ClientCacheStatusPacket;
use pocketmine\network\mcpe\protocol\ClientToServerHandshakePacket;
use pocketmine\network\mcpe\protocol\CommandBlockUpdatePacket;
use pocketmine\network\mcpe\protocol\CommandRequestPacket;
use pocketmine\network\mcpe\protocol\CommandStepPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\ContainerSetSlotPacket;
use pocketmine\network\mcpe\protocol\CraftingEventPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\DropItemPacket;
use pocketmine\network\mcpe\protocol\EmoteListPacket;
use pocketmine\network\mcpe\protocol\EmotePacket;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\ItemFrameDropItemPacket;
use pocketmine\network\mcpe\protocol\ItemStackRequestPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacketV1;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\MapInfoRequestPacket;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\NetworkStackLatencyPacket;
use pocketmine\network\mcpe\protocol\PacketViolationWarningPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\PlayerHotbarPacket;
use pocketmine\network\mcpe\protocol\PlayerInputPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\RemoveBlockPacket;
use pocketmine\network\mcpe\protocol\RequestAbilityPacket;
use pocketmine\network\mcpe\protocol\RequestChunkRadiusPacket;
use pocketmine\network\mcpe\protocol\RequestNetworkSettingsPacket;
use pocketmine\network\mcpe\protocol\ResourcePackChunkRequestPacket;
use pocketmine\network\mcpe\protocol\ResourcePackClientResponsePacket;
use pocketmine\network\mcpe\protocol\RespawnPacket;
use pocketmine\network\mcpe\protocol\RiderJumpPacket;
use pocketmine\network\mcpe\protocol\ServerSettingsRequestPacket;
use pocketmine\network\mcpe\protocol\SetActorMotionPacket;
use pocketmine\network\mcpe\protocol\SetLocalPlayerAsInitializedPacket;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;
use pocketmine\network\mcpe\protocol\ShowCreditsPacket;
use pocketmine\network\mcpe\protocol\SpawnExperienceOrbPacket;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\network\mcpe\protocol\TickSyncPacket;
use pocketmine\network\mcpe\protocol\types\InputMode;
use pocketmine\network\mcpe\protocol\UseItemPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\timings\Timings;
use RuntimeException;

use function bin2hex;
use function implode;
use function is_bool;
use function json_decode;
use function json_last_error_msg;
use function microtime;
use function preg_match;
use function strlen;
use function substr;
use function trim;

class PlayerNetworkSessionAdapter extends NetworkSession
{
	private Server $server;
	private Player $player;

	private float $lastRightClickBlock = 0;

	protected ?string $lastRequestedFullSkinId = null;

	public function __construct(Server $server, Player $player)
	{
		$this->server = $server;
		$this->player = $player;
	}

	public function isCompressionEnabled() : bool
	{
		return $this->player->hasNetworkCompression();
	}

	public function getProtocol() : int
	{
		return $this->player->getProtocolVersion();
	}

	public function handleDataPacket(DataPacket $packet) : void
	{
		if (!$this->player->isConnected()) {
			return;
		}

		if ($this->player->getProtocolVersion() < ProtocolInfo::PROTOCOL_137) {
			//TODO: Remove this hack once InteractPacket spam issue is fixed
			if ($packet->buffer === "\x21\x04\x00") {
				return;
			}
		}

		if (
			!$this->player->loggedIn &&
			!$this->player->awaitingEncryptionHandshake &&
			!(
				$packet instanceof LoginPacket ||
				$packet instanceof RequestNetworkSettingsPacket
			)
		) { //Ignore any packets before login
			return;
		}

		$timings = Timings::getReceiveDataPacketTimings($packet);
		$timings->startTiming();

		try {
			if (!$packet->wasDecoded && $packet->mustBeDecoded()) { //Allow plugins to decode it
				$decodeTimings = Timings::getDecodeDataPacketTimings($packet);
				$decodeTimings->startTiming();
				try {
					$packet->decode();
					if (!$packet->feof() && !$packet->mayHaveUnreadBytes()) {
						$remains = substr($packet->buffer, $packet->offset);
						$this->server->getLogger()->debug("Still " . strlen($remains) . " bytes unread in " . $packet->getName() . ": 0x" . bin2hex($remains));
					}
				} finally {
					$decodeTimings->stopTiming();
				}
			}

			$ev = new DataPacketReceiveEvent($this->player, $packet);
			$ev->call();
			if ($ev->isCancelled() || !$packet->mustBeDecoded()) {
				return;
			}

			$handlerTimings = Timings::getHandleDataPacketTimings($packet);
			$handlerTimings->startTiming();
			try {
				if (!$packet->handle($this)) {
					$this->server->getLogger()->debug("Unhandled " . $packet->getName() . " received from " . $this->player->getName() . ": 0x" . bin2hex($packet->buffer));
				}
			} finally {
				$handlerTimings->stopTiming();
			}
		} finally {
			$timings->stopTiming();
		}
	}

	public function handleRequestNetworkSettings(RequestNetworkSettingsPacket $packet) : bool
	{
		return $this->player->handleRequestNetworkSettings($packet);
	}

	public function handleLogin(LoginPacket $packet) : bool
	{
		return $this->player->handleLogin($packet);
	}

	public function handleClientToServerHandshake(ClientToServerHandshakePacket $packet) : bool
	{
		return $this->player->onEncryptionHandshake();
	}

	public function handleResourcePackClientResponse(ResourcePackClientResponsePacket $packet) : bool
	{
		return $this->player->handleResourcePackClientResponse($packet);
	}

	public function handleText(TextPacket $packet) : bool
	{
		if ($packet->type === TextPacket::TYPE_CHAT) {
			return $this->player->chat($packet->message);
		}

		return false;
	}

	/*
	 * A similar code design was taken from PlayerAuthInputPacket
	 */
	public function handleMovePlayer(MovePlayerPacket $packet) : bool
	{
		$this->player->updateNextPosition($packet->position, $packet->yaw, $packet->yaw, $packet->pitch);

		return true;
	}

	public function handlePlayerAuthInput(PlayerAuthInputPacket $packet) : bool
	{
		return $this->player->handlePlayerAuthInput($packet);
	}

	public function handleLevelSoundEventPacketV1(LevelSoundEventPacketV1 $packet) : bool
	{
		return true; //useless leftover from 1.8
	}

	public function handleActorEvent(ActorEventPacket $packet) : bool
	{
		return $this->player->handleEntityEvent($packet);
	}

	public function handleInventoryTransaction(InventoryTransactionPacket $packet) : bool
	{
		return $this->player->handleInventoryTransaction($packet);
	}

	public function handleItemStackRequest(ItemStackRequestPacket $packet) : bool
	{
		return $this->player->handleItemStackRequest($packet);
	}

	public function handleMobEquipment(MobEquipmentPacket $packet) : bool
	{
		return $this->player->handleMobEquipment($packet);
	}

	public function handleMobArmorEquipment(MobArmorEquipmentPacket $packet) : bool
	{
		return true; //Not used
	}

	public function handleTickSync(TickSyncPacket $packet) : bool
	{
		return true; //Not used
	}

	public function handleEmoteList(EmoteListPacket $packet) : bool
	{
		return true; // Not used
	}

	public function handleEmote(EmotePacket $packet) : bool
	{
		return $this->player->handleEmote($packet);
	}

	public function handleInteract(InteractPacket $packet) : bool
	{
		return $this->player->handleInteract($packet);
	}

	public function handleBlockPickRequest(BlockPickRequestPacket $packet) : bool
	{
		return $this->player->pickBlock(new Vector3($packet->blockX, $packet->blockY, $packet->blockZ), $packet->addUserData);
	}

	public function handleActorPickRequest(ActorPickRequestPacket $packet) : bool
	{
		return $this->player->pickEntity($packet->entityUniqueId);
	}

	public function handlePlayerAction(PlayerActionPacket $packet) : bool
	{
		return $this->player->handlePlayerAction($packet);
	}

	public function handleActorFall(ActorFallPacket $packet) : bool
	{
		return true; //Not used
	}

	public function handleAnimate(AnimatePacket $packet) : bool
	{
		return $this->player->handleAnimate($packet);
	}

	public function handleRespawn(RespawnPacket $packet) : bool
	{
		return $this->player->handleRespawn($packet);
	}

	public function handleContainerClose(ContainerClosePacket $packet) : bool
	{
		return $this->player->handleContainerClose($packet);
	}

	public function handlePlayerHotbar(PlayerHotbarPacket $packet) : bool
	{
		return true; //this packet is useless
	}

	public function handleCraftingEvent(CraftingEventPacket $packet) : bool
	{
		if ($this->player->getProtocolVersion() < ProtocolInfo::PROTOCOL_137) {
			return $this->player->handleCraftingEvent($packet); // only <= 1.1
		}

		return true;
	}

	public function handleClientCacheStatus(ClientCacheStatusPacket $packet) : bool
	{
		return true; // Not used
	}

	public function handleAdventureSettings(AdventureSettingsPacket $packet) : bool
	{
		return $this->player->handleAdventureSettings($packet);
	}

	public function handleBlockActorData(BlockActorDataPacket $packet) : bool
	{
		return $this->player->handleBlockEntityData($packet);
	}

	public function handlePlayerInput(PlayerInputPacket $packet) : bool
	{
		$this->player->setMoveForward($packet->motionY);
		$this->player->setMoveStrafing($packet->motionX);

		return true;
	}

	public function handleRiderJump(RiderJumpPacket $packet) : bool
	{
		if ($this->player->isRiding()) {
			$horse = $this->player->getRidingEntity();
			if ($horse instanceof AbstractHorse) {
				$horse->setJumpPower($packet->jumpStrength);

				return true;
			}
		}
		return false;
	}

	public function handleSetPlayerGameType(SetPlayerGameTypePacket $packet) : bool
	{
		return $this->player->handleSetPlayerGameType($packet);
	}

	public function handleSpawnExperienceOrb(SpawnExperienceOrbPacket $packet) : bool
	{
		return false; //TODO
	}

	public function handleMapInfoRequest(MapInfoRequestPacket $packet) : bool
	{
		return $this->player->handleMapInfoRequest($packet);
	}

	public function handleRequestChunkRadius(RequestChunkRadiusPacket $packet) : bool
	{
		if (!$this->player->loginProcessed) {
			return false;
		}

		$this->player->setViewDistance($packet->radius);

		return true;
	}

	public function handleItemFrameDropItem(ItemFrameDropItemPacket $packet) : bool
	{
		return $this->player->handleItemFrameDropItem($packet);
	}

	public function handleBossEvent(BossEventPacket $packet) : bool
	{
		return false; //TODO
	}

	public function handleShowCredits(ShowCreditsPacket $packet) : bool
	{
		return false; //TODO: handle resume
	}

	public function handleCommandRequest(CommandRequestPacket $packet) : bool
	{
		return $this->player->chat($packet->command);
	}

	public function handleCommandBlockUpdate(CommandBlockUpdatePacket $packet) : bool
	{
		return false; //TODO
	}

	public function handleCommandStep(CommandStepPacket $packet) : bool
	{
		return $this->player->handleCommandStep($packet);
	}

	public function handleContainerSetSlot(ContainerSetSlotPacket $packet) : bool
	{
		return $this->player->handleContainerSetSlot($packet);
	}

	public function handleDropItem(DropItemPacket $packet) : bool
	{
		return $this->player->handleDropItem($packet);
	}

	public function handleRemoveBlock(RemoveBlockPacket $packet) : bool
	{
		if (!$this->player->spawned || !$this->player->isAlive()) {
			return true;
		}

		return $this->player->removeBlock(new Vector3($packet->x, $packet->y, $packet->z));
	}

	public function handleUseItem(UseItemPacket $packet) : bool
	{
		if (!$this->player->spawned || !$this->player->isAlive()) {
			return true;
		}

		$blockVector = new Vector3($packet->x, $packet->y, $packet->z);

		$item = TypeConverter::getInstance()->netItemStackToCore($packet->item, $this->player->getProtocolVersion());
		if ($packet->face === -1) {
			if (microtime(true) - $this->lastRightClickBlock > 0.005) {
				$this->player->useItem($blockVector, $packet->clickPos, $item, $packet->face);
			}
		} else {
			$this->lastRightClickBlock = microtime(true);
			$this->player->useItem($blockVector, $packet->clickPos, $item, $packet->face);

			if ($this->player->getCurrentInputMode() !== InputMode::TOUCHSCREEN) { //this is a very nasty hack
				$this->player->useItem($blockVector, $packet->clickPos, $item, -1);
			}
		}

		return true;
	}

	public function handleResourcePackChunkRequest(ResourcePackChunkRequestPacket $packet) : bool
	{
		return $this->player->handleResourcePackChunkRequest($packet);
	}

	public function handlePlayerSkin(PlayerSkinPacket $packet) : bool
	{
		$skin = $packet->skin;

		if ($skin->getSerializedSkin()->getFullSkinId() === $this->lastRequestedFullSkinId) {
			//TODO: HACK! In 1.19.60, the client sends its skin back to us if we sent it a skin different from the one
			//it's using. We need to prevent this from causing a feedback loop.
			$this->server->getLogger()->debug("Refused duplicate skin change request");
			return true;
		}
		$this->lastRequestedFullSkinId = $skin->getSerializedSkin()->getFullSkinId();

		if (!$skin->isValid()) {
			return false;
		}

		$this->server->getLogger()->debug("Processing skin change request for " . $this->player->getName());
		return $this->player->changeSkin($skin, $packet->newSkinName, $packet->oldSkinName);
	}

	public function handleBookEdit(BookEditPacket $packet) : bool
	{
		return $this->player->handleBookEdit($packet);
	}

	public function handleModalFormResponse(ModalFormResponsePacket $packet) : bool
	{
		if ($packet->cancelReason !== null) {
			//TODO: make APIs for this to allow plugins to use this information
			return $this->player->onFormSubmit($packet->formId, null);
		} elseif ($packet->formData !== null) {
			return $this->player->onFormSubmit($packet->formId, self::stupid_json_decode($packet->formData, true));
		} else {
			throw new RuntimeException("Expected either formData or cancelReason to be set in ModalFormResponsePacket");
		}
	}

	/**
	 * Hack to work around a stupid bug in Minecraft W10 which causes empty strings to be sent unquoted in form responses.
	 *
	 * @return mixed
	 */
	private static function stupid_json_decode(string $json, bool $assoc = false)
	{
		if (preg_match('/^\[(.+)\]$/s', $json, $matches) > 0) {
			$raw = $matches[1];
			$lastComma = -1;
			$newParts = [];
			$quoteType = null;
			for ($i = 0, $len = strlen($raw); $i <= $len; ++$i) {
				if ($i === $len || ($raw[$i] === "," && $quoteType === null)) {
					$part = substr($raw, $lastComma + 1, $i - ($lastComma + 1));
					if (trim($part) === "") { //regular parts will have quotes or something else that makes them non-empty
						$part = '""';
					}
					$newParts[] = $part;
					$lastComma = $i;
				} elseif ($raw[$i] === '"') {
					if ($quoteType === null) {
						$quoteType = $raw[$i];
					} elseif ($raw[$i] === $quoteType) {
						for ($backslashes = 0; $backslashes < $i && $raw[$i - $backslashes - 1] === "\\"; ++$backslashes) {
						}
						if (($backslashes % 2) === 0) { //unescaped quote
							$quoteType = null;
						}
					}
				}
			}

			$fixed = "[" . implode(",", $newParts) . "]";
			if (($ret = json_decode($fixed, $assoc)) === null) {
				throw new InvalidArgumentException("Failed to fix JSON: " . json_last_error_msg() . "(original: $json, modified: $fixed)");
			}

			return $ret;
		}

		return json_decode($json, $assoc);
	}

	public function handleServerSettingsRequest(ServerSettingsRequestPacket $packet) : bool
	{
		return true; //TODO: GUI stuff
	}

	public function handleSetLocalPlayerAsInitialized(SetLocalPlayerAsInitializedPacket $packet) : bool
	{
		$this->player->doFirstSpawn();
		return true;
	}

	public function handleLevelSoundEvent(LevelSoundEventPacket $packet) : bool
	{
		/*
		* We don't handle this - all sounds are handled by the server now.
		* However, some plugins find this useful to detect events like left-click-air, which doesn't have any other
		* action bound to it.
		* In addition, we use this handler to silence debug noise, since this packet is frequently sent by the client.
		*/
		return true;
	}

	public function handleMoveActorAbsolute(MoveActorAbsolutePacket $packet) : bool
	{
		$target = $this->player->getServer()->findEntity($packet->entityRuntimeId);
		if ($target !== null) {
			$target->setClientPositionAndRotation($packet->position, $packet->yaw, $packet->pitch, 3, ($packet->flags & MoveActorAbsolutePacket::FLAG_TELEPORT) !== 0);
			//$target->onGround = ($packet->flags & MoveActorAbsolutePacket::FLAG_GROUND) !== 0;

			return true;
		}

		return false;
	}

	public function handleSetActorMotion(SetActorMotionPacket $packet) : bool
	{
		return true;
	}

	public function handleNetworkStackLatency(NetworkStackLatencyPacket $packet) : bool
	{
		return true; //TODO: implement this properly - this is here to silence debug spam from MCPE dev builds
	}

	public function handleRequestAbility(RequestAbilityPacket $packet) : bool
	{
		if ($packet->getAbilityId() === RequestAbilityPacket::ABILITY_FLYING) {
			$isFlying = $packet->getAbilityValue();
			if (!is_bool($isFlying)) {
				return false;
			}

			if ($isFlying !== $this->player->isFlying()) {
				if (!$this->player->toggleFlight($isFlying)) {
					$this->player->sendAbilities();
				}
			}

			return true;
		}

		return false;
	}

	public function handlePacketViolationWarning(PacketViolationWarningPacket $packet) : bool
	{
		$this->player->getServer()->getLogger()->notice("PacketViolationWarning from {$this->player->getName()}: (type=" . $packet->getType() . ",severity=" . $packet->getSeverity() . ",packetId=" . $packet->getPacketId() . ",violationContext=" . $packet->getMessage() . ")");
		return true;
	}
}
