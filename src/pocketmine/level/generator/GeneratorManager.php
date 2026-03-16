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
use pocketmine\level\generator\end\End;
use pocketmine\level\generator\hell\Nether;
use pocketmine\level\generator\normal\Normal;
use pocketmine\utils\Utils;

use function array_keys;
use function strtolower;

final class GeneratorManager
{
	/**
	 * @var string[] name => classname mapping
	 * @phpstan-var array<string, string>
	 */
	private static array $list = [];

	/**
	 * Registers the default known generators.
	 */
	public static function registerDefaultGenerators() : void
	{
		self::addGenerator(Flat::class, "flat");
		self::addGenerator(Normal::class, "normal");
		self::addGenerator(Normal::class, "default");
		self::addGenerator(Nether::class, "hell");
		self::addGenerator(Nether::class, "nether");
		self::addGenerator(End::class, "end");
		self::addGenerator(VoidGenerator::class, "void");
	}

	/**
	 * @param string $class     Fully qualified name of class that extends \pocketmine\level\generator\Generator
	 * @param string $name      Alias for this generator type that can be written in configs
	 * @param bool   $overwrite Whether to force overwriting any existing registered generator with the same name
	 *
	 * @phpstan-param class-string<Generator> $class
	 */
	public static function addGenerator(string $class, string $name, bool $overwrite = false) : void
	{
		Utils::testValidInstance($class, Generator::class);

		$name = strtolower($name);
		if (!$overwrite && isset(self::$list[$name])) {
			throw new InvalidArgumentException("Alias \"$name\" is already assigned");
		}

		self::$list[$name] = $class;
	}

	/**
	 * Returns a list of names for registered generators.
	 *
	 * @return string[]
	 */
	public static function getGeneratorList() : array
	{
		return array_keys(self::$list);
	}

	/**
	 * Returns the generator entry of a registered Generator matching the given name, or null if not found.
	 */
	public static function getGenerator(string $name, bool $throwOnMissing = false) : string
	{
		if (isset(self::$list[$name = strtolower($name)])) {
			return self::$list[$name];
		}

		if ($throwOnMissing) {
			throw new InvalidArgumentException("Alias \"$name\" does not map to any known generator");
		}
		return Normal::class;
	}

	/**
	 * Returns the registered name of the given Generator class.
	 *
	 * @param string $class Fully qualified name of class that extends \pocketmine\world\generator\Generator
	 * @phpstan-param class-string<Generator> $class
	 *
	 * @throws InvalidArgumentException if the class type cannot be matched to a known alias
	 */
	public static function getGeneratorName(string $class) : string
	{
		Utils::testValidInstance($class, Generator::class);
		foreach (Utils::stringifyKeys(self::$list) as $name => $c) {
			if ($c === $class) {
				return $name;
			}
		}

		throw new InvalidArgumentException("Generator class $class is not registered");
	}

	private function __construct()
	{
		//NOOP
	}
}
