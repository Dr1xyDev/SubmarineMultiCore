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

namespace pocketmine\block;

use pocketmine\entity\Entity;
use pocketmine\entity\object\FallingBlock;
use pocketmine\math\Facing;

abstract class Fallable extends Solid
{
	public function onNearbyBlockChange() : void
	{
		$down = $this->getSide(Facing::DOWN);
		if ($down->getId() === self::AIR || $down instanceof Liquid || $down instanceof Fire) {
			$this->level->setBlock($this, BlockFactory::get(Block::AIR), true);

			$nbt = Entity::createBaseNBT($this->add(0.5, 0, 0.5));
			$nbt->setInt("TileID", $this->getId());
			$nbt->setByte("Data", $this->getDamage());

			$fall = Entity::createEntity("FallingSand", $this->getLevel(), $nbt);

			if ($fall instanceof FallingBlock) {
				$fall->spawnToAll();

				$this->onStartFalling($fall);
			}
		}
	}

	public function tickFalling() : ?Block
	{
		return null;
	}

	public function onStartFalling(FallingBlock $fallingBlock) : void
	{
		// NOOP
	}
}
