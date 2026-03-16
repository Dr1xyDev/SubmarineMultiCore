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

/**
 * Inventory related events
 */

namespace pocketmine\event\inventory;

use pocketmine\entity\Human;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;

abstract class InventoryEvent extends Event
{
	/** @var Inventory */
	protected $inventory;

	public function __construct(Inventory $inventory)
	{
		$this->inventory = $inventory;
	}

	public function getInventory() : Inventory
	{
		return $this->inventory;
	}

	/**
	 * @return Human[]
	 */
	public function getViewers() : array
	{
		return $this->inventory->getViewers();
	}
}
