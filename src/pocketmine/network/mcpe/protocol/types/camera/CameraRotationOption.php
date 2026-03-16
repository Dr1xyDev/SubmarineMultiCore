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

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\NetworkBinaryStream;

final class CameraRotationOption{

	public function __construct(
		private Vector3 $value,
		private float $time,
	){}

	public function getValue() : Vector3{ return $this->value; }

	public function getTime() : float{ return $this->time; }

	public static function read(NetworkBinaryStream $in) : self{
		$value = $in->getVector3();
		$time = $in->getLFloat();

		return new self(
			$value,
			$time
		);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putVector3($this->value);
		$out->putLFloat($this->time);
	}
}
