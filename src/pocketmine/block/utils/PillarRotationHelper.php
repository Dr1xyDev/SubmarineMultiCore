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

use pocketmine\block\Block;
use pocketmine\block\BoneBlock;
use pocketmine\block\CreakingHeart;
use pocketmine\block\Deepslate;
use pocketmine\block\FixLog;
use pocketmine\block\FixWood;
use pocketmine\block\HayBale;
use pocketmine\block\Log;
use pocketmine\block\Quartz;
use pocketmine\block\StrippedLog;
use pocketmine\block\StrippedWood;
use pocketmine\math\Facing;

class PillarRotationHelper
{
	/**
	 * @param int $face false - the old rotation system trees
	 */
	public static function getMetaFromFace(int $meta, int $face, bool $fix = false) : int{
		if ($fix) {
			$faces = [
				Facing::DOWN => 0, //y
				Facing::NORTH => 2, //z
				Facing::WEST => 1 //x
			];
		} else {
			$faces = [
				Facing::DOWN => 0, //y
				Facing::NORTH => 0x08, //z
				Facing::WEST => 0x04 //x
			];
		}

		return ($meta & 0x03) | $faces[$face & ~0x01];
	}

	public static function getRotations(Block $block) : ?PillarRotations {
		if (
			$block instanceof FixLog ||
			$block instanceof StrippedLog ||
			$block instanceof FixWood ||
			$block instanceof StrippedWood ||
			$block instanceof CreakingHeart ||
			$block instanceof Deepslate
		) {
			return new PillarRotations(1, 0, 2);
		} elseif (
			$block instanceof Log ||
			$block instanceof Quartz ||
			$block instanceof HayBale ||
			$block instanceof BoneBlock
		) {
			return new PillarRotations(0x04, 0, 0x08);
		}

		return null;
	}
}
