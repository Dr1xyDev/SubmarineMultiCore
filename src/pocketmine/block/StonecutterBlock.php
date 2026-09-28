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

use pocketmine\inventory\StonecutterInventory;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;

class StonecutterBlock extends Stonecutter
{
	protected $id = self::STONECUTTER_BLOCK;

	public function onActivate(Item $item, ?Player $player = null) : bool
	{
		if ($player instanceof Player && $player->getProtocolVersion() >= ProtocolInfo::PROTOCOL_407) {
			$player->addWindow(new StonecutterInventory($this));
		}

		return true;
	}
}
