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

namespace pocketmine\network\mcpe\protocol\types\resourcepacks;

use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\ProtocolInfo;

class ResourcePackStackEntry
{
	public function __construct(
		private string $packId,
		private string $version,
		private string $subPackName
	) {
	}

	public function getPackId() : string
	{
		return $this->packId;
	}

	public function getVersion() : string
	{
		return $this->version;
	}

	public function getSubPackName() : string
	{
		return $this->subPackName;
	}

	public function write(int $playerProtocol, NetworkBinaryStream $out) : void
	{
		$out->putString($this->packId);
		$out->putString($this->version);
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_137) {
			$out->putString($this->subPackName);
		}
	}

	public static function read(int $playerProtocol, NetworkBinaryStream $in) : self
	{
		$packId = $in->getString();
		$version = $in->getString();
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_137) {
			$subPackName = $in->getString();
		}
		return new self($packId, $version, $subPackName ?? "");
	}
}
