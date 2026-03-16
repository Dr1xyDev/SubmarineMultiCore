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
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\FlowerPot as TileFlowerPot;
use pocketmine\tile\Tile;

class FlowerPot extends Flowable
{
	public const STATE_EMPTY = 0;
	public const STATE_FULL = 1;

	protected $id = self::FLOWER_POT_BLOCK;
	protected $itemId = Item::FLOWER_POT;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Flower Pot";
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB
	{
		return new AxisAlignedBB(
			$this->x + 0.3125,
			$this->y,
			$this->z + 0.3125,
			$this->x + 0.6875,
			$this->y + 0.375,
			$this->z + 0.6875
		);
	}

	private function isValidPlant(Item $item) : bool
	{
		$id = $item->getId();

		return
			$id === Item::CACTUS ||
			$id === Item::DEAD_BUSH ||
			$id === Item::RED_FLOWER ||
			$id === Item::DANDELION ||
			$id === Item::RED_MUSHROOM ||
			$id === Item::BROWN_MUSHROOM ||
			$id === Item::SAPLING ||
			($id === Item::TALL_GRASS && $item->getDamage() === 1);
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool
	{
		if ($this->getSide(Facing::DOWN)->isTransparent()) {
			return false;
		}

		$this->getLevel()->setBlock($blockReplace, $this, true, true);
		Tile::createTile(Tile::FLOWER_POT, $this->getLevel(), TileFlowerPot::createNBT($this, $face, $item, $player));
		return true;
	}

	public function onNearbyBlockChange() : void
	{
		if ($this->getSide(Facing::DOWN)->isTransparent()) {
			$this->level->useBreakOn($this);
		}
	}

	public function onActivate(Item $item, Player $player = null) : bool
	{
		$pot = $this->getLevel()->getTile($this);
		if (!($pot instanceof TileFlowerPot)) {
			return false;
		}

		if ($this->isValidPlant($item)) {
			$this->setDamage(self::STATE_FULL); //specific damage value is unnecessary, it just needs to be non-zero to show an item.
			$pot->setItem($item->pop());
			$this->getLevel()->setBlock($this, $this);

			return true;
		}

		return false;
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		$items = parent::getDropsForCompatibleTool($item);

		$tile = $this->getLevel()->getTile($this);
		if ($tile instanceof TileFlowerPot) {
			$item = $tile->getItem();
			if ($item->getId() !== Item::AIR) {
				$items[] = $item;
			}
		}

		return $items;
	}

	public function getPickedItem(bool $addUserData = false) : Item
	{
		$plant = null;

		$tile = $this->getLevel()->getTile($this);
		if ($tile instanceof TileFlowerPot) {
			$item = $tile->getItem();
			if ($item->getId() !== Item::AIR) {
				$plant = $item;
			}
		}

		return $plant !== null ? $plant : parent::getPickedItem($addUserData);
	}
}
