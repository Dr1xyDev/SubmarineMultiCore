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

use pocketmine\block\utils\TreeType;
use pocketmine\event\block\BlockGrowEvent;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\level\generator\object\TreeFactory;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\Random;

use function mt_rand;

class Sapling extends Flowable
{
	public const OAK = 0;
	public const SPRUCE = 1;
	public const BIRCH = 2;
	public const JUNGLE = 3;
	public const ACACIA = 4;
	public const DARK_OAK = 5;

	protected $id = self::SAPLING;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		static $names = [
			0 => "Oak Sapling",
			1 => "Spruce Sapling",
			2 => "Birch Sapling",
			3 => "Jungle Sapling",
			4 => "Acacia Sapling",
			5 => "Dark Oak Sapling"
		];
		return $names[$this->getVariant()] ?? "Unknown";
	}

	public function getTreeType() : ?TreeType {
		static $types = [
			0 => TreeType::OAK(),
			1 => TreeType::SPRUCE(),
			2 => TreeType::BIRCH(),
			3 => TreeType::JUNGLE(),
			4 => TreeType::ACACIA(),
			5 => TreeType::DARK_OAK()
		];
		return $types[$this->getVariant()] ?? null;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool
	{
		$down = $this->getSide(Facing::DOWN);
		if ($down->getId() === self::GRASS || $down->getId() === self::DIRT || $down->getId() === self::FARMLAND) {
			$this->getLevel()->setBlock($blockReplace, $this, true, true);

			return true;
		}

		return false;
	}

	public function onActivate(Item $item, Player $player = null) : bool
	{
		if ($item instanceof Fertilizer && $this->grow()) {
			$item->pop();

			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void
	{
		if ($this->getSide(Facing::DOWN)->isTransparent()) {
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function ticksRandomly() : bool
	{
		return true;
	}

	public function onRandomTick() : void
	{
		if ($this->level->getFullLightAt($this->x, $this->y, $this->z) >= 8 && mt_rand(1, 7) === 1) {
			if ($this->isReady()) {
				$this->grow();
			} else {
				$this->setReady(true);
				$this->getLevel()->setBlock($this, $this, true);
			}
		}
	}

	public function grow() : bool {
		$block = clone $this;
		$ev = new BlockGrowEvent($this, $block);
		$ev->call();
		if (!$ev->isCancelled()) {
			$treeType = $this->getTreeType();
			if ($treeType !== null) {
				$random = new Random(mt_rand());
				$transaction = TreeFactory::get($random, $treeType)?->getBlockTransaction($this->level, $this->x, $this->y, $this->z, $random);
				if ($transaction === null) {
					return false;
				}

				$transaction->apply();
			}
		}

		return true;
	}

	public function getVariantBitmask() : int
	{
		return 0x07;
	}

	public function getReadyBitmask() : int {
		return 0x08;
	}

	public function isReady() : bool {
		return ($this->meta & $this->getReadyBitmask()) !== 0;
	}

	public function setReady(bool $value) : void {
		$this->meta = ($this->meta & ~$this->getReadyBitmask()) | ($value ? $this->getReadyBitmask() : 0);
	}

	public function getFuelTime() : int
	{
		return 100;
	}
}
