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

final class SubChunkPacketEntryWithCache
{
	public function __construct(
		private SubChunkPacketEntryCommon $base,
		private int $usedBlobHash
	) {
	}

	public function getBase() : SubChunkPacketEntryCommon
	{
		return $this->base;
	}

	public function getUsedBlobHash() : int
	{
		return $this->usedBlobHash;
	}

	public static function read(NetworkBinaryStream $in, int $protocolVersion) : self
	{
		$base = SubChunkPacketEntryCommon::read($in, true, $protocolVersion);
		$usedBlobHash = $in->getLLong();

		return new self($base, $usedBlobHash);
	}

	public function write(NetworkBinaryStream $out, int $protocolVersion) : void
	{
		$this->base->write($out, true, $protocolVersion);
		$out->putLLong($this->usedBlobHash);
	}
}
