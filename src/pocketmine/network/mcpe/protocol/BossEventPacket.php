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

namespace pocketmine\network\mcpe\protocol;

use pocketmine\network\mcpe\convert\ConstantTranslator;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\BossBarColor;

class BossEventPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::BOSS_EVENT_PACKET;

	/* S2C: Shows the boss-bar to the player. */
	public const TYPE_SHOW = 0;
	/* C2S: Registers a player to a boss fight. */
	public const TYPE_REGISTER_PLAYER = 1;
	/* S2C: Removes the boss-bar from the client. */
	public const TYPE_HIDE = 2;
	/* C2S: Unregisters a player from a boss fight. */
	public const TYPE_UNREGISTER_PLAYER = 3;
	/* S2C: Sets the bar percentage. */
	public const TYPE_HEALTH_PERCENT = 4;
	/* S2C: Sets title of the bar. */
	public const TYPE_TITLE = 5;
	/* S2C: Not sure on this. Includes color and overlay fields, plus an unknown short. TODO: check this */
	public const TYPE_PROPERTIES = 6;
	/* S2C: Not implemented :( Intended to alter bar appearance, but these currently produce no effect on client-side whatsoever. */
	public const TYPE_TEXTURE = 7;
	/* C2S: Client asking the server to resend all boss data. */
	public const TYPE_QUERY = 8;

	public int $bossEid;
	public int $eventType;

	public int $playerEid;
	public float $healthPercent;
	public string $title;
	public string $filteredTitle = "";
	public bool $darkenScreen;
	public int $color;
	public int $overlay;

	protected function decodePayload() : void
	{
		$this->bossEid = $this->getEntityUniqueId();
		$this->eventType = $this->getUnsignedVarInt();
		switch ($this->eventType) {
			case self::TYPE_REGISTER_PLAYER:
			case self::TYPE_UNREGISTER_PLAYER:
			case self::TYPE_QUERY:
				$this->playerEid = $this->getEntityUniqueId();
				break;
				/** @noinspection PhpMissingBreakStatementInspection */
			case self::TYPE_SHOW:
				$this->title = $this->getString();
				if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_776) {
					$this->filteredTitle = $this->getString();
				}
				$this->healthPercent = $this->getLFloat();
				/** @noinspection PhpMissingBreakStatementInspection */
				// no break
			case self::TYPE_PROPERTIES:
				$this->darkenScreen = match($raw = $this->getLShort()) {
					0 => false,
					1 => true,
					default => throw new PacketDecodeException("Invalid darkenScreen value $raw"),
				};
				// no break
			case self::TYPE_TEXTURE:
				$this->color = ConstantTranslator::getInstance()->fromNetworkId(BossBarColor::class, $this->getUnsignedVarInt(), $this->getProtocol());
				$this->overlay = $this->getUnsignedVarInt();
				break;
			case self::TYPE_HEALTH_PERCENT:
				$this->healthPercent = $this->getLFloat();
				break;
			case self::TYPE_TITLE:
				$this->title = $this->getString();
				if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_776) {
					$this->filteredTitle = $this->getString();
				}
				break;
			default:
				break;
		}
	}

	protected function encodePayload() : void
	{
		$this->putEntityUniqueId($this->bossEid);
		$this->putUnsignedVarInt($this->eventType);
		switch ($this->eventType) {
			case self::TYPE_REGISTER_PLAYER:
			case self::TYPE_UNREGISTER_PLAYER:
			case self::TYPE_QUERY:
				$this->putEntityUniqueId($this->playerEid);
				break;
				/** @noinspection PhpMissingBreakStatementInspection */
			case self::TYPE_SHOW:
				$this->putString($this->title);
				if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_776) {
					$this->putString($this->filteredTitle);
				}
				$this->putLFloat($this->healthPercent);
				/** @noinspection PhpMissingBreakStatementInspection */
				// no break
			case self::TYPE_PROPERTIES:
				$this->putLShort($this->darkenScreen ? 1 : 0);
				// no break
			case self::TYPE_TEXTURE:
				$this->putUnsignedVarInt(ConstantTranslator::getInstance()->toNetworkId(BossBarColor::class, $this->color, $this->getProtocol(), BossBarColor::PURPLE));
				$this->putUnsignedVarInt($this->overlay);
				break;
			case self::TYPE_HEALTH_PERCENT:
				$this->putLFloat($this->healthPercent);
				break;
			case self::TYPE_TITLE:
				$this->putString($this->title);
				if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_776) {
					$this->putString($this->filteredTitle);
				}
				break;
			default:
				break;
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleBossEvent($this);
	}
}
