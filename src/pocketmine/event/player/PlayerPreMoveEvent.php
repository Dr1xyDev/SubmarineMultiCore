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

use pocketmine\event\Cancellable;
use pocketmine\math\Vector3;
use pocketmine\Player;

class PlayerPreMoveEvent extends PlayerEvent implements Cancellable
{
	public static $handlerList = null;

	/** @var Vector3 */
	private $from;
	/** @var Vector3 */
	private $to;

	public function __construct(Player $player, Vector3 $from, Vector3 $to)
	{
		$this->player = $player;
		$this->from = $from;
		$this->to = $to;
	}

	public function getFrom() : Vector3
	{
		return $this->from;
	}

	public function getTo() : Vector3
	{
		return $this->to;
	}

	public function setTo(Vector3 $to)
	{
		$this->to = $to;
	}
}
