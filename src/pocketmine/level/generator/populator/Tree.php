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
use pocketmine\block\utils\TreeType;
use pocketmine\level\ChunkManager;
use pocketmine\level\format\Chunk;
use pocketmine\level\generator\object\TreeFactory;
use pocketmine\utils\Random;

class Tree implements Populator
{
	private int $randomAmount = 1;
	private int $baseAmount = 0;
	private TreeType $type;

	/**
	 * @param TreeType|null $type default oak
	 */
	public function __construct(?TreeType $type = null)
	{
		$this->type = $type ?? TreeType::OAK();
	}

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
		for ($i = 0; $i < $amount; ++$i) {
			$x = $random->nextRange($chunkX << Chunk::COORD_BIT_SIZE, ($chunkX << Chunk::COORD_BIT_SIZE) + Chunk::EDGE_LENGTH);
			$z = $random->nextRange($chunkZ << Chunk::COORD_BIT_SIZE, ($chunkZ << Chunk::COORD_BIT_SIZE) + Chunk::EDGE_LENGTH);
			$y = $this->getHighestWorkableBlock($level, $x, $z);
			if ($y === -1) {
				continue;
			}
			$tree = TreeFactory::get($random, $this->type);
			$transaction = $tree?->getBlockTransaction($level, $x, $y, $z, $random);
			$transaction?->apply();
		}
	}

	private function getHighestWorkableBlock(ChunkManager $level, int $x, int $z) : int
	{
		for ($y = 127; $y >= 0; --$y) {
			$b = $level->getBlockAt($x, $y, $z)->getId();
			if ($b === Block::DIRT || $b === Block::GRASS) {
				return $y + 1;
			} elseif ($b !== Block::AIR && $b !== Block::SNOW_LAYER) {
				return -1;
			}
		}

		return -1;
	}
}
