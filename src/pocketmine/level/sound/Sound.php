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

namespace pocketmine\level\sound;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;

abstract class Sound extends Vector3
{
	protected int $protocol = ProtocolInfo::CURRENT_PROTOCOL;

	/**
	 * @return DataPacket|DataPacket[]
	 */
	abstract public function encode();

	public function setProtocol(int $protocol) : void
	{
		$this->protocol = $protocol;
	}

	/*
	* It is implemented so that packages are not simply assembled (saving memory)
	*/
	public function isUseProtocol() : bool
	{
		return false;
	}
}
