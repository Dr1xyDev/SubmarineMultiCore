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
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\EnderChest as TileEnderChest;
use pocketmine\tile\Tile;

class EnderChest extends Chest
{
	protected $id = self::ENDER_CHEST;

	public function getHardness() : float
	{
		return 22.5;
	}

	public function getBlastResistance() : float
	{
		return 3000;
	}

	public function getLightLevel() : int
	{
		return 7;
	}

	public function getName() : string
	{
		return "Ender Chest";
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int
	{
		return TieredTool::TIER_WOODEN;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool
	{
		$faces = [
			0 => 4,
			1 => 2,
			2 => 5,
			3 => 3
		];

		$this->meta = $faces[$player instanceof Player ? $player->getDirection() : 0];

		$this->getLevel()->setBlock($blockReplace, $this, true, true);
		Tile::createTile(Tile::ENDER_CHEST, $this->getLevel(), TileEnderChest::createNBT($this, $face, $item, $player));

		return true;
	}

	public function onActivate(Item $item, Player $player = null) : bool
	{
		if ($player instanceof Player) {

			$t = $this->getLevel()->getTile($this);
			$enderChest = null;
			if ($t instanceof TileEnderChest) {
				$enderChest = $t;
			} else {
				$enderChest = Tile::createTile(Tile::ENDER_CHEST, $this->getLevel(), TileEnderChest::createNBT($this));
				if (!($enderChest instanceof TileEnderChest)) {
					return true;
				}
			}

			if (!$this->getSide(Facing::UP)->isTransparent()) {
				return true;
			}

			$player->getEnderChestInventory()->setHolderPosition($enderChest);
			$player->addWindow($player->getEnderChestInventory());
		}

		return true;
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [
			ItemFactory::get(Item::OBSIDIAN, 0, 8)
		];
	}

	public function getFuelTime() : int
	{
		return 0;
	}
}
