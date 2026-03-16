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

namespace pocketmine\event\player;

use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Called when a player middle-clicks on an entity to get an item in creative mode.
 */
class PlayerEntityPickEvent extends PlayerEvent implements Cancellable
{
	public function __construct(
		Player $player,
		private Entity $entityClicked,
		private Item $resultItem
	) {
		$this->player = $player;
	}

	public function getEntity() : Entity
	{
		return $this->entityClicked;
	}

	public function getResultItem() : Item
	{
		return $this->resultItem;
	}
}
