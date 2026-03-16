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

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\Liquid;
use pocketmine\level\biome\Biome;
use pocketmine\level\ChunkManager;
use pocketmine\level\format\Chunk;
use pocketmine\utils\Random;

use function count;
use function min;

class GroundCover implements Populator
{
	public function populate(ChunkManager $level, int $chunkX, int $chunkZ, Random $random) : void
	{
		$chunk = $level->getChunk($chunkX, $chunkZ);
		for ($x = 0; $x < Chunk::EDGE_LENGTH; ++$x) {
			for ($z = 0; $z < Chunk::EDGE_LENGTH; ++$z) {
				$biome = Biome::getBiome($chunk->getBiomeId($x, $z));
				$cover = $biome->getGroundCover();
				if (count($cover) > 0) {
					$diffY = 0;
					if (!$cover[0]->isSolid()) {
						$diffY = 1;
					}

					$startY = 127;
					for (; $startY > 0; --$startY) {
						if (!BlockFactory::fromFullBlock($chunk->getFullBlock($x, $startY, $z))->isTransparent()) {
							break;
						}
					}
					$startY = min(127, $startY + $diffY);
					$endY = $startY - count($cover);
					for ($y = $startY; $y > $endY && $y >= 0; --$y) {
						$b = $cover[$startY - $y];
						$id = BlockFactory::fromFullBlock($chunk->getFullBlock($x, $y, $z));
						if ($id->getId() === Block::AIR && $b->isSolid()) {
							break;
						}
						if ($b->canBeFlowedInto() && $id instanceof Liquid) {
							continue;
						}

						$chunk->setFullBlock($x, $y, $z, $b->getFullId());
					}
				}
			}
		}
	}
}
