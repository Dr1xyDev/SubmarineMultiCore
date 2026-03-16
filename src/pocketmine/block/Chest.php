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
use pocketmine\tile\Chest as TileChest;
use pocketmine\tile\Tile;

class Chest extends Transparent
{
	protected $id = self::CHEST;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getHardness() : float
	{
		return 2.5;
	}

	public function getName() : string
	{
		return "Chest";
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_AXE;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB
	{
		//these are slightly bigger than in PC
		return new AxisAlignedBB(
			$this->x + 0.025,
			$this->y,
			$this->z + 0.025,
			$this->x + 0.975,
			$this->y + 0.95,
			$this->z + 0.975
		);
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool
	{
		$faces = [
			0 => 4,
			1 => 2,
			2 => 5,
			3 => 3
		];

		$chest = null;
		$this->meta = $faces[$player instanceof Player ? $player->getDirection() : 0];

		for ($side = 2; $side <= 5; ++$side) {
			if (($this->meta === 4 || $this->meta === 5) && ($side === 4 || $side === 5)) {
				continue;
			} elseif (($this->meta === 3 || $this->meta === 2) && ($side === 2 || $side === 3)) {
				continue;
			}
			$c = $this->getSide($side);
			if ($c->getId() === $this->id && $c->getDamage() === $this->meta) {
				$tile = $this->getLevel()->getTile($c);
				if ($tile instanceof TileChest && !$tile->isPaired()) {
					$chest = $tile;
					break;
				}
			}
		}

		$this->getLevel()->setBlock($blockReplace, $this, true, true);
		$tile = Tile::createTile(Tile::CHEST, $this->getLevel(), TileChest::createNBT($this, $face, $item, $player));

		if ($chest instanceof TileChest && $tile instanceof TileChest) {
			$chest->pairWith($tile);
			$tile->pairWith($chest);
		}

		return true;
	}

	public function onActivate(Item $item, Player $player = null) : bool
	{
		if ($player instanceof Player) {

			$t = $this->getLevel()->getTile($this);
			$chest = null;
			if ($t instanceof TileChest) {
				$chest = $t;
			} else {
				$chest = Tile::createTile(Tile::CHEST, $this->getLevel(), TileChest::createNBT($this));
				if (!($chest instanceof TileChest)) {
					return true;
				}
			}

			if (
				!$this->getSide(Facing::UP)->isTransparent() ||
				(($pair = $chest->getPair()) !== null && !$pair->getBlock()->getSide(Facing::UP)->isTransparent()) ||
				!$chest->canOpenWith($item->getCustomName())
			) {
				return true;
			}

			$player->addWindow($chest->getInventory());
		}

		return true;
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}

	public function getFuelTime() : int
	{
		return 300;
	}
}
