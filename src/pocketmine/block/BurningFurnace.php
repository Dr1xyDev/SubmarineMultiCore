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
use pocketmine\item\TieredTool;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\Furnace as TileFurnace;
use pocketmine\tile\Tile;

use function mt_rand;

class BurningFurnace extends Solid
{
	protected $id = self::BURNING_FURNACE;

	protected $itemId = self::FURNACE;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Burning Furnace";
	}

	public function getHardness() : float
	{
		return 3.5;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int
	{
		return TieredTool::TIER_WOODEN;
	}

	public function getLightLevel() : int
	{
		return 13;
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

		Tile::createTile(Tile::FURNACE, $this->getLevel(), TileFurnace::createNBT($this, $face, $item, $player));

		return true;
	}

	public function onActivate(Item $item, Player $player = null) : bool
	{
		if ($player instanceof Player) {
			$furnace = $this->getLevel()->getTile($this);
			if (!($furnace instanceof TileFurnace)) {
				$furnace = Tile::createTile(Tile::FURNACE, $this->getLevel(), TileFurnace::createNBT($this));
				if (!($furnace instanceof TileFurnace)) {
					return true;
				}
			}

			if (!$furnace->canOpenWith($item->getCustomName())) {
				return true;
			}

			$player->addWindow($furnace->getInventory());
		}

		return true;
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}

	public function onScheduledUpdate() : void
	{
		$level = $this->getLevel();
		$furnace = $level->getTile($this);
		if ($furnace instanceof TileFurnace && $furnace->onUpdate()) {
			if (mt_rand(1, 60) === 1) { //in vanilla this is between 1 and 5 seconds; try to average about 3
				$level->addSound($furnace->getFurnaceType()->getCookSound($this));
			}
			$level->scheduleDelayedBlockUpdate($this, 1); //TODO: check this
		}
	}
}
