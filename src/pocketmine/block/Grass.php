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

use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\item\Fertilizer;
use pocketmine\item\Hoe;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\Shovel;
use pocketmine\level\generator\object\TallGrass as TallGrassObject;
use pocketmine\level\sound\ItemUseOnBlockSound;
use pocketmine\math\Facing;
use pocketmine\Player;
use pocketmine\utils\Random;

use function mt_rand;

class Grass extends Solid
{
	protected $id = self::GRASS;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Grass";
	}

	public function getHardness() : float
	{
		return 0.6;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_SHOVEL;
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [
			ItemFactory::get(Item::DIRT)
		];
	}

	public function ticksRandomly() : bool
	{
		return true;
	}

	public function onRandomTick() : void
	{
		$level = $this->getLevel();
		$lightAbove = $level->getFullLightAt($this->x, $this->y + 1, $this->z);
		if ($lightAbove < 4 && $level->getBlockAt($this->x, $this->y + 1, $this->z)->getLightFilter() >= 2) {
			//grass dies
			$ev = new BlockSpreadEvent($this, $this, BlockFactory::get(Block::DIRT));
			$ev->call();
			if (!$ev->isCancelled()) {
				$level->setBlock($this, $ev->getNewState(), false, false);
			}
		} elseif ($lightAbove >= 9) {
			//try grass spread
			for ($i = 0; $i < 4; ++$i) {
				$x = mt_rand($this->x - 1, $this->x + 1);
				$y = mt_rand($this->y - 3, $this->y + 1);
				$z = mt_rand($this->z - 1, $this->z + 1);

				$b = $level->getBlockAt($x, $y, $z);
				if (
					!($b instanceof Dirt) ||
					$b->getDamage() === 1 ||
					$level->getFullLightAt($x, $y + 1, $z) < 4 ||
					$level->getBlockAt($x, $y + 1, $z)->getLightFilter() >= 2
				) {
					continue;
				}

				$ev = new BlockSpreadEvent($b, $this, BlockFactory::get(Block::GRASS));
				$ev->call();
				if (!$ev->isCancelled()) {
					$level->setBlock($b, $ev->getNewState(), false, false);
				}
			}
		}
	}

	public function onActivate(Item $item, Player $player = null) : bool
	{
		if ($item instanceof Fertilizer) {
			$item->pop();
			TallGrassObject::growGrass($this->getLevel(), $this, new Random(mt_rand()), 8, 2);

			return true;
		} elseif ($item instanceof Hoe) {
			$item->applyDamage(1);
			$newBlock = BlockFactory::get(Block::FARMLAND);
			$this->level->addSound(new ItemUseOnBlockSound($this->add(0.5, 0.5, 0.5), $newBlock));
			$this->getLevel()->setBlock($this, $newBlock);

			return true;
		} elseif ($item instanceof Shovel && $this->getSide(Facing::UP)->getId() === Block::AIR) {
			$item->applyDamage(1);
			$newBlock = BlockFactory::get(Block::GRASS_PATH);
			$this->level->addSound(new ItemUseOnBlockSound($this->add(0.5, 0.5, 0.5), $newBlock));
			$this->getLevel()->setBlock($this, $newBlock);

			return true;
		}

		return false;
	}
}
