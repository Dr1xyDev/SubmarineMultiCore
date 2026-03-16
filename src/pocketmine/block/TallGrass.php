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
use pocketmine\item\TieredTool;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;

class TallGrass extends Flowable
{
	public const TYPE_DEAD_SHRUB = 0;
	public const TYPE_TALL_GRASS = 1;
	public const TYPE_FERN = 2;

	protected $id = self::TALL_GRASS;

	public function __construct(int $meta = 1)
	{
		$this->meta = $meta;
	}

	public function canBeReplaced() : bool
	{
		return true;
	}

	public function getName() : string
	{
		static $names = [
			self::TYPE_DEAD_SHRUB => "Dead Shrub",
			self::TYPE_TALL_GRASS => "Tall Grass",
			self::TYPE_FERN => "Fern"
		];
		return $names[$this->getVariant()] ?? "Unknown";
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool
	{
		$down = $this->getSide(Facing::DOWN)->getId();
		if ($down === self::GRASS || $down === self::DIRT) {
			$this->getLevel()->setBlock($blockReplace, $this, true);

			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void
	{
		if ($this->getSide(Facing::DOWN)->isTransparent()) { //Replace with common break method
			$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), true, true);
		}
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_SHEARS;
	}

	public function getToolHarvestLevel() : int
	{
		return TieredTool::TIER_WOODEN;
	}

	public function getDropsForIncompatibleTool(Item $item) : array
	{
		if (FortuneDropHelper::bonusChanceDivisor($item, 8, 2)) {
			return [
				ItemFactory::get(Item::WHEAT_SEEDS)
			];
		}

		return [];
	}

	public function getFlameEncouragement() : int
	{
		return 60;
	}

	public function getFlammability() : int
	{
		return 100;
	}
}
