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

class StrippedCherryLog extends StrippedLog
{

	public function getBlockProtocol(int $playerProtocol) : ?Block{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_582) {
			return BlockFactory::get(BlockIds::LOG, match ($this->meta) {
				0x01 => 0x04,
				0x02 => 0x08,
				default => 0,
			});
		}

		return null;
	}
}
