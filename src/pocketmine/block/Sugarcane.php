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

class Sugarcane extends Flowable
{
	protected $id = self::SUGARCANE_BLOCK;

	protected $itemId = Item::SUGARCANE;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Sugarcane";
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}

	public function onActivate(Item $item, Player $player = null) : bool
	{
		if ($item instanceof Fertilizer) {
			if ($this->getSide(Facing::DOWN)->getId() !== self::SUGARCANE_BLOCK) {
				for ($y = 1; $y < 3; ++$y) {
					$b = $this->getLevel()->getBlockAt($this->x, $this->y + $y, $this->z);
					if ($b->getId() === self::AIR) {
						$ev = new BlockGrowEvent($b, BlockFactory::get(Block::SUGARCANE_BLOCK));
						$ev->call();
						if ($ev->isCancelled()) {
							break;
						}
						$this->getLevel()->setBlock($b, $ev->getNewState(), true);
					} else {
						break;
					}
				}
				$this->meta = 0;
				$this->getLevel()->setBlock($this, $this, true);
			}

			$item->count--;

			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void
	{
		$down = $this->getSide(Facing::DOWN);
		if ($down->isTransparent() && $down->getId() !== self::SUGARCANE_BLOCK) {
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function ticksRandomly() : bool
	{
		return true;
	}

	public function onRandomTick() : void
	{
		if ($this->getSide(Facing::DOWN)->getId() !== self::SUGARCANE_BLOCK) {
			if ($this->meta === 0x0F) {
				for ($y = 1; $y < 3; ++$y) {
					$b = $this->getLevel()->getBlockAt($this->x, $this->y + $y, $this->z);
					if ($b->getId() === self::AIR) {
						$this->getLevel()->setBlock($b, BlockFactory::get(Block::SUGARCANE_BLOCK), true);
						break;
					}
				}
				$this->meta = 0;
				$this->getLevel()->setBlock($this, $this, true);
			} else {
				++$this->meta;
				$this->getLevel()->setBlock($this, $this, true);
			}
		}
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool
	{
		$down = $this->getSide(Facing::DOWN);
		if ($down->getId() === self::SUGARCANE_BLOCK) {
			$this->getLevel()->setBlock($blockReplace, BlockFactory::get(Block::SUGARCANE_BLOCK), true);

			return true;
		} elseif ($down->getId() === self::GRASS || $down->getId() === self::DIRT || $down->getId() === self::SAND) {
			$block0 = $down->getSide(Facing::NORTH);
			$block1 = $down->getSide(Facing::SOUTH);
			$block2 = $down->getSide(Facing::WEST);
			$block3 = $down->getSide(Facing::EAST);
			if (($block0 instanceof Water) || ($block1 instanceof Water) || ($block2 instanceof Water) || ($block3 instanceof Water)) {
				$this->getLevel()->setBlock($blockReplace, BlockFactory::get(Block::SUGARCANE_BLOCK), true);

				return true;
			}
		}

		return false;
	}
}
