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
use pocketmine\network\mcpe\protocol\types\inventory\FullContainerName;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;

use function count;

class InventoryContentPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::INVENTORY_CONTENT_PACKET;

	public int $windowId;
	/** @var Item|ItemStackWrapper[] */
	public array $items = [];
	/** @var int[] */
	public array $index = [];
	public FullContainerName $containerName;
	public Item|ItemStackWrapper $storage;

	/**
	 * @generate-create-func
	 * @param Item|ItemStackWrapper[] $items
	 */
	public static function create(int $windowId, array $items, FullContainerName $containerName, Item|ItemStackWrapper $storage) : self
	{
		$result = new self();
		$result->windowId = $windowId;
		$result->items = $items;
		$result->containerName = $containerName;
		$result->storage = $storage;
		return $result;
	}

	protected function decodePayload() : void
	{
		$this->windowId = $this->getUnsignedVarInt();
		$count = $this->getUnsignedVarInt();
		for ($i = 0; $i < $count; ++$i) {
			$this->index[] = $this->getVarInt();
			$this->items[] = $this->getItemStackWrapper($this->getProtocol());
		}
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_712) {
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_729) {
				$this->containerName = FullContainerName::read($this, $this->getProtocol());
				if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_748) {
					$this->storage = $this->getItemStackWrapper($this->getProtocol());
				} else {
					$this->getUnsignedVarInt(); //TODO: dynamicContainerSize, WTF?
				}
			} else {
				$this->containerName = new FullContainerName($this->getUnsignedVarInt());
			}
		}
	}

	protected function encodePayload() : void
	{
		$this->putUnsignedVarInt($this->windowId);
		$this->putUnsignedVarInt(count($this->items));
		$index = 1;
		foreach ($this->items as $item) {
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_407 && $this->getProtocol() <= ProtocolInfo::PROTOCOL_428) {
				if ($item->getStackId() === 0) {
					$this->putVarInt(0);
				} else {
					$this->putVarInt($index++);
				}
			}
			$this->putItemStackWrapper($item, $this->getProtocol());
		}

		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_712) {
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_729) {
				($this->containerName ?? new FullContainerName(0))->write($this, $this->getProtocol());
				if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_748) {
					$this->putItemStackWrapper($this->storage, $this->getProtocol());
				} else {
					$this->putUnsignedVarInt(0); //TODO: dynamicContainerSize, WTF?
				}
			} else {
				$this->putUnsignedVarInt(($this->containerName ?? new FullContainerName(0))->getContainerId());
			}
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleInventoryContent($this);
	}
}
