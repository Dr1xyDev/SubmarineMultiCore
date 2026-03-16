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

use pocketmine\block\BlockFactory;
use pocketmine\item\ItemFactory;
use pocketmine\level\biome\Biome;
use pocketmine\level\Level;
use pocketmine\level\SimpleChunkManager;
use pocketmine\scheduler\AsyncTask;
use pocketmine\thread\NonThreadSafeValue;
use pocketmine\utils\Random;

class GeneratorRegisterTask extends AsyncTask
{
	/** @phpstan-var class-string<Generator> */
	public string $generatorClass;
	public NonThreadSafeValue $settings;
	public int $seed;
	public int $levelId;
	public int $worldHeight;

	/**
	 * @phpstan-param class-string<Generator> $generatorClass
	 */
	public function __construct(Level $level, string $generatorClass, array $generatorSettings = [])
	{
		$this->generatorClass = $generatorClass;
		$this->settings = new NonThreadSafeValue($generatorSettings);
		$this->seed = $level->getSeed();
		$this->levelId = $level->getId();
		$this->worldHeight = $level->getWorldHeight();
	}

	public function onRun() : void
	{
		BlockFactory::init();
		ItemFactory::init();
		Biome::init();

		$manager = new SimpleChunkManager($this->seed, $this->worldHeight);
		ThreadLocalManagerContext::register(new ThreadLocalManagerContext($manager), $this->levelId);

		/**
		 * @var Generator $generator
		 * @see Generator::__construct()
		 */
		$generator = new $this->generatorClass($this->settings->deserialize());
		$generator->init($manager, new Random($manager->getSeed()));
		ThreadLocalGeneratorContext::register(new ThreadLocalGeneratorContext($generator), $this->levelId);
	}
}
