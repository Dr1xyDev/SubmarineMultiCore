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

namespace pocketmine\level\generator\hell;

use pocketmine\block\Bedrock;
use pocketmine\block\Gravel;
use pocketmine\block\Lava;
use pocketmine\block\NetherQuartzOre;
use pocketmine\block\Netherrack;
use pocketmine\block\SoulSand;
use pocketmine\block\StillLava;
use pocketmine\level\biome\Biome;
use pocketmine\level\biome\BiomeIds;
use pocketmine\level\ChunkManager;
use pocketmine\level\format\Chunk;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\InvalidGeneratorOptionsException;
use pocketmine\level\generator\noise\Simplex;
use pocketmine\level\generator\object\OreType;
use pocketmine\level\generator\populator\GroundFire;
use pocketmine\level\generator\populator\NetherGlowStone;
use pocketmine\level\generator\populator\Ore;
use pocketmine\level\generator\populator\Populator;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

use function abs;

class Nether extends Generator
{
	private int $waterHeight = 32;
	private int $emptyHeight = 64;
	private int $emptyAmplitude = 1;
	private float $density = 0.5;

	/** @var Populator[] */
	private array $populators = [];
	/** @var Populator[] */
	private array $generationPopulators = [];
	private Simplex $noiseBase;

	/**
	 * @throws InvalidGeneratorOptionsException
	 */
	public function init(ChunkManager $level, Random $random) : void
	{
		parent::init($level, $random);

		$this->random->setSeed($this->level->getSeed());
		$this->noiseBase = new Simplex($this->random, 4, 1 / 4, 1 / 64);
		$this->random->setSeed($this->level->getSeed());

		$ores = new Ore();
		$ores->setOreTypes([
			new OreType(new NetherQuartzOre(), new Netherrack(), 20, 16, 0, 128),
			new OreType(new SoulSand(), new Netherrack(), 5, 64, 0, 128),
			new OreType(new Gravel(), new Netherrack(), 5, 64, 0, 128),
			new OreType(new Lava(), new Netherrack(), 1, 16, 0, $this->waterHeight)
		]);

		$this->populators[] = $ores;

		$this->populators[] = new NetherGlowStone();

		$groundFire = new GroundFire();
		$groundFire->setBaseAmount(1);
		$groundFire->setRandomAmount(1);
		$this->populators[] = $groundFire;
	}

	public function getName() : string
	{
		return "nether";
	}

	public function getSettings() : array
	{
		return [];
	}

	public function generateChunk(int $chunkX, int $chunkZ) : void
	{
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());

		$noise = $this->noiseBase->getFastNoise3D(Chunk::EDGE_LENGTH, 128, Chunk::EDGE_LENGTH, 4, 8, 4, $chunkX * Chunk::EDGE_LENGTH, 0, $chunkZ * Chunk::EDGE_LENGTH);

		$chunk = $this->level->getChunk($chunkX, $chunkZ);

		$bedrock = (new Bedrock())->getFullId();
		$netherrack = (new Netherrack())->getFullId();
		$stillLava = (new StillLava())->getFullId();

		for ($x = 0; $x < Chunk::EDGE_LENGTH; ++$x) {
			for ($z = 0; $z < Chunk::EDGE_LENGTH; ++$z) {
				$chunk->setBiomeId($x, $z, BiomeIds::HELL);

				for ($y = 0; $y < 128; ++$y) {
					if ($y === 0 || $y === 127) {
						$chunk->setFullBlock($x, $y, $z, $bedrock);
						continue;
					}
					$noiseValue = (abs($this->emptyHeight - $y) / $this->emptyHeight) * $this->emptyAmplitude - $noise[$x][$z][$y];
					$noiseValue -= 1 - $this->density;

					if ($noiseValue > 0) {
						$chunk->setFullBlock($x, $y, $z, $netherrack);
					} elseif ($y <= $this->waterHeight) {
						$chunk->setFullBlock($x, $y, $z, $stillLava);
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
		return new Vector3(128, 128, 128);
	}
}
