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

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;

use pocketmine\item\ItemIds;
use function mt_rand;

class Potato extends Crops
{
	protected $id = self::POTATO_BLOCK;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Potato Block";
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		$result = [
			ItemFactory::get(ItemIds::POTATO, 0, $this->getDamage() >= 0x07 ? mt_rand(1, 5) : 1)
		];
		if ($this->getDamage() >= 7 && mt_rand(0, 49) === 0) {
			$result[] = ItemFactory::get(ItemIds::POISONOUS_POTATO);
		}
		return $result;
	}

	public function getPickedItem(bool $addUserData = false) : Item
	{
		return ItemFactory::get(ItemIds::POTATO);
	}
}
