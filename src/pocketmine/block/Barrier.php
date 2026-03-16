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

class Barrier extends Transparent
{
	protected $id = self::BARRIER;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Barrier";
	}

	public function getHardness() : float
	{
		return -1.0;
	}

	public function getBlastResistance() : float
	{
		return 18000000.0;
	}

	public function getBlockProtocol(int $playerProtocol) : ?Block{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_282) {
			return BlockFactory::get(BlockIds::INVISIBLEBEDROCK);
		}

		return null;
	}
}
