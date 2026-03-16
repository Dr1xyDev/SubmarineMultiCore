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

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\CompoundTag;

class FlowerPot extends Spawnable
{
	public const TAG_ITEM = "item";
	public const TAG_ITEM_DATA = "mData";

	/** @var Item */
	private $item;

	protected function readSaveData(CompoundTag $nbt) : void
	{
		$this->item = ItemFactory::get($nbt->getShort(self::TAG_ITEM, 0, true), $nbt->getInt(self::TAG_ITEM_DATA, 0, true), 1);
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$nbt->setShort(self::TAG_ITEM, $this->item->getId());
		$nbt->setInt(self::TAG_ITEM_DATA, $this->item->getDamage());
	}

	public function getItem() : Item
	{
		return clone $this->item;
	}

	/**
	 * @return void
	 */
	public function setItem(Item $item)
	{
		$this->item = clone $item;

		$this->onChanged();
	}

	/**
	 * @return void
	 */
	public function removeItem()
	{
		$this->setItem(ItemFactory::get(Item::AIR, 0, 0));
	}

	public function isEmpty() : bool
	{
		return $this->getItem()->isNull();
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void
	{
		$nbt->setShort(self::TAG_ITEM, $this->item->getId());
		$nbt->setInt(self::TAG_ITEM_DATA, $this->item->getDamage());
	}
}
