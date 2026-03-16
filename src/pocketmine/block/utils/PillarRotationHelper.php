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

namespace pocketmine\block\utils;

use pocketmine\math\Facing;

class PillarRotationHelper
{
	/**
	 * @param int $face false - the old rotation system trees
	 */
	public static function getMetaFromFace(int $meta, int $face, bool $axis = false) : int
	{
		if ($axis) {
			$faces = [
				Facing::DOWN => 0,
				Facing::NORTH => 0x02,
				Facing::WEST => 0x01
			];
		} else {
			$faces = [
				Facing::DOWN => 0,
				Facing::NORTH => 0x08,
				Facing::WEST => 0x04
			];
		}

		return ($meta & 0x03) | $faces[$face & ~0x01];
	}
}
