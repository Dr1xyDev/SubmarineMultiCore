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

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Minecart extends Item
{
	public function __construct(int $meta = 0)
	{
		parent::__construct(self::MINECART, $meta, "Minecart");
	}

	public function getMaxStackSize() : int
	{
		return 1;
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool
	{
		if ($blockClicked->getId() !== Block::RAIL) {
			return false;
		}

		$nbt = Entity::createBaseNBT($blockReplace->add(0.5, 0, 0.5));
		$entity = Entity::createEntity("Minecart", $player->level, $nbt);
		$entity->spawnToAll();

		$this->pop();

		return true;
	}
}
