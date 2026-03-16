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

use pocketmine\block\BlockFactory;
use pocketmine\level\generator\GeneratorManager;
use pocketmine\level\generator\normal\Normal;
use pocketmine\level\LevelCreationOptions;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\utils\BinaryStream;
use pocketmine\utils\Filesystem;
use pocketmine\world\format\PalettedBlockArray;
use Symfony\Component\Filesystem\Path;

use function array_values;
use function basename;
use function chr;
use function crc32;
use function file_exists;
use function floor;
use function microtime;
use function mkdir;
use function ord;
use function random_bytes;
use function rename;
use function round;
use function rtrim;
use function substr;
use function unpack;

use const DIRECTORY_SEPARATOR;

class FormatConverter
{
	private string $backupPath;
	private \Logger $logger;

	public function __construct(
		private LevelProvider $oldProvider,
		private WritableLevelProviderManagerEntry $newProvider,
		string $backupPath,
		\Logger $logger,
		private int $chunksPerProgressUpdate = 256
	) {
		$this->logger = new \PrefixedLogger($logger, "World Converter: " . $this->oldProvider->getLevelData()->getName());

		if (!file_exists($backupPath)) {
			@mkdir($backupPath, 0777, true);
		}
		$nextSuffix = "";
		do {
			$this->backupPath = Path::join($backupPath, basename($this->oldProvider->getPath()) . $nextSuffix);
			$nextSuffix = "_" . crc32(random_bytes(4));
		} while (file_exists($this->backupPath));
	}

	public function getBackupPath() : string
	{
		return $this->backupPath;
	}

	public function execute() : LevelProvider
	{
		$new = $this->generateNew();

		$this->populateLevelData($new->getLevelData());
		$this->convertTerrain($new);

		$path = $this->oldProvider->getPath();
		$this->oldProvider->close();
		$new->close();

		$this->logger->info("Backing up pre-conversion world to " . $this->backupPath);
		if (!@rename($path, $this->backupPath)) {
			$this->logger->warning("Moving old world files for backup failed, attempting copy instead. This might take a long time.");
			Filesystem::recursiveCopy($path, $this->backupPath);
			Filesystem::recursiveUnlink($path);
		}
		if (!@rename($new->getPath(), $path)) {
			//we don't expect this to happen because worlds/ should most likely be all on the same FS, but just in case...
			$this->logger->debug("Relocation of new world files to location failed, attempting copy and delete instead");
			Filesystem::recursiveCopy($new->getPath(), $path);
			Filesystem::recursiveUnlink($new->getPath());
		}

		$this->logger->info("Conversion completed");
		return $this->newProvider->fromPath($path);
	}

	private function generateNew() : WritableLevelProvider
	{
		$this->logger->info("Generating new world");
		$data = $this->oldProvider->getLevelData();

		$convertedOutput = rtrim($this->oldProvider->getPath(), "/" . DIRECTORY_SEPARATOR) . "_converted" . DIRECTORY_SEPARATOR;
		if (file_exists($convertedOutput)) {
			$this->logger->info("Found previous conversion attempt, deleting...");
			Filesystem::recursiveUnlink($convertedOutput);
		}
		$this->newProvider->generate(
			$convertedOutput,
			$data->getName(),
			LevelCreationOptions::create()
			//TODO: defaulting to NORMAL here really isn't very good behaviour, but it's consistent with what we already
			//did previously; besides, WorldManager checks for unknown generators before this is reached anyway.
			->setGeneratorClass(GeneratorManager::getGenerator($data->getGenerator()) ?? Normal::class)
			->setGeneratorOptions("")
			->setSeed($data->getSeed())
			->setSpawnPosition($data->getSpawn())
			->setDifficulty($data->getDifficulty())
		);

		return $this->newProvider->fromPath($convertedOutput);
	}

	private function populateLevelData(LevelData $data) : void
	{
		$this->logger->info("Converting world manifest");
		$oldData = $this->oldProvider->getLevelData();
		$data->setDifficulty($oldData->getDifficulty());
		$data->setLightningLevel($oldData->getLightningLevel());
		$data->setLightningTime($oldData->getLightningTime());
		$data->setRainLevel($oldData->getRainLevel());
		$data->setRainTime($oldData->getRainTime());
		$data->setSpawn($oldData->getSpawn());
		$data->setTime($oldData->getTime());

		$data->save();
		$this->logger->info("Finished converting manifest");
		//TODO: add more properties as-needed
	}

	private function convertTerrain(WritableLevelProvider $new) : void
	{
		$this->logger->info("Calculating chunk count");
		$count = $this->oldProvider->calculateChunkCount();
		$this->logger->info("Discovered $count chunks");

		$counter = 0;

		$start = microtime(true);
		$thisRound = $start;
		foreach ($this->oldProvider->getAllChunks(true, $this->logger) as $coords => $chunk) {
			[$chunkX, $chunkZ] = $coords;
			$chunk->setChanged();
			$new->saveChunk($chunk);
			$counter++;
			if (($counter % $this->chunksPerProgressUpdate) === 0) {
				$time = microtime(true);
				$diff = $time - $thisRound;
				$thisRound = $time;
				$this->logger->info("Converted $counter / $count chunks (" . floor($this->chunksPerProgressUpdate / $diff) . " chunks/sec)");
			}
		}
		$total = microtime(true) - $start;
		$this->logger->info("Converted $counter / $counter chunks in " . round($total, 3) . " seconds (" . floor($counter / $total) . " chunks/sec)");
	}

	/**
	 * @return string[]
	 */
	public static function convertSubChunkFromPaletteXZY(PalettedBlockArray $palettedBlockArray, int $protocol = ProtocolInfo::PROTOCOL_110) : array
	{
		$idArray = "";
		$metaArray = "";
		for ($x = 0; $x < 16; ++$x) {
			for ($z = 0; $z < 16; ++$z) {
				for ($y = 0; $y < 16; ++$y) {
					$block = BlockFactory::fromFullBlock($palettedBlockArray->get($x, $y, $z));
					$block = $block->getBlockProtocol($protocol) ?? $block;
					[$legacyId, $legacyMeta] = [$block->getId(), $block->getDamage()];
					if ($legacyId > 255) {
						$legacyId = 248; //minecraft:info_update
						$legacyMeta = 0;
					}

					$idArray[($x << 8) | ($z << 4) | $y] = chr($legacyId);
					$indexData = ($x << 7) | ($z << 3) | ($y >> 1);
					if (($y & 1) === 0) {
						$metaArray[$indexData] = chr((ord($metaArray[$indexData] ?? chr(0)) & 0xf0) | ($legacyMeta & 0x0f));
					} else {
						$metaArray[$indexData] = chr((($legacyMeta & 0x0f) << 4) | (ord($metaArray[$indexData] ?? chr(0)) & 0x0f));
					}
				}
			}
		}

		return [$idArray, $metaArray];
	}

	/**
	 * @return string[]
	 */
	public static function convertSubChunkFromPaletteYZX(PalettedBlockArray $palettedBlockArray, int $protocol = ProtocolInfo::PROTOCOL_110) : array
	{
		[$idArray, $metaArray] = self::convertSubChunkFromPaletteXZY($palettedBlockArray, $protocol);
		return [ChunkUtils::reorderByteArray($idArray) . ChunkUtils::reorderNibbleArray($metaArray)];
	}

	/**
	 * @param PalettedBlockArray[] $palettedBlocks
	 *
	 * @return string[]
	 */
	public static function convertSubChunkFromPaletteColumn(array $palettedBlocks, int $protocol = ProtocolInfo::PROTOCOL_110) : array
	{
		$ids = "";
		$data = "";

		$yOffset = 0;
		foreach ($palettedBlocks as $palettedBlockArray) {
			[$idArray, $metaArray] = self::convertSubChunkFromPaletteXZY($palettedBlockArray, $protocol);

			$offset = ($yOffset << 4);
			for ($i = 0; $i < 256; ++$i) {
				$ids .= substr($idArray, $offset, 16);
				$offset += 128;
			}

			$offset = ($yOffset << 3);
			for ($i = 0; $i < 256; ++$i) {
				$data .= substr($metaArray, $offset, 8);
				$offset += 64;
			}

			$yOffset++;
		}

		return [$ids, $data];
	}

	public static function deserializeBlockLayers(BinaryStream $stream) : array
	{
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

		return [$airBlockId, $layers];
	}
}
