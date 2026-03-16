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

use pocketmine\item\Item;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;

class MobArmorEquipmentPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::MOB_ARMOR_EQUIPMENT_PACKET;

	public int $entityRuntimeId;

	//this intentionally doesn't use an array because we don't want any implicit dependencies on internal order
	public Item|ItemStackWrapper $head;
	public Item|ItemStackWrapper $chest;
	public Item|ItemStackWrapper $legs;
	public Item|ItemStackWrapper $feet;
	public Item|ItemStackWrapper $body;

	/**
	 * @generate-create-func
	 */
	public static function create(
		int $entityRuntimeId,
		Item|ItemStackWrapper $head,
		Item|ItemStackWrapper $chest,
		Item|ItemStackWrapper $legs,
		Item|ItemStackWrapper $feet,
		Item|ItemStackWrapper $body
	) : self{
		$result = new self();
		$result->entityRuntimeId = $entityRuntimeId;
		$result->head = $head;
		$result->chest = $chest;
		$result->legs = $legs;
		$result->feet = $feet;
		$result->body = $body;
		return $result;
	}

	protected function decodePayload() : void
	{
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		$this->head = $this->getItemStackWrapper($this->getProtocol());
		$this->chest = $this->getItemStackWrapper($this->getProtocol());
		$this->legs = $this->getItemStackWrapper($this->getProtocol());
		$this->feet = $this->getItemStackWrapper($this->getProtocol());
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_712) {
			$this->body = $this->getItemStackWrapper($this->getProtocol());
		}
	}

	protected function encodePayload() : void
	{
		$this->putEntityRuntimeId($this->entityRuntimeId);
		$this->putItemStackWrapper($this->head, $this->getProtocol());
		$this->putItemStackWrapper($this->chest, $this->getProtocol());
		$this->putItemStackWrapper($this->legs, $this->getProtocol());
		$this->putItemStackWrapper($this->feet, $this->getProtocol());
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_712) {
			$this->putItemStackWrapper($this->body, $this->getProtocol());
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleMobArmorEquipment($this);
	}
}
