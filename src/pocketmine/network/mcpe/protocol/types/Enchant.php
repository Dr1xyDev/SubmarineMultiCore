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

namespace pocketmine\network\mcpe\protocol\types;

use pocketmine\network\mcpe\NetworkBinaryStream;

final class Enchant
{
	public function __construct(
		private int $id,
		private int $level
	) {
	}

	public function getId() : int
	{
		return $this->id;
	}

	public function getLevel() : int
	{
		return $this->level;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$id = $in->getByte();
		$level = $in->getByte();
		return new self($id, $level);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putByte($this->id);
		$out->putByte($this->level);
	}
}
