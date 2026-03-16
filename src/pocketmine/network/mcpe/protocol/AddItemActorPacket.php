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
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;

class AddItemActorPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::ADD_ITEM_ACTOR_PACKET;

	public ?int $entityUniqueId = null; //TODO
	public int $entityRuntimeId;
	public Item|ItemStackWrapper $item;
	public Vector3 $position;
	public ?Vector3 $motion = null;
	public array $metadata = [];
	public bool $isFromFishing = false;

	/**
	 * @generate-create-func
	 */
	public static function create(
		int $entityUniqueId,
		int $entityRuntimeId,
		Item|ItemStackWrapper $item,
		Vector3 $position,
		?Vector3 $motion,
		array $metadata,
		bool $isFromFishing
	) : self {
		$result = new self();
		$result->entityUniqueId = $entityUniqueId;
		$result->entityRuntimeId = $entityRuntimeId;
		$result->item = $item;
		$result->position = $position;
		$result->motion = $motion;
		$result->metadata = $metadata;
		$result->isFromFishing = $isFromFishing;
		return $result;
	}

	protected function decodePayload() : void
	{
		$this->entityUniqueId = $this->getEntityUniqueId();
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		$this->item = $this->getItemStackWrapper($this->getProtocol());
		$this->position = $this->getVector3();
		$this->motion = $this->getVector3();
		$this->metadata = $this->getEntityMetadata($this->getProtocol());
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_223) {
			$this->isFromFishing = $this->getBool();
		}
	}

	protected function encodePayload() : void
	{
		$this->putEntityUniqueId($this->entityUniqueId ?? $this->entityRuntimeId);
		$this->putEntityRuntimeId($this->entityRuntimeId);
		$this->putItemStackWrapper($this->item, $this->getProtocol());
		$this->putVector3($this->position);
		$this->putVector3Nullable($this->motion);
		$this->putEntityMetadata($this->metadata, $this->getProtocol());
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_223) {
			$this->putBool($this->isFromFishing);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleAddItemActor($this);
	}
}
