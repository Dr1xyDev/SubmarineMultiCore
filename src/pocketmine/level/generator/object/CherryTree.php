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

use pocketmine\block\CherryLeaves;
use pocketmine\block\CherryLog;
use pocketmine\block\Dirt;
use pocketmine\level\BlockTransaction;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use function abs;

class CherryTree extends Tree
{
	public function __construct()
	{
		parent::__construct(new CherryLog(), new CherryLeaves(), 6);
	}

	protected function generateTrunkHeight(Random $random) : int {
		return 4 + $random->nextBoundedInt(4);
	}

	protected function placeTrunk(int $x, int $y, int $z, Random $random, int $trunkHeight, BlockTransaction $transaction) : void {
		$transaction->addBlockAt($x, $y - 1, $z, new Dirt());

		for ($yy = 0; $yy < $trunkHeight; ++$yy) {
			if ($this->canOverride($transaction->fetchBlockAt($x, $y + $yy, $z))) {
				$transaction->addBlockAt($x, $y + $yy, $z, $this->trunkBlock);
			}
		}

		$directions = [Facing::NORTH, Facing::SOUTH, Facing::EAST, Facing::WEST];
		for ($i = 0; $i < 4; ++$i) {
			$branchStart = 2 + $random->nextBoundedInt($trunkHeight - 3);
			$dir = $directions[$random->nextBoundedInt(4)];
			$branchLength = 2 + $random->nextBoundedInt(4);

			$bx = $x;
			$bz = $z;
			$by = $y + $branchStart;

			for ($l = 1; $l <= $branchLength; ++$l) {
				$pos = (new Vector3($bx, $by, $bz))->getSide($dir, $l);
				if ($this->canOverride($transaction->fetchBlockAt($pos->x, $pos->y, $pos->z))) {
					$transaction->addBlockAt($pos->x, $pos->y, $pos->z, $this->trunkBlock);
				}

				if ($random->nextBoundedInt(3) === 0) {
					++$by;
				}
			}
		}
	}

	protected function placeCanopy(int $x, int $y, int $z, Random $random, BlockTransaction $transaction) : void {
		$topY = $y + $this->treeHeight - 1;

		for ($layer = -2; $layer <= 2; ++$layer) {
			$ly = $topY + $layer;
			$radius = 4 - abs($layer);

			for ($dx = -$radius; $dx <= $radius; ++$dx) {
				for ($dz = -$radius; $dz <= $radius; ++$dz) {
					if (abs($dx) + abs($dz) > $radius && $random->nextBoundedInt(2) !== 0) {
						continue; // Round the edges
					}

					$lx = $x + $dx;
					$lz = $z + $dz;

					$block = $transaction->fetchBlockAt($lx, $ly, $lz);
					if (!$block->isSolid() && $this->canOverride($block)) {
						$transaction->addBlockAt($lx, $ly, $lz, $this->leafBlock);
					}

					if ($layer <= 0 && $random->nextBoundedInt(3) === 0) {
						$hangY = $ly - 1;
						$hangBlock = $transaction->fetchBlockAt($lx, $hangY, $lz);
						if (!$hangBlock->isSolid() && $this->canOverride($hangBlock)) {
							$transaction->addBlockAt($lx, $hangY, $lz, $this->leafBlock);
						}
					}
				}
			}
		}

		$directions = [Facing::NORTH, Facing::SOUTH, Facing::EAST, Facing::WEST];
		foreach ($directions as $dir) {
			for ($dist = 1; $dist <= 4; ++$dist) {
				$pos = (new Vector3($x, $topY, $z))->getSide($dir, $dist);
				for ($dx = -1; $dx <= 1; ++$dx) {
					for ($dz = -1; $dz <= 1; ++$dz) {
						if (abs($dx) + abs($dz) > 1 && $random->nextBoundedInt(2) !== 0) {
							continue;
						}
						$lx = $pos->x + $dx;
						$lz = $pos->z + $dz;
						$block = $transaction->fetchBlockAt($lx, $topY, $lz);
						if (!$block->isSolid() && $this->canOverride($block)) {
							$transaction->addBlockAt($lx, $topY, $lz, $this->leafBlock);
						}
					}
				}
			}
		}
	}
}
