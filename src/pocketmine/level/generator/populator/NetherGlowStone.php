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

namespace pocketmine\level\generator\populator;

use pocketmine\block\BlockIds;
use pocketmine\block\Glowstone;
use pocketmine\block\Netherrack;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\object\Ore as ObjectOre;
use pocketmine\level\generator\object\OreType;
use pocketmine\utils\Random;

class NetherGlowStone implements Populator
{
	public function populate(ChunkManager $level, int $chunkX, int $chunkZ, Random $random) : void
	{
		$type = new OreType(new Glowstone(), new Netherrack(), 1, 20, 128, 10);
		$ore = new ObjectOre($random, $type);
		for ($i = 0; $i < $ore->type->clusterCount; ++$i) {
			$x = $random->nextRange($chunkX << 4, ($chunkX << 4) + 15);
			$z = $random->nextRange($chunkZ << 4, ($chunkZ << 4) + 15);
			$y = $this->getHighestWorkableBlock($level, $x, $z);
			$ore->placeObject($level, $x, $y, $z);
		}
	}

	private function getHighestWorkableBlock(ChunkManager $level, int $x, int $z) : int
	{
		for ($y = 127; $y >= 0; --$y) {
			$b = $level->getBlockAt($x, $y, $z)->getId();
			if ($b == BlockIds::AIR) {
				break;
			}
		}

		return $y === 0 ? -1 : ++$y;
	}

}
