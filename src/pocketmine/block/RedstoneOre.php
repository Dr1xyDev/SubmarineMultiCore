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
use pocketmine\item\TieredTool;
use pocketmine\math\Vector3;
use pocketmine\Player;

use function mt_rand;

class RedstoneOre extends Solid
{
	protected $id = self::REDSTONE_ORE;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Redstone Ore";
	}

	public function getHardness() : float
	{
		return 3;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool
	{
		$this->getLevel()->setBlock($this, $this, true, false);
		return true;
	}

	public function onActivate(Item $item, Player $player = null) : bool
	{
		$this->getLevel()->setBlock($this, BlockFactory::get(Block::GLOWING_REDSTONE_ORE, $this->meta));
		return false; //this shouldn't prevent block placement
	}

	public function onNearbyBlockChange() : void
	{
		$this->getLevel()->setBlock($this, BlockFactory::get(Block::GLOWING_REDSTONE_ORE, $this->meta));
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int
	{
		return TieredTool::TIER_IRON;
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [
			ItemFactory::get(Item::REDSTONE_DUST, 0, mt_rand(4, 5))
		];
	}

	protected function getXpDropAmount() : int
	{
		return mt_rand(1, 5);
	}
}
