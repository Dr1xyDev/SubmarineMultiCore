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

namespace pocketmine\network\mcpe\protocol\types\camera;

use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstructionColor as Color;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstructionTime as Time;

final class CameraFadeInstruction
{
	public function __construct(
		private ?Time $time,
		private ?Color $color,
	) {
	}

	public function getTime() : ?Time
	{
		return $this->time;
	}

	public function getColor() : ?Color
	{
		return $this->color;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$time = $in->readOptional(fn () => Time::read($in));
		$color = $in->readOptional(fn () => Color::read($in));
		return new self(
			$time,
			$color
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->writeOptional($this->time, fn (Time $v) => $v->write($out));
		$out->writeOptional($this->color, fn (Color $v) => $v->write($out));
	}
}
