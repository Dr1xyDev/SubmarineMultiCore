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

namespace pocketmine\level;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\level\format\Chunk;

use const INT32_MAX;
use const INT32_MIN;

class SimpleChunkManager implements ChunkManager
{
	/** @var Chunk[] */
	protected $chunks = [];

	public function __construct(
		private int $seed,
		private int $worldHeight
	) {
	}

	public function getBlockAt(int $x, int $y, int $z) : Block
	{
		if ($this->isInWorld($x, $y, $z) && ($chunk = $this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE)) !== null) {
			return BlockFactory::fromFullBlock($chunk->getFullBlock($x & Chunk::COORD_MASK, $y, $z & Chunk::COORD_MASK));
		}
		return new Air();
	}

	public function setBlockAt(int $x, int $y, int $z, Block $block) : bool
	{
		if (($chunk = $this->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE)) !== null) {
			return $chunk->setFullBlock($x & Chunk::COORD_MASK, $y, $z & Chunk::COORD_MASK, $block->getFullId());
		} else {
			return false;
		}
	}

	public function getChunk(int $chunkX, int $chunkZ) : ?Chunk
	{
		return $this->chunks[Level::chunkHash($chunkX, $chunkZ)] ?? null;
	}

	public function setChunk(int $chunkX, int $chunkZ, Chunk $chunk = null) : void
	{
		$this->chunks[Level::chunkHash($chunkX, $chunkZ)] = $chunk;
	}

	public function cleanChunks() : void
	{
		$this->chunks = [];
	}

	/**
	 * Gets the level seed
	 */
	public function getSeed() : int
	{
		return $this->seed;
	}

	public function getWorldHeight() : int
	{
		return $this->worldHeight;
	}

	public function isInWorld(int $x, int $y, int $z) : bool
	{
		return (
			$x <= INT32_MAX && $x >= INT32_MIN &&
			$y < $this->worldHeight && $y >= Level::Y_MIN &&
			$z <= INT32_MAX && $z >= INT32_MIN
		);
	}
}
