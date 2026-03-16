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
use pocketmine\block\Dandelion;
use pocketmine\block\Flower;
use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

use function count;

class TallGrass
{
	public static function growGrass(ChunkManager $world, Vector3 $pos, Random $random, int $count = 15, int $radius = 10) : void
	{
		/** @var Block[] $arr */
		$arr = [
			new Dandelion(),
			new Flower(),
			$tallGrass = new \pocketmine\block\TallGrass(),
			$tallGrass,
			$tallGrass,
			$tallGrass
		];
		$arrC = count($arr) - 1;
		for ($c = 0; $c < $count; ++$c) {
			$x = $random->nextRange($pos->x - $radius, $pos->x + $radius);
			$z = $random->nextRange($pos->z - $radius, $pos->z + $radius);
			if ($world->getBlockAt($x, $pos->y + 1, $z)->getId() === Block::AIR && $world->getBlockAt($x, $pos->y, $z)->getId() === Block::GRASS) {
				$world->setBlockAt($x, $pos->y + 1, $z, $arr[$random->nextRange(0, $arrC)]);
			}
		}
	}
}
