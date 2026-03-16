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

use pocketmine\block\Block;
use pocketmine\level\format\io\exception\CorruptedLevelException;
use pocketmine\level\format\io\exception\UnsupportedLevelFormatException;
use pocketmine\level\LevelException;
use pocketmine\world\format\io\SubChunkConverter;
use pocketmine\world\format\PalettedBlockArray;

use function file_exists;

abstract class BaseLevelProvider implements LevelProvider
{
	protected string $path;
	protected LevelData $levelData;

	public function __construct(string $path)
	{
		if (!file_exists($path)) {
			throw new LevelException("World does not exist");
		}

		$this->path = $path;
		$this->levelData = $this->loadLevelData();
	}

	/**
	 * @throws CorruptedLevelException
	 * @throws UnsupportedLevelFormatException
	 */
	abstract protected function loadLevelData() : LevelData;

	protected function translatePalette(PalettedBlockArray $blockArray) : PalettedBlockArray
	{
		$palette = $blockArray->getPalette();

		$newPalette = [];
		foreach ($palette as $k => $legacyIdMeta) {
			//TODO: remember data for unknown states so we can implement them later
			$id = $legacyIdMeta >> 4;
			$meta = $legacyIdMeta & 0xf;

			$newPalette[$k] = ($id << Block::INTERNAL_METADATA_BITS) | $meta;
		}

		//TODO: this is sub-optimal since it reallocates the offset table multiple times
		return PalettedBlockArray::fromData(
			$blockArray->getBitsPerBlock(),
			$blockArray->getWordArray(),
			$newPalette
		);
	}

	protected function palettizeLegacySubChunkXZY(string $idArray, string $metaArray) : PalettedBlockArray
	{
		return $this->translatePalette(SubChunkConverter::convertSubChunkXZY($idArray, $metaArray));
	}

	protected function palettizeLegacySubChunkYZX(string $idArray, string $metaArray) : PalettedBlockArray
	{
		return $this->translatePalette(SubChunkConverter::convertSubChunkYZX($idArray, $metaArray));
	}

	protected function palettizeLegacySubChunkFromColumn(string $idArray, string $metaArray, int $yOffset) : PalettedBlockArray
	{
		return $this->translatePalette(SubChunkConverter::convertSubChunkFromLegacyColumn($idArray, $metaArray, $yOffset));
	}

	public function getPath() : string
	{
		return $this->path;
	}

	public function getLevelData() : LevelData
	{
		return $this->levelData;
	}
}
