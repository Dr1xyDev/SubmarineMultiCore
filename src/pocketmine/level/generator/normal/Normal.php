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

namespace pocketmine\level\generator\normal;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\CoalOre;
use pocketmine\block\DiamondOre;
use pocketmine\block\Dirt;
use pocketmine\block\GoldOre;
use pocketmine\block\Gravel;
use pocketmine\block\IronOre;
use pocketmine\block\LapisOre;
use pocketmine\block\RedstoneOre;
use pocketmine\block\Stone;
use pocketmine\level\biome\Biome;
use pocketmine\level\biome\BiomeIds;
use pocketmine\level\ChunkManager;
use pocketmine\level\format\Chunk;
use pocketmine\level\generator\biome\BiomeSelector;
use pocketmine\level\generator\Gaussian;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\noise\Simplex;
use pocketmine\level\generator\object\OreType;
use pocketmine\level\generator\populator\GroundCover;
use pocketmine\level\generator\populator\Ore;
use pocketmine\level\generator\populator\Populator;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class Normal extends Generator
{
	private int $waterHeight = 62;

	/** @var Populator[] */
	private array $populators = [];
	/** @var Populator[] */
	private array $generationPopulators = [];
	private Simplex $noiseBase;
	private BiomeSelector $selector;
	private Gaussian $gaussian;

	public function init(ChunkManager $level, Random $random) : void
	{
		parent::init($level, $random);

		$this->gaussian = new Gaussian(2);

		$this->random->setSeed($this->level->getSeed());
		$this->noiseBase = new Simplex($this->random, 4, 1 / 4, 1 / 32);
		$this->random->setSeed($this->level->getSeed());

		$this->selector = new class ($this->random) extends BiomeSelector {
			protected function lookup(float $temperature, float $rainfall) : int
			{
				if ($rainfall < 0.25) {
					if ($temperature < 0.7) {
						return BiomeIds::OCEAN;
					} elseif ($temperature < 0.85) {
						return BiomeIds::RIVER;
					} else {
						return BiomeIds::SWAMPLAND;
					}
				} elseif ($rainfall < 0.60) {
					if ($temperature < 0.25) {
						return BiomeIds::ICE_PLAINS;
					} elseif ($temperature < 0.75) {
						return BiomeIds::PLAINS;
					} else {
						return BiomeIds::DESERT;
					}
				} elseif ($rainfall < 0.80) {
					if ($temperature < 0.25) {
						return BiomeIds::TAIGA;
					} elseif ($temperature < 0.75) {
						return BiomeIds::FOREST;
					} else {
						return BiomeIds::BIRCH_FOREST;
					}
				} else {
					if ($temperature < 0.20) {
						return BiomeIds::EXTREME_HILLS;
					} elseif ($temperature < 0.40) {
						return BiomeIds::EXTREME_HILLS_EDGE;
					} else {
						return BiomeIds::RIVER;
					}
				}
			}
		};

		$this->selector->recalculate();

		$cover = new GroundCover();
		$this->generationPopulators[] = $cover;

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

	public function getName() : string
	{
		return "normal";
	}

	public function getSettings() : array
	{
		return [];
	}

	private function pickBiome(int $x, int $z) : Biome
	{
		$hash = $x * 2345803 ^ $z * 9236449 ^ $this->level->getSeed();
		$hash *= $hash + 223;
		$hash = (int) $hash;
		$xNoise = $hash >> 20 & 3;
		$zNoise = $hash >> 22 & 3;
		if ($xNoise == 3) {
			$xNoise = 1;
		}
		if ($zNoise == 3) {
			$zNoise = 1;
		}

		return $this->selector->pickBiome($x + $xNoise - 1, $z + $zNoise - 1);
	}

	public function generateChunk(int $chunkX, int $chunkZ) : void
	{
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());

		$noise = $this->noiseBase->getFastNoise3D(Chunk::EDGE_LENGTH, 128, Chunk::EDGE_LENGTH, 4, 8, 4, $chunkX * Chunk::EDGE_LENGTH, 0, $chunkZ * Chunk::EDGE_LENGTH);

		$chunk = $this->level->getChunk($chunkX, $chunkZ);

		$biomeCache = [];

		$bedrock = BlockFactory::get(Block::BEDROCK)->getFullId();
		$stone = BlockFactory::get(Block::STONE)->getFullId();
		$stillWater = BlockFactory::get(Block::WATER)->getFullId();

		$baseX = $chunkX * Chunk::EDGE_LENGTH;
		$baseZ = $chunkZ * Chunk::EDGE_LENGTH;
		for ($x = 0; $x < Chunk::EDGE_LENGTH; ++$x) {
			$absoluteX = $baseX + $x;
			for ($z = 0; $z < Chunk::EDGE_LENGTH; ++$z) {
				$absoluteZ = $baseZ + $z;
				$minSum = 0;
				$maxSum = 0;
				$weightSum = 0;

				$biome = $this->pickBiome($absoluteX, $absoluteZ);
				$chunk->setBiomeId($x, $z, $biome->getId());

				for ($sx = -$this->gaussian->smoothSize; $sx <= $this->gaussian->smoothSize; ++$sx) {
					for ($sz = -$this->gaussian->smoothSize; $sz <= $this->gaussian->smoothSize; ++$sz) {

						$weight = $this->gaussian->kernel[$sx + $this->gaussian->smoothSize][$sz + $this->gaussian->smoothSize];

						if ($sx === 0 && $sz === 0) {
							$adjacent = $biome;
						} else {
							$index = Level::chunkHash($absoluteX + $sx, $absoluteZ + $sz);
							if (isset($biomeCache[$index])) {
								$adjacent = $biomeCache[$index];
							} else {
								$biomeCache[$index] = $adjacent = $this->pickBiome($absoluteX + $sx, $absoluteZ + $sz);
							}
						}

						$minSum += ($adjacent->getMinElevation() - 1) * $weight;
						$maxSum += $adjacent->getMaxElevation() * $weight;

						$weightSum += $weight;
					}
				}

				$minSum /= $weightSum;
				$maxSum /= $weightSum;

				$smoothHeight = ($maxSum - $minSum) / 2;

				for ($y = 0; $y < 128; ++$y) {
					if ($y === 0) {
						$chunk->setFullBlock($x, $y, $z, $bedrock);
						continue;
					}

					$noiseValue = $noise[$x][$z][$y] - 1 / $smoothHeight * ($y - $smoothHeight - $minSum);

					if ($noiseValue > 0) {
						$chunk->setFullBlock($x, $y, $z, $stone);
					} elseif ($y <= $this->waterHeight) {
						$chunk->setFullBlock($x, $y, $z, $stillWater);
					}
				}
			}
		}

		foreach ($this->generationPopulators as $populator) {
			$populator->populate($this->level, $chunkX, $chunkZ, $this->random);
		}
	}

	public function populateChunk(int $chunkX, int $chunkZ) : void
	{
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
		foreach ($this->populators as $populator) {
			$populator->populate($this->level, $chunkX, $chunkZ, $this->random);
		}

		$chunk = $this->level->getChunk($chunkX, $chunkZ);
		$biome = Biome::getBiome($chunk->getBiomeId(7, 7));
		$biome->populateChunk($this->level, $chunkX, $chunkZ, $this->random);
	}

	public function getSpawn() : Vector3
	{
		return new Vector3(127.5, 128, 127.5);
	}
}
