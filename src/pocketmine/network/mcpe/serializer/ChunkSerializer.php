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

namespace pocketmine\network\mcpe\serializer;

use pocketmine\block\Block;
use pocketmine\level\biome\BiomeIds;
use pocketmine\level\format\Chunk;
use pocketmine\level\format\io\FormatConverter;
use pocketmine\level\format\SubChunkInterface;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Binary;
use pocketmine\utils\BinaryStream;
use pocketmine\world\format\PalettedBlockArray;

use function array_flip;
use function chr;
use function count;
use function file_get_contents;
use function is_array;
use function json_decode;
use function pack;
use function str_repeat;

final class ChunkSerializer
{
	public static ?string $emptySkyLight = null;
	public static ?string $emptyBlockLight = null;

	private function __construct()
	{
		//NOOP
	}

	/**
	 * Returns the min/max subchunk index expected in the protocol.
	 * This has no relation to the world height supported by PM.
	 *
	 * @phpstan-param DimensionIds::* $dimensionId
	 * @return int[]
	 * @phpstan-return array{int, int}
	 */
	public static function getDimensionChunkBounds(int $dimensionId, int $playerProtocol) : array
	{
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_475) {
			return match ($dimensionId) {
				DimensionIds::OVERWORLD => [-4, 19],
				DimensionIds::NETHER => [0, 7],
				DimensionIds::THE_END => [0, 15],
				default => throw new \InvalidArgumentException("Unknown dimension ID $dimensionId"),
			};
		}

		return match ($dimensionId) {
			DimensionIds::OVERWORLD, DimensionIds::THE_END => [0, 15],
			DimensionIds::NETHER => [0, 7],
			default => throw new \InvalidArgumentException("Unknown dimension ID $dimensionId"),
		};
	}

	/**
	 * Returns the number of subchunks that will be sent from the given chunk.
	 * Chunks are sent in a stack, so every chunk below the top non-empty one must be sent.
	 *
	 * @phpstan-param DimensionIds::* $dimensionId
	 */
	public static function getSubChunkCount(Chunk $chunk, int $dimensionId, int $playerProtocol) : int
	{
		//if the protocol world bounds ever exceed the PM supported bounds again in the future, we might need to
		//polyfill some stuff here
		[$minSubChunkIndex, $maxSubChunkIndex] = self::getDimensionChunkBounds($dimensionId, $playerProtocol);
		for ($y = $maxSubChunkIndex, $count = $maxSubChunkIndex - $minSubChunkIndex + 1; $y >= $minSubChunkIndex; --$y, --$count) {
			if ($chunk->getSubChunk($y)->isEmpty(false)) {
				continue;
			}
			return $count;
		}

		return 0;
	}

	/**
	 * Serializes the chunk for sending to players
	 */
	public static function serializeFullChunk(Chunk $chunk, int $playerProtocol, \Closure|null $legacyToRuntime, int $dimensionId, bool $fast) : string
	{
		$stream = new BinaryStream();

		$subChunkCount = self::getSubChunkCount($chunk, $dimensionId, $playerProtocol);
        if ($playerProtocol < ProtocolInfo::PROTOCOL_361) {
			$stream->putByte($subChunkCount);
		}

		$writtenCount = 0;

		[$minSubChunkIndex, $maxSubChunkIndex] = self::getDimensionChunkBounds($dimensionId, $playerProtocol);
		for ($y = $minSubChunkIndex; $writtenCount < $subChunkCount; ++$y, ++$writtenCount) {
			self::serializeSubChunk($chunk->getSubChunk($y), $legacyToRuntime, $playerProtocol, $stream, $fast);
		}

		if ($playerProtocol >= ProtocolInfo::PROTOCOL_475) {
			//TODO: right now we don't support 3D natively, so we just 3Dify our 2D biomes so they fill the column
			$encodedBiomePalette = self::networkSerializeBiomesAsPalette($chunk);
			for ($y = $minSubChunkIndex; $y <= $maxSubChunkIndex; ++$y) {
				$stream->put($encodedBiomePalette);
			}
		} else {
			if ($playerProtocol < ProtocolInfo::PROTOCOL_361) {
				$stream->put(pack("v*", ...$chunk->getHeightMapArray()));
			}

			$stream->put($chunk->getBiomeIdArray());
		}

        if ($fast) {
            $stream->putVarInt(PHP_INT_MAX); //wtf? fast chunks
        }

		$stream->putByte(0); //border block array count
		//Border block entry format: 1 byte (4 bits X, 4 bits Z). These are however useless since they crash the regular client.

		if ($playerProtocol < ProtocolInfo::PROTOCOL_274) {
            $stream->putVarInt(0); // extraData (WTF)
		}

		return $stream->getBuffer();
	}

	public static function serializeSubChunk(SubChunkInterface $subChunk, \Closure|null $legacyToRuntime, int $playerProtocol, BinaryStream $stream, bool $fast) : string
	{
		if ($legacyToRuntime === null) {
			$stream->putByte(0); //storage version

			[$blockIdArray, $blockDataArray] = FormatConverter::convertSubChunkFromPaletteXZY($subChunk->getBlockLayers()[0] ?? new PalettedBlockArray(Block::AIR), $playerProtocol);
			$stream->put($blockIdArray . $blockDataArray);

			if ($playerProtocol < ProtocolInfo::PROTOCOL_137) {
				if (self::$emptySkyLight === null) {
					self::$emptySkyLight = str_repeat("\xff", 2048);
				}

				if (self::$emptyBlockLight === null) {
					self::$emptyBlockLight = str_repeat("\x00", 2048);
				}

				// HACK! No shadows for 1.1
				$stream->put(self::$emptySkyLight); // sky light
				$stream->put(self::$emptyBlockLight); // block light
			}
		} else {
			$stream->putByte($fast ? 0 : 8); // storage version

			$blockLayers = $subChunk->getBlockLayers();
			$stream->putByte(count($blockLayers)); // layer count

			foreach ($blockLayers as $blocks) {
				// 1 is network format (palette out of runtimeIDs), 0 is storage format (palette out of NBT tags)
				if ($blocks->getBitsPerBlock() === 0) {
					//TODO: we use these in memory, but the game doesn't support them yet
					//polyfill them with 1-bpb instead
					$bitsPerBlock = 1;
					$words = str_repeat("\x00", PalettedBlockArray::getExpectedWordArraySize(1));
				} else {
					$bitsPerBlock = $blocks->getBitsPerBlock();
					$words = $blocks->getWordArray();
				}
				$stream->putByte(($bitsPerBlock << 1) | 1);
				$stream->put($words);
				$palette = $blocks->getPalette();

				//these LSHIFT by 1 uvarints are optimizations: the client expects zigzag varints here
				//but since we know they are always unsigned, we can avoid the extra fcall overhead of
				//zigzag and just shift directly.
				$stream->putUnsignedVarInt(count($palette) << 1);
				foreach ($palette as $fullBlock) {
					$runtimeId = $legacyToRuntime($fullBlock);
					$stream->putUnsignedVarInt($runtimeId << 1);
				}
			}
		}

		return $stream->getBuffer();
	}

	private static function networkSerializeBiomesAsPalette(Chunk $chunk) : string
	{
		/** @var string[]|null $biomeIdMap */
		static $biomeIdMap = null;
		if ($biomeIdMap === null) {
			$biomeIdMapRaw = file_get_contents(\pocketmine\BEDROCK_DATA_PATH . 'biome_id_map.json');
			if ($biomeIdMapRaw === false) {
				throw new AssumptionFailedError();
			}
			$biomeIdMapDecoded = json_decode($biomeIdMapRaw, true);
			if (!is_array($biomeIdMapDecoded)) {
				throw new AssumptionFailedError();
			}
			$biomeIdMap = array_flip($biomeIdMapDecoded);
		}
		$biomePalette = new PalettedBlockArray($chunk->getBiomeId(0, 0));
		for ($x = 0; $x < 16; ++$x) {
			for ($z = 0; $z < 16; ++$z) {
				$biomeId = $chunk->getBiomeId($x, $z);
				if (!isset($biomeIdMap[$biomeId])) {
					//make sure we aren't sending bogus biomes - the 1.18.0 client crashes if we do this
					$biomeId = BiomeIds::OCEAN;
				}
				for ($y = 0; $y < 16; ++$y) {
					$biomePalette->set($x, $y, $z, $biomeId);
				}
			}
		}

		$biomePaletteBitsPerBlock = $biomePalette->getBitsPerBlock();
		$encodedBiomePalette =
			chr(($biomePaletteBitsPerBlock << 1) | 1) . //the last bit is non-persistence (like for blocks), though it has no effect on biomes since they always use integer IDs
			$biomePalette->getWordArray();

		//these LSHIFT by 1 uvarints are optimizations: the client expects zigzag varints here
		//but since we know they are always unsigned, we can avoid the extra fcall overhead of
		//zigzag and just shift directly.
		$biomePaletteArray = $biomePalette->getPalette();
		if ($biomePaletteBitsPerBlock !== 0) {
			$encodedBiomePalette .= Binary::writeUnsignedVarInt(count($biomePaletteArray) << 1);
		}
		foreach ($biomePaletteArray as $p) {
			$encodedBiomePalette .= Binary::writeUnsignedVarInt($p << 1);
		}

		return $encodedBiomePalette;
	}

}
