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

use pocketmine\entity\Skin;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\utils\UUID;

class PlayerSkinPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::PLAYER_SKIN_PACKET;

	public UUID $uuid;
	public string $oldSkinName = "";
	public string $newSkinName = "";
	public ?Skin $skin = null;
	public bool $premiumSkin = false;

	protected function decodePayload() : void
	{
		$this->uuid = $this->getUUID();

		if ($this->getProtocol() < ProtocolInfo::PROTOCOL_370) {
			$skinId = $this->getString();
			$this->newSkinName = $this->getString();
			$this->oldSkinName = $this->getString();
			$skinData = $this->getString();
			$capeData = "";
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_137) {
				$capeData = $this->getString();
			}
			$geometryModel = $this->getString();
			$geometryData = $this->getString();

			$this->skin = new Skin($skinId, $skinData, $capeData, $geometryModel, $geometryData);

			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_261 && !$this->feof()) {
				$this->premiumSkin = $this->getBool();
			}
		} else {
			$this->skin = $this->getSkin($this->getProtocol());
			$this->newSkinName = $this->getString();
			$this->oldSkinName = $this->getString();
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_390) {
				$this->getBool(); //TODO: trustedSkin
			}
		}
	}

	protected function encodePayload() : void
	{
		$this->putUUID($this->uuid);

		if ($this->getProtocol() < ProtocolInfo::PROTOCOL_370) {
			$this->putString($this->skin->getSkinId());
			$this->putString($this->newSkinName);
			$this->putString($this->oldSkinName);
			$skinData = $this->skin->getClientFriendlySkinData($this->getProtocol());
			$this->putString($skinData);
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_137) {
				$this->putString($this->skin->getCapeData());
			}
			$this->putString($this->skin->getGeometryName());
			$this->putString($this->skin->getGeometryData());

			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_261) {
				$this->putBool($this->premiumSkin);
			}
		} else {
			$this->putSkin($this->skin, $this->getProtocol());
			$this->putString($this->newSkinName);
			$this->putString($this->oldSkinName);
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_390) {
				$this->putBool($this->skin->getSerializedSkin()->isTrustedSkin());
			}
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handlePlayerSkin($this);
	}
}
