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
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Facing;

use function mt_rand;

class MelonStem extends Crops
{
	protected $id = self::MELON_STEM;

	public function getName() : string
	{
		return "Melon Stem";
	}

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
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
					$this->getLevel()->setBlock($this, $ev->getNewState(), true);
				}
			} else {
				for ($side = 2; $side <= 5; ++$side) {
					$b = $this->getSide($side);
					if ($b->getId() === self::MELON_BLOCK) {
						return;
					}
				}
				$side = $this->getSide(mt_rand(2, 5));
				$d = $side->getSide(Facing::DOWN);
				if ($side->getId() === self::AIR && ($d->getId() === self::FARMLAND || $d->getId() === self::GRASS || $d->getId() === self::DIRT)) {
					$ev = new BlockGrowEvent($side, BlockFactory::get(Block::MELON_BLOCK));
					$ev->call();
					if (!$ev->isCancelled()) {
						$this->getLevel()->setBlock($side, $ev->getNewState(), true);
					}
				}
			}
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [
			ItemFactory::get(Item::MELON_SEEDS, 0, mt_rand(0, 2))
		];
	}

	public function getPickedItem(bool $addUserData = false) : Item
	{
		return ItemFactory::get(Item::MELON_SEEDS);
	}
}
