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

use pocketmine\network\mcpe\protocol\ProtocolInfo;

class SandstoneWall extends Wall
{
	protected $id = self::SANDSTONE_WALL;

	public function getName() : string
	{
		return "Sandstone Wall";
	}

	public function getBlockProtocol(int $playerProtocol) : ?Block
	{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_332) {
			return BlockFactory::get(BlockIds::COBBLESTONE_WALL);
		} elseif ($playerProtocol < ProtocolInfo::PROTOCOL_419) {
			return BlockFactory::get(BlockIds::COBBLESTONE_WALL, 5);
		}

		return null;
	}
}
