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

namespace pocketmine\level\biome;

use pocketmine\block\Block;
use pocketmine\block\utils\TreeType;
use pocketmine\entity\Animal;
use pocketmine\entity\Creature;
use pocketmine\entity\CreatureType;
use pocketmine\entity\hostile\Creeper;
use pocketmine\entity\hostile\Skeleton;
use pocketmine\entity\hostile\Slime;
use pocketmine\entity\hostile\Spider;
use pocketmine\entity\hostile\Zombie;
use pocketmine\entity\Monster;
use pocketmine\entity\passive\Chicken;
use pocketmine\entity\passive\Cow;
use pocketmine\entity\passive\Pig;
use pocketmine\entity\passive\Sheep;
use pocketmine\entity\passive\Squid;
use pocketmine\entity\WaterAnimal;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\populator\Populator;
use pocketmine\utils\Random;

abstract class Biome
{
	public const MAX_BIOMES = 256;

	/**
	 * @var Biome[]|\SplFixedArray
	 * @phpstan-var \SplFixedArray<Biome>
	 */
	private static \SplFixedArray $biomes;

	private static bool $initialized = false;

	private int $id;
	private bool $registered = false;

	/** @var Populator[] */
	private array $populators = [];

	private int $minElevation;
	private int $maxElevation;

	/** @var Block[] */
	private array $groundCover = [];

	protected float $rainfall = 0.5;
	protected float $temperature = 0.5;

	/** @var SpawnListEntry[] */
	protected array $spawnableMonsterList = [];
	/** @var SpawnListEntry[] */
	protected array $spawnableCreatureList = [];
	/** @var SpawnListEntry[] */
	protected array $spawnableWaterCreatureList = [];
	/** @var SpawnListEntry[] */
	protected array $spawnableCaveCreatureList = [];

	public static function init() : void
	{
		if (!self::$initialized) {
			self::$initialized = true;

			self::$biomes = new \SplFixedArray(Biome::MAX_BIOMES);

			self::register(BiomeIds::OCEAN, new OceanBiome());
			self::register(BiomeIds::PLAINS, new PlainBiome());
			self::register(BiomeIds::DESERT, new DesertBiome());
			self::register(BiomeIds::EXTREME_HILLS, new MountainsBiome());
			self::register(BiomeIds::FOREST, new ForestBiome());
			self::register(BiomeIds::TAIGA, new TaigaBiome());
			self::register(BiomeIds::SWAMPLAND, new SwampBiome());
			self::register(BiomeIds::RIVER, new RiverBiome());

			self::register(BiomeIds::HELL, new HellBiome());
			self::register(BiomeIds::THE_END, new EndBiome());

			self::register(BiomeIds::ICE_PLAINS, new IcePlainsBiome());

			self::register(BiomeIds::EXTREME_HILLS_EDGE, new SmallMountainsBiome());

			self::register(BiomeIds::BIRCH_FOREST, new ForestBiome(TreeType::BIRCH()));
		}
	}

	protected static function register(int $id, Biome $biome) : void
	{
		self::$biomes[$id] = $biome;
		$biome->setId($id);
	}

	public static function getBiome(int $id) : Biome
	{
		if (self::$biomes[$id] === null) {
			self::register($id, new UnknownBiome());
		}

		return self::$biomes[$id];
	}

	public function __construct()
	{
		$this->spawnableCreatureList[] = new SpawnListEntry(Sheep::class, 12, 4, 4);
		$this->spawnableCreatureList[] = new SpawnListEntry(Pig::class, 10, 4, 4);
		$this->spawnableCreatureList[] = new SpawnListEntry(Chicken::class, 10, 4, 4);
		$this->spawnableCreatureList[] = new SpawnListEntry(Cow::class, 8, 4, 4);
		$this->spawnableMonsterList[] = new SpawnListEntry(Spider::class, 100, 4, 4);
		$this->spawnableMonsterList[] = new SpawnListEntry(Zombie::class, 100, 4, 4);
		$this->spawnableMonsterList[] = new SpawnListEntry(Skeleton::class, 100, 4, 4);
		$this->spawnableMonsterList[] = new SpawnListEntry(Creeper::class, 100, 4, 4);
		$this->spawnableMonsterList[] = new SpawnListEntry(Slime::class, 100, 4, 4);
		$this->spawnableWaterCreatureList[] = new SpawnListEntry(Squid::class, 10, 4, 4);
	}

	public function clearPopulators() : void
	{
		$this->populators = [];
	}

	public function addPopulator(Populator $populator) : void
	{
		$this->populators[] = $populator;
	}

	public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ, Random $random) : void
	{
		foreach ($this->populators as $populator) {
			$populator->populate($world, $chunkX, $chunkZ, $random);
		}
	}

	/**
	 * @return Populator[]
	 */
	public function getPopulators() : array
	{
		return $this->populators;
	}

	public function setId(int $id) : void
	{
		if (!$this->registered) {
			$this->registered = true;
			$this->id = $id;
		}
	}

	public function getId() : int
	{
		return $this->id;
	}

	abstract public function getName() : string;

	public function getMinElevation() : int
	{
		return $this->minElevation;
	}

	public function getMaxElevation() : int
	{
		return $this->maxElevation;
	}

	public function setElevation(int $min, int $max) : void
	{
		$this->minElevation = $min;
		$this->maxElevation = $max;
	}

	/**
	 * @return Block[]
	 */
	public function getGroundCover() : array
	{
		return $this->groundCover;
	}

	/**
	 * @param Block[] $covers
	 */
	public function setGroundCover(array $covers) : void
	{
		$this->groundCover = $covers;
	}

	public function getTemperature() : float
	{
		return $this->temperature;
	}

	public function getRainfall() : float
	{
		return $this->rainfall;
	}

	/**
	 * @return SpawnListEntry[]
	 */
	public function getSpawnableList(CreatureType $creatureType) : array
	{
		$entityClass = $creatureType->getCreatureClass();
		return match ($entityClass) {
			WaterAnimal::class => $this->spawnableWaterCreatureList,
			Creature::class => $this->spawnableCaveCreatureList,
			Animal::class => $this->spawnableCreatureList,
			Monster::class => $this->spawnableMonsterList,
			default => [],
		};

	}

	public function getSpawningChance() : float
	{
		return 0.1;
	}
}
