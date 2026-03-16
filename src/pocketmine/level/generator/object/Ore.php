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

use pocketmine\level\ChunkManager;
use pocketmine\math\VectorMath;
use pocketmine\utils\Random;

use function sin;

use const M_PI;

class Ore
{
	public function __construct(
		private Random $random,
		public OreType $type
	) {
	}

	public function getType() : OreType
	{
		return $this->type;
	}

	public function canPlaceObject(ChunkManager $world, int $x, int $y, int $z) : bool
	{
		return $world->getBlockAt($x, $y, $z)->isSameType($this->type->replaces);
	}

	public function placeObject(ChunkManager $world, int $x, int $y, int $z) : void
	{
		$clusterSize = $this->type->clusterSize;
		$angle = $this->random->nextFloat() * M_PI;
		$offset = VectorMath::getDirection2D($angle)->multiply($clusterSize / 8);
		$x1 = $x + 8 + $offset->x;
		$x2 = $x + 8 - $offset->x;
		$z1 = $z + 8 + $offset->y;
		$z2 = $z + 8 - $offset->y;
		$y1 = $y + $this->random->nextBoundedInt(3) + 2;
		$y2 = $y + $this->random->nextBoundedInt(3) + 2;
		for ($count = 0; $count <= $clusterSize; ++$count) {
			$seedX = $x1 + ($x2 - $x1) * $count / $clusterSize;
			$seedY = $y1 + ($y2 - $y1) * $count / $clusterSize;
			$seedZ = $z1 + ($z2 - $z1) * $count / $clusterSize;
			$size = ((sin($count * (M_PI / $clusterSize)) + 1) * $this->random->nextFloat() * $clusterSize / 16 + 1) / 2;

			$startX = (int) ($seedX - $size);
			$startY = (int) ($seedY - $size);
			$startZ = (int) ($seedZ - $size);
			$endX = (int) ($seedX + $size);
			$endY = (int) ($seedY + $size);
			$endZ = (int) ($seedZ + $size);

			for ($xx = $startX; $xx <= $endX; ++$xx) {
				$sizeX = ($xx + 0.5 - $seedX) / $size;
				$sizeX *= $sizeX;

				if ($sizeX < 1) {
					for ($yy = $startY; $yy <= $endY; ++$yy) {
						$sizeY = ($yy + 0.5 - $seedY) / $size;
						$sizeY *= $sizeY;

						if ($yy > 0 && ($sizeX + $sizeY) < 1) {
							for ($zz = $startZ; $zz <= $endZ; ++$zz) {
								$sizeZ = ($zz + 0.5 - $seedZ) / $size;
								$sizeZ *= $sizeZ;

								if (($sizeX + $sizeY + $sizeZ) < 1 && $world->getBlockAt($xx, $yy, $zz)->isSameType($this->type->replaces)) {
									$world->setBlockAt($xx, $yy, $zz, $this->type->material);
								}
							}
						}
					}
				}
			}
		}
	}
}
