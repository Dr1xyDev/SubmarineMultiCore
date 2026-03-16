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
use pocketmine\level\ChunkManager;
use pocketmine\level\format\Chunk;
use pocketmine\utils\Random;

class TallGrass implements Populator
{
	private int $randomAmount = 1;
	private int $baseAmount = 0;

	public function setRandomAmount(int $amount) : void
	{
		$this->randomAmount = $amount;
	}

	public function setBaseAmount(int $amount) : void
	{
		$this->baseAmount = $amount;
	}

	public function populate(ChunkManager $level, int $chunkX, int $chunkZ, Random $random) : void
	{
		$amount = $random->nextRange(0, $this->randomAmount) + $this->baseAmount;

		$block = new \pocketmine\block\TallGrass();
		for ($i = 0; $i < $amount; ++$i) {
			$x = $random->nextRange($chunkX * Chunk::EDGE_LENGTH, $chunkX * Chunk::EDGE_LENGTH + (Chunk::EDGE_LENGTH - 1));
			$z = $random->nextRange($chunkZ * Chunk::EDGE_LENGTH, $chunkZ * Chunk::EDGE_LENGTH + (Chunk::EDGE_LENGTH - 1));
			$y = $this->getHighestWorkableBlock($level, $x, $z);

			if ($y !== -1 && $this->canTallGrassStay($level, $x, $y, $z)) {
				$level->setBlockAt($x, $y, $z, $block);
			}
		}
	}

	private function canTallGrassStay(ChunkManager $level, int $x, int $y, int $z) : bool
	{
		$b = $level->getBlockAt($x, $y, $z)->getId();
		return ($b === Block::AIR || $b === Block::SNOW_LAYER) && $level->getBlockAt($x, $y - 1, $z)->getId() === Block::GRASS;
	}

	private function getHighestWorkableBlock(ChunkManager $level, int $x, int $z) : int
	{
		for ($y = 127; $y >= 0; --$y) {
			$b = $level->getBlockAt($x, $y, $z)->getId();
			if ($b !== Block::AIR && $b !== Block::LEAVES && $b !== Block::LEAVES2 && $b !== Block::SNOW_LAYER) {
				return $y + 1;
			}
		}

		return -1;
	}
}
