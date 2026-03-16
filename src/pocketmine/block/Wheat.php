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

use function mt_rand;

class Wheat extends Crops
{
	protected $id = self::WHEAT_BLOCK;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Wheat Block";
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		if ($this->meta >= 0x07) {
			return [
				ItemFactory::get(Item::WHEAT),
				ItemFactory::get(Item::WHEAT_SEEDS, 0, mt_rand(0, 3))
			];
		} else {
			return [
				ItemFactory::get(Item::WHEAT_SEEDS)
			];
		}
	}

	public function getPickedItem(bool $addUserData = false) : Item
	{
		return ItemFactory::get(Item::WHEAT_SEEDS);
	}
}
