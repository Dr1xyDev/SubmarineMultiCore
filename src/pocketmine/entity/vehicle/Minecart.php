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

namespace pocketmine\entity\vehicle;

use pocketmine\entity\Vehicle;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;

class Minecart extends Vehicle
{
	public const NETWORK_ID = self::MINECART;

	public float $height = 0.7;
	public float $width = 0.98;

	protected $gravity = 0.5;
	protected $drag = 0.1;

	protected function initEntity() : void
	{
		$this->setHealth(6);

		parent::initEntity();
	}

	public function getRiderSeatPosition(int $seatNumber = 0) : Vector3
	{
		return new Vector3($seatNumber * 0.8, 0, 0);
	}

	public function getDrops() : array
	{
		return [
			ItemFactory::get(Item::MINECART)
		];
	}
}
