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

/**
 * Manages thread-local caches for generators and the things needed to support them
 */
final class ThreadLocalGeneratorContext
{
	/**
	 * @var self[]
	 * @phpstan-var array<int, self>
	 */
	private static array $contexts = [];

	public static function register(self $context, int $worldId) : void
	{
		self::$contexts[$worldId] = $context;
	}

	public static function unregister(int $worldId) : void
	{
		unset(self::$contexts[$worldId]);
	}

	public static function fetch(int $worldId) : ?self
	{
		return self::$contexts[$worldId] ?? null;
	}

	public function __construct(
		private Generator $generator
	) {
	}

	public function getGenerator() : Generator
	{
		return $this->generator;
	}
}
