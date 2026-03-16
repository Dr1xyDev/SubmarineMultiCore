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

namespace raklib\protocol;

use pocketmine\utils\BinaryDataException;

abstract class Packet
{
	/** @var int */
	public static $ID = -1;

	public function encode(PacketSerializer $out) : void
	{
		$this->encodeHeader($out);
		$this->encodePayload($out);
	}

	protected function encodeHeader(PacketSerializer $out) : void
	{
		$out->putByte(static::$ID);
	}

	abstract protected function encodePayload(PacketSerializer $out) : void;

	/**
	 * @throws BinaryDataException
	 */
	public function decode(PacketSerializer $in) : void
	{
		$this->decodeHeader($in);
		$this->decodePayload($in);
	}

	/**
	 * @throws BinaryDataException
	 */
	protected function decodeHeader(PacketSerializer $in) : void
	{
		$in->getByte(); //PID
	}

	/**
	 * @throws BinaryDataException
	 */
	abstract protected function decodePayload(PacketSerializer $in) : void;
}
