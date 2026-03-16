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

use pocketmine\block\Leaves;
use pocketmine\block\Log;
use pocketmine\level\BlockTransaction;
use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;

use function abs;

class SpruceTree extends Tree
{
	public function __construct()
	{
		parent::__construct(new Log(Log::SPRUCE), new Leaves(Leaves::SPRUCE), 10);
	}

	protected function generateTrunkHeight(Random $random) : int
	{
		return $this->treeHeight - $random->nextBoundedInt(3);
	}

	public function getBlockTransaction(ChunkManager $world, int $x, int $y, int $z, Random $random) : ?BlockTransaction
	{
		$this->treeHeight = $random->nextBoundedInt(4) + 6;
		return parent::getBlockTransaction($world, $x, $y, $z, $random);
	}

	protected function placeCanopy(int $x, int $y, int $z, Random $random, BlockTransaction $transaction) : void
	{
		$topSize = $this->treeHeight - (1 + $random->nextBoundedInt(2));
		$lRadius = 2 + $random->nextBoundedInt(2);
		$radius = $random->nextBoundedInt(2);
		$maxR = 1;
		$minR = 0;

		for ($yy = 0; $yy <= $topSize; ++$yy) {
			$yyy = $y + $this->treeHeight - $yy;

			for ($xx = $x - $radius; $xx <= $x + $radius; ++$xx) {
				$xOff = abs($xx - $x);
				for ($zz = $z - $radius; $zz <= $z + $radius; ++$zz) {
					$zOff = abs($zz - $z);
					if ($xOff === $radius && $zOff === $radius && $radius > 0) {
						continue;
					}

					if (!$transaction->fetchBlockAt($xx, $yyy, $zz)->isSolid()) {
						$transaction->addBlockAt($xx, $yyy, $zz, $this->leafBlock);
					}
				}
			}

			if ($radius >= $maxR) {
				$radius = $minR;
				$minR = 1;
				if (++$maxR > $lRadius) {
					$maxR = $lRadius;
				}
			} else {
				++$radius;
			}
		}
	}
}
