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

namespace pocketmine\level\format\io\region;

use pocketmine\block\Block;
use pocketmine\block\BlockIds;
use pocketmine\level\format\io\FormatConverter;
use pocketmine\level\format\SubChunk;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\BinaryStream;
use pocketmine\world\format\PalettedBlockArray;

class Anvil extends RegionLevelProvider
{
	use LegacyAnvilChunkTrait;

	protected function deserializeSubChunk(CompoundTag $subChunk) : SubChunk
	{
		if ($subChunk->hasTag("Data")) {
			return new SubChunk(BlockIds::AIR << Block::INTERNAL_METADATA_BITS, [$this->palettizeLegacySubChunkYZX(
				self::readFixedSizeByteArray($subChunk, "Blocks", 4096),
				self::readFixedSizeByteArray($subChunk, "Data", 2048)
			)]);
		} else {
			$stream = new BinaryStream($subChunk->getByteArray("Blocks"));
			[$emptyBlockId, $blockLayers] = FormatConverter::deserializeBlockLayers($stream);
			return new SubChunk($emptyBlockId, [$this->translatePalette($blockLayers[0] ?? new PalettedBlockArray($emptyBlockId))]);
		}
		//ignore legacy light information
	}

	protected static function getRegionFileExtension() : string
	{
		return "mca";
	}

	public static function getPcWorldFormatVersion() : int
	{
		return 19133;
	}

	public function getWorldHeight() : int
	{
		//TODO: add world height options
		return 256;
	}
}
