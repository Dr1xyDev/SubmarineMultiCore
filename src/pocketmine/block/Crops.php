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

use pocketmine\event\block\BlockGrowEvent;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;

use function mt_rand;

abstract class Crops extends Flowable
{
	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool
	{
		if ($blockReplace->getSide(Facing::DOWN)->getId() === Block::FARMLAND) {
			$this->getLevel()->setBlock($blockReplace, $this, true, true);

			return true;
		}

		return false;
	}

	public function onActivate(Item $item, Player $player = null) : bool
	{
		if ($this->meta < 7 && $item instanceof Fertilizer) { //Bonemeal
			$block = clone $this;
			$block->meta += mt_rand(2, 5);
			if ($block->meta > 7) {
				$block->meta = 7;
			}

			$ev = new BlockGrowEvent($this, $block);
			$ev->call();
			if (!$ev->isCancelled()) {
				$this->getLevel()->setBlock($this, $ev->getNewState(), true, true);
			}

			$item->pop();

			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void
	{
		if ($this->getSide(Facing::DOWN)->getId() !== Block::FARMLAND) {
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function ticksRandomly() : bool
	{
		return true;
	}

	public function onRandomTick() : void
	{
		if (mt_rand(0, 2) === 1) {
			if ($this->meta < 0x07) {
				$block = clone $this;
				++$block->meta;
				$ev = new BlockGrowEvent($this, $block);
				$ev->call();
				if (!$ev->isCancelled()) {
					$this->getLevel()->setBlock($this, $ev->getNewState(), true, true);
				}
			}
		}
	}

	public function isAffectedBySilkTouch() : bool
	{
		return false;
	}
}
