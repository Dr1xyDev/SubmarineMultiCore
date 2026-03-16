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

class Log2 extends Log
{
	public const ACACIA = 0;
	public const DARK_OAK = 1;

	protected $id = self::LOG2;

	public function getName() : string
	{
		static $names = [
			0 => "Acacia Wood",
			1 => "Dark Oak Wood"
		];
		return $names[$this->getVariant()] ?? "Unknown";
	}

	public function getBlockProtocol(int $playerProtocol) : ?Block
	{
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_340 && $this->meta >= 12) {
			return BlockFactory::get(BlockIds::WOOD, 4 + $this->getVariant());
		}

		return null;
	}
}
