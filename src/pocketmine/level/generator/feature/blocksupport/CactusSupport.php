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

namespace pocketmine\level\generator\feature\blocksupport;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\block\Sand;
use pocketmine\level\ChunkManager;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;

class CactusSupport implements BlockSupport {

	public function isValidPosition(ChunkManager $level, Vector3 $origin) : bool {
		$down = $level->getBlockAt($origin->x, $origin->y - 1, $origin->z);
		if (!$down->isSameType(BlockFactory::get(BlockIds::CACTUS)) && !$down instanceof Sand) {
			return false;
		}
		foreach (Facing::HORIZONTAL as $side) {
			$posSide = $origin->getSide($side);
			if ($level->getBlockAt($posSide->x, $posSide->y, $posSide->z)->isSolid()) {
				return false;
			}
		}

		return true;
	}
}
