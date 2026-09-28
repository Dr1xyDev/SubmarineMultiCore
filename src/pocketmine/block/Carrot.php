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

use pocketmine\block\utils\FortuneDropHelper;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;

use pocketmine\item\ItemIds;

class Carrot extends Crops {
	protected $id = self::CARROT_BLOCK;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string
	{return "Carrot Block";
	}

	public function getSeed() : Item {
		return ItemFactory::get(ItemIds::CARROT);
	}

	public function getCrop() : Item {
		return ItemFactory::get(ItemIds::CARROT);
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			ItemFactory::get(ItemIds::CARROT, 0, $this->isMaxAge() ? FortuneDropHelper::binomial($item, 1) : 1)
		];
	}
}
