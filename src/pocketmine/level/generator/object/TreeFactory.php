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

namespace pocketmine\level\generator\object;

use pocketmine\block\utils\TreeType;
use pocketmine\utils\Random;

final class TreeFactory
{
	/**
	 * @param TreeType|null $type default oak
	 */
	public static function get(Random $random, ?TreeType $type = null) : ?Tree
	{
		$type = $type ?? TreeType::OAK();
		if ($type->equals(TreeType::SPRUCE())) {
			return new SpruceTree();
		} elseif ($type->equals(TreeType::BIRCH())) {
			if ($random->nextBoundedInt(39) === 0) {
				return new BirchTree(true);
			} else {
				return new BirchTree();
			}
		} elseif ($type->equals(TreeType::JUNGLE())) {
			return new JungleTree();
		} elseif ($type->equals(TreeType::OAK())) { //default
			return new OakTree();
			/*if($random->nextRange(0, 9) === 0){
				$tree = new BigTree();
			}else{*/

			//}
		} elseif ($type->equals(TreeType::CHERRY())) {
			return new CherryTree();
		} elseif ($type->equals(TreeType::PALE_OAK())) {
			//TODO: Generation PALE_OAK
		}
		return null;
	}
}
