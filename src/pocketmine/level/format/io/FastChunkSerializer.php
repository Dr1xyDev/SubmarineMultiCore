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

namespace pocketmine\level\format\io;

use pocketmine\level\format\Chunk;
use pocketmine\level\format\SubChunk;
use pocketmine\utils\Binary;
use pocketmine\utils\BinaryStream;
use pocketmine\world\format\PalettedBlockArray;

use function array_values;
use function count;
use function pack;
use function strlen;
use function unpack;

/**
 * This class provides a serializer used for transmitting chunks between threads.
 * The serialization format **is not intended for permanent storage** and may change without warning.
 */
final class FastChunkSerializer
{
	private function __construct()
	{
		//NOOP
	}

	/**
	 * Fast-serializes the chunk for passing between threads
	 * TODO: tiles and entities
	 */
	public static function serializeTerrain(Chunk $chunk) : string
	{
		$stream = new BinaryStream();
		$stream->putInt($chunk->getX());
		$stream->putInt($chunk->getZ());

		$stream->putByte(
			($chunk->isLightPopulated() ? 4 : 0) |
			($chunk->isPopulated() ? 2 : 0) |
			($chunk->isGenerated() ? 1 : 0)
		);

		if ($chunk->isGenerated()) {
			//subchunks
			$subChunks = $chunk->getSubChunks();
			$count = count($subChunks);
			$stream->putByte($count);

			foreach ($subChunks as $y => $subChunk) {
				$stream->putByte($y);
				$stream->putInt($subChunk->getEmptyBlockId());
				$layers = $subChunk->getBlockLayers();
				$stream->putByte(count($layers));
				foreach ($layers as $blocks) {
					$wordArray = $blocks->getWordArray();
					$palette = $blocks->getPalette();

					$stream->putByte($blocks->getBitsPerBlock());
					$stream->put($wordArray);
					$serialPalette = pack("L*", ...$palette);
					$stream->putInt(strlen($serialPalette));
					$stream->put($serialPalette);
				}
			}

			//biomes
			$stream->put($chunk->getBiomeIdArray());

			if ($chunk->isLightPopulated()) {
				$stream->put(pack("v*", ...$chunk->getHeightMapArray()));
			}
		}

		return $stream->getBuffer();
	}

	/**
	 * Deserializes a fast-serialized chunk
	 */
	public static function deserializeTerrain(string $data) : Chunk
	{
		$stream = new BinaryStream($data);
		$x = $stream->getInt();
		$z = $stream->getInt();

		$flags = $stream->getByte();
		$lightPopulated = (bool) ($flags & 4);
		$terrainPopulated = (bool) ($flags & 2);
		$terrainGenerated = (bool) ($flags & 1);

		$subChunks = [];
		$biomeIds = "";
		$heightMap = [];
		if ($terrainGenerated) {
			$count = $stream->getByte();
			for ($subCount = 0; $subCount < $count; ++$subCount) {
				$y = Binary::signByte($stream->getByte());
				$airBlockId = $stream->getInt();

				/** @var PalettedBlockArray[] $layers */
				$layers = [];
				for ($i = 0, $layerCount = $stream->getByte(); $i < $layerCount; ++$i) {
					$bitsPerBlock = $stream->getByte();
					$words = $stream->get(PalettedBlockArray::getExpectedWordArraySize($bitsPerBlock));
					/** @var int[] $unpackedPalette */
					$unpackedPalette = unpack("L*", $stream->get($stream->getInt())); //unpack() will never fail here
					$palette = array_values($unpackedPalette);

					$layers[] = PalettedBlockArray::fromData($bitsPerBlock, $words, $palette);
				}
				$subChunks[$y] = new SubChunk($airBlockId, $layers);
			}

			$biomeIds = $stream->get(256);
			if ($lightPopulated) {
				$heightMap = array_values(unpack("v*", $stream->get(512)));
			}
		}

		$chunk = new Chunk($x, $z, $subChunks, [], [], $biomeIds, $heightMap);
		$chunk->setGenerated($terrainGenerated);
		$chunk->setPopulated($terrainPopulated);
		$chunk->setLightPopulated($lightPopulated);

		return $chunk;
	}
}
