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

use function exp;

final class Gaussian
{
	/** @var float[][] */
	public array $kernel = [];

	public function __construct(public int $smoothSize)
	{
		$bellSize = 1 / $this->smoothSize;
		$bellHeight = 2 * $this->smoothSize;

		for ($sx = -$this->smoothSize; $sx <= $this->smoothSize; ++$sx) {
			$this->kernel[$sx + $this->smoothSize] = [];

			for ($sz = -$this->smoothSize; $sz <= $this->smoothSize; ++$sz) {
				$bx = $bellSize * $sx;
				$bz = $bellSize * $sz;
				$this->kernel[$sx + $this->smoothSize][$sz + $this->smoothSize] = $bellHeight * exp(-($bx * $bx + $bz * $bz) / 2);
			}
		}
	}
}
