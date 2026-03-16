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

namespace pocketmine\level\generator\object;

use pocketmine\block\Block;
use pocketmine\block\Dirt;
use pocketmine\block\Leaves;
use pocketmine\block\Sapling;
use pocketmine\level\BlockTransaction;
use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;

use function abs;

abstract class Tree
{
	public function __construct(
		protected Block $trunkBlock,
		protected Block $leafBlock,
		protected int $treeHeight = 7
	) {
	}

	public function canPlaceObject(ChunkManager $world, int $x, int $y, int $z, Random $random) : bool
	{
		$radiusToCheck = 0;
		for ($yy = 0; $yy < $this->treeHeight + 3; ++$yy) {
			if ($yy === 1 || $yy === $this->treeHeight) {
				++$radiusToCheck;
			}
			for ($xx = -$radiusToCheck; $xx < ($radiusToCheck + 1); ++$xx) {
				for ($zz = -$radiusToCheck; $zz < ($radiusToCheck + 1); ++$zz) {
					if (!$this->canOverride($world->getBlockAt($x + $xx, $y + $yy, $z + $zz))) {
						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Returns the BlockTransaction containing all the blocks the tree would change upon growing at the given coordinates
	 * or null if the tree can't be grown
	 */
	public function getBlockTransaction(ChunkManager $world, int $x, int $y, int $z, Random $random) : ?BlockTransaction
	{
		if (!$this->canPlaceObject($world, $x, $y, $z, $random)) {
			return null;
		}

		$transaction = new BlockTransaction($world);
		$this->placeTrunk($x, $y, $z, $random, $this->generateTrunkHeight($random), $transaction);
		$this->placeCanopy($x, $y, $z, $random, $transaction);

		return $transaction;
	}

	protected function generateTrunkHeight(Random $random) : int
	{
		return $this->treeHeight - 1;
	}

	protected function placeTrunk(int $x, int $y, int $z, Random $random, int $trunkHeight, BlockTransaction $transaction) : void
	{
		// The base dirt block
		$transaction->addBlockAt($x, $y - 1, $z, new Dirt());

		for ($yy = 0; $yy < $trunkHeight; ++$yy) {
			if ($this->canOverride($transaction->fetchBlockAt($x, $y + $yy, $z))) {
				$transaction->addBlockAt($x, $y + $yy, $z, $this->trunkBlock);
			}
		}
	}

	protected function placeCanopy(int $x, int $y, int $z, Random $random, BlockTransaction $transaction) : void
	{
		for ($yy = $y - 3 + $this->treeHeight; $yy <= $y + $this->treeHeight; ++$yy) {
			$yOff = $yy - ($y + $this->treeHeight);
			$mid = (int) (1 - $yOff / 2);
			for ($xx = $x - $mid; $xx <= $x + $mid; ++$xx) {
				$xOff = abs($xx - $x);
				for ($zz = $z - $mid; $zz <= $z + $mid; ++$zz) {
					$zOff = abs($zz - $z);
					if ($xOff === $mid && $zOff === $mid && ($yOff === 0 || $random->nextBoundedInt(2) === 0)) {
						continue;
					}
					if (!$transaction->fetchBlockAt($xx, $yy, $zz)->isSolid()) {
						$transaction->addBlockAt($xx, $yy, $zz, $this->leafBlock);
					}
				}
			}
		}
	}

	protected function canOverride(Block $block) : bool
	{
		return $block->canBeReplaced() || $block instanceof Sapling || $block instanceof Leaves;
	}
}
