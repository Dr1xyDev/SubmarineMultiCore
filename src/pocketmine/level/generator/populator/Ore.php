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

use pocketmine\level\ChunkManager;
use pocketmine\level\format\Chunk;
use pocketmine\level\generator\object\Ore as ObjectOre;
use pocketmine\level\generator\object\OreType;
use pocketmine\utils\Random;

class Ore implements Populator
{
	/** @var OreType[] */
	private array $oreTypes = [];

	public function populate(ChunkManager $level, int $chunkX, int $chunkZ, Random $random) : void
	{
		foreach ($this->oreTypes as $type) {
			$ore = new ObjectOre($random, $type);
			for ($i = 0; $i < $ore->type->clusterCount; ++$i) {
				$x = $random->nextRange($chunkX << Chunk::COORD_BIT_SIZE, ($chunkX << Chunk::COORD_BIT_SIZE) + Chunk::EDGE_LENGTH - 1);
				$y = $random->nextRange($ore->type->minHeight, $ore->type->maxHeight);
				$z = $random->nextRange($chunkZ << Chunk::COORD_BIT_SIZE, ($chunkZ << Chunk::COORD_BIT_SIZE) + Chunk::EDGE_LENGTH - 1);
				if ($ore->canPlaceObject($level, $x, $y, $z)) {
					$ore->placeObject($level, $x, $y, $z);
				}
			}
		}
	}

	/**
	 * @param OreType[] $types
	 */
	public function setOreTypes(array $types) : void
	{
		$this->oreTypes = $types;
	}
}
