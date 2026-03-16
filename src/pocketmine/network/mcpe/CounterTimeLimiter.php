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

namespace pocketmine\network\mcpe;

use pocketmine\network\PacketHandlingException;
use function time;

final class CounterTimeLimiter {

	private int $lastTime = 0;
	private int $score = 0;

	public function __construct(
		private string $name,
		private int $limit
	){}

	/**
	 * @throws PacketHandlingException if the rate limit has been exceeded
	 */
	public function update() : void {
		$time = time();
		if ($time !== $this->lastTime) {
			$this->score = 0;
		}

		if (++$this->score > $this->limit) {
			throw new PacketHandlingException("Exceeded rate limit for \"$this->name\"");
		}
		$this->lastTime = $time;
	}
}
