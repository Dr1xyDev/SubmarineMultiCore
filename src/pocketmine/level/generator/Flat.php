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

namespace pocketmine\level\generator;

use InvalidArgumentException;
use pocketmine\block\Block;
use pocketmine\block\CoalOre;
use pocketmine\block\DiamondOre;
use pocketmine\block\Dirt;
use pocketmine\block\GoldOre;
use pocketmine\block\Gravel;
use pocketmine\block\IronOre;
use pocketmine\block\LapisOre;
use pocketmine\block\RedstoneOre;
use pocketmine\block\Stone;
use pocketmine\item\ItemFactory;
use pocketmine\level\ChunkManager;
use pocketmine\level\format\Chunk;
use pocketmine\level\format\SubChunk;
use pocketmine\level\generator\object\OreType;
use pocketmine\level\generator\populator\Ore;
use pocketmine\level\generator\populator\Populator;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

use function array_map;
use function count;
use function explode;
use function preg_match;
use function preg_match_all;

class Flat extends Generator
{
	private Chunk $chunk;
	/** @var Populator[] */
	private array $populators = [];

	private array $structure = [];
	private int $floorLevel;
	private int $biome;

	/** @var mixed[] */
	private array $options;
	private string $preset;

	public function init(ChunkManager $level, Random $random) : void
	{
		parent::init($level, $random);
		$this->generateBaseChunk();
	}

	public function getName() : string
	{
		return "flat";
	}

	public function getSettings() : array
	{
		return $this->options;
	}

	/**
	 * @throws InvalidGeneratorOptionsException
	 */
	public function __construct(array $options = [])
	{
		parent::__construct($options);

		$this->options = $options;
		if (isset($this->options["preset"]) && $this->options["preset"] != "") {
			$this->preset = $this->options["preset"];
		} else {
			$this->preset = "2;7,2x3,2;1;";
			//$this->preset = "2;7,59x1,3x3,2;1;spawn(radius=10 block=89),decoration(treecount=80 grasscount=45)";
		}

		$this->parsePreset();

		if (isset($this->options["decoration"])) {
			$ores = new Ore();
			$stone = new Stone();
			$ores->setOreTypes([
				new OreType(new CoalOre(), $stone, 20, 16, 0, 128),
				new OreType(new IronOre(), $stone, 20, 8, 0, 64),
				new OreType(new RedstoneOre(), $stone, 8, 7, 0, 16),
				new OreType(new LapisOre(), $stone, 1, 6, 0, 32),
				new OreType(new GoldOre(), $stone, 2, 8, 0, 32),
				new OreType(new DiamondOre(), $stone, 1, 7, 0, 16),
				new OreType(new Dirt(), $stone, 20, 32, 0, 128),
				new OreType(new Stone(Stone::GRANITE), $stone, 20, 32, 0, 128),
				new OreType(new Stone(Stone::DIORITE), $stone, 20, 32, 0, 128),
				new OreType(new Stone(Stone::ANDESITE), $stone, 20, 32, 0, 128),
				new OreType(new Gravel(), $stone, 10, 16, 0, 128)
			]);
			$this->populators[] = $ores;
		}
	}

	/**
	 * @return int[][]
	 * @throws InvalidGeneratorOptionsException
	 */
	public static function parseLayers(string $layers) : array
	{
		$result = [];
		$split = array_map('\trim', explode(',', $layers));
		$y = 0;
		foreach ($split as $line) {
			preg_match('#^(?:(\d+)[x|*])?(.+)$#', $line, $matches);
			if (count($matches) !== 3) {
				throw new InvalidGeneratorOptionsException("Invalid preset layer \"$line\"");
			}

			$cnt = $matches[1] !== "" ? (int) $matches[1] : 1;
			try {
				$b = ItemFactory::fromString($matches[2])->getBlock();
			} catch (InvalidArgumentException $e) {
				throw new InvalidGeneratorOptionsException("Invalid preset layer \"$line\": " . $e->getMessage(), 0, $e);
			}
			for ($cY = $y, $y += $cnt; $cY < $y; ++$cY) {
				$result[$cY] = [$b->getId(), $b->getDamage()];
			}
		}

		return $result;
	}

	protected function parsePreset() : void
	{
		$preset = explode(";", $this->preset);
		$blocks = (string) ($preset[1] ?? "");
		$this->biome = (int) ($preset[2] ?? 1);
		$options = (string) ($preset[3] ?? "");
		$this->structure = self::parseLayers($blocks);

		$this->floorLevel = count($this->structure);

		//TODO: more error checking
		preg_match_all('#(([0-9a-z_]{1,})\(?([0-9a-z_ =:]{0,})\)?),?#', $options, $matches);
		foreach ($matches[2] as $i => $option) {
			$params = true;
			if ($matches[3][$i] !== "") {
				$params = [];
				$p = explode(" ", $matches[3][$i]);
				foreach ($p as $k) {
					$k = explode("=", $k);
					if (isset($k[1])) {
						$params[$k[0]] = $k[1];
					}
				}
			}
			$this->options[$option] = $params;
		}
	}

	protected function generateBaseChunk() : void
	{
		$this->chunk = new Chunk(0, 0);
		$this->chunk->setGenerated();

		$structure = $this->structure;
		$count = count($structure);
		for ($sy = 0; $sy < $count; $sy += SubChunk::EDGE_LENGTH) {
			$subchunk = $this->chunk->getSubChunk($sy >> SubChunk::COORD_BIT_SIZE);
			for ($y = 0; $y < SubChunk::EDGE_LENGTH && isset($structure[$y | $sy]); ++$y) {
				[$id, $meta] = $structure[$y | $sy];

				for ($Z = 0; $Z < SubChunk::EDGE_LENGTH; ++$Z) {
					for ($X = 0; $X < SubChunk::EDGE_LENGTH; ++$X) {
						$subchunk->setFullBlock($X, $y, $Z, $id << Block::INTERNAL_METADATA_BITS | $meta);
					}
				}
			}
		}
	}

	public function generateChunk(int $chunkX, int $chunkZ) : void
	{
		$chunk = clone $this->chunk;
		$chunk->setX($chunkX);
		$chunk->setZ($chunkZ);
		$this->level->setChunk($chunkX, $chunkZ, $chunk);
	}

	public function populateChunk(int $chunkX, int $chunkZ) : void
	{
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
		foreach ($this->populators as $populator) {
			$populator->populate($this->level, $chunkX, $chunkZ, $this->random);
		}

	}

	public function getSpawn() : Vector3
	{
		return new Vector3(128, $this->floorLevel, 128);
	}
}
