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

/**
 * Noise classes used in Levels
 */

namespace pocketmine\level\generator;

use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\utils\Utils;
use ReflectionClass;

use function preg_match;

abstract class Generator
{
	/**
	 * Converts a string level seed into an integer for use by the generator.
	 */
	public static function convertSeed(string $seed) : ?int
	{
		if ($seed === "") { //empty seed should cause a random seed to be selected - can't use 0 here because 0 is a valid seed
			$convertedSeed = null;
		} elseif (preg_match('/^-?\d+$/', $seed) === 1) { //this avoids treating seeds like "404.4" as integer seeds
			$convertedSeed = (int) $seed;
		} else {
			$convertedSeed = Utils::javaStringHash($seed);
		}

		return $convertedSeed;
	}

	protected ChunkManager $level;
	protected Random $random;

	/**
	 * @throws InvalidGeneratorOptionsException
	 */
	public function __construct(array $settings = [])
	{
		//NOOP
	}

	public function init(ChunkManager $level, Random $random) : void
	{
		$this->level = $level;
		$this->random = $random;
	}

	abstract public function generateChunk(int $chunkX, int $chunkZ) : void;

	abstract public function populateChunk(int $chunkX, int $chunkZ) : void;

	public function getSettings() : array
	{
		return [];
	}

	public function getName() : string
	{
		return (new ReflectionClass($this))->getShortName();
	}

	abstract public function getSpawn() : Vector3;
}
