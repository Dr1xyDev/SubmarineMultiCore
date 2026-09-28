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
use pocketmine\utils\Random;

class Beetroot extends Crops {
	protected $id = self::BEETROOT_BLOCK;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Beetroot Block";
	}

	public function getMaxAge() : int{
		return 3;
	}

	public function onRandomTick() : void{
		if ($this->level->random->nextBoundedInt(3) !== 0) {
			parent::onRandomTick();
		}
	}

	protected function getBonemealAgeIncrease(Random $random) : int{
		return (int) (parent::getBonemealAgeIncrease($random) / 3);
	}

	public function getSeed() : Item {
		return ItemFactory::get(ItemIds::BEETROOT_SEEDS);
	}

	public function getCrop() : Item {
		return ItemFactory::get(ItemIds::BEETROOT);
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		if ($this->isMaxAge()) {
			return [
				ItemFactory::get(ItemIds::BEETROOT),
				ItemFactory::get(ItemIds::BEETROOT_SEEDS, 0, FortuneDropHelper::binomial($item, 0))
			];
		} else {
			return [
				ItemFactory::get(ItemIds::BEETROOT_SEEDS)
			];
		}
	}
}
