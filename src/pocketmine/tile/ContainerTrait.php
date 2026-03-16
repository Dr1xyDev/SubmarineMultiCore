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

namespace pocketmine\tile;

use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;

/**
 * This trait implements most methods in the {@link Container} interface. It should only be used by Tiles.
 */
trait ContainerTrait
{
	/** @var string|null */
	private $lock;

	/**
	 * @return Inventory
	 */
	abstract public function getRealInventory();

	protected function loadItems(CompoundTag $tag) : void
	{
		if ($tag->hasTag(Container::TAG_ITEMS, ListTag::class)) {
			$inventoryTag = $tag->getListTag(Container::TAG_ITEMS);
			$inventory = $this->getRealInventory();

			$newContents = [];
			/** @var CompoundTag $itemNBT */
			foreach ($inventoryTag as $itemNBT) {
				$newContents[$itemNBT->getByte("Slot")] = Item::nbtDeserialize($itemNBT);
			}
			$inventory->setContents($newContents);
		}

		if ($tag->hasTag(Container::TAG_LOCK, StringTag::class)) {
			$this->lock = $tag->getString(Container::TAG_LOCK);
		}
	}

	protected function saveItems(CompoundTag $tag) : void
	{
		$items = [];
		foreach ($this->getRealInventory()->getContents() as $slot => $item) {
			$items[] = $item->nbtSerialize($slot);
		}

		$tag->setTag(new ListTag(Container::TAG_ITEMS, $items, NBT::TAG_Compound));

		if ($this->lock !== null) {
			$tag->setString(Container::TAG_LOCK, $this->lock);
		}
	}

	/**
	 * @see Container::canOpenWith()
	 */
	public function canOpenWith(string $key) : bool
	{
		return $this->lock === null || $this->lock === $key;
	}
}
