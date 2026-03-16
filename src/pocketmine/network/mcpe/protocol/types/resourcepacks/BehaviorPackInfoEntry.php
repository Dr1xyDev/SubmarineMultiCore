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

class BehaviorPackInfoEntry
{
	public function __construct(
		private string $packId,
		private string $version,
		private int $sizeBytes,
		private string $encryptionKey = "",
		private string $subPackName = "",
		private string $contentId = "",
		private bool $hasScripts = false,
		private bool $isAddonPack = false
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

	public function getSizeBytes() : int
	{
		return $this->sizeBytes;
	}

	public function getEncryptionKey() : string
	{
		return $this->encryptionKey;
	}

	public function getSubPackName() : string
	{
		return $this->subPackName;
	}

	public function getContentId() : string
	{
		return $this->contentId;
	}

	public function hasScripts() : bool
	{
		return $this->hasScripts;
	}

	public function isAddonPack() : bool
	{
		return $this->isAddonPack;
	}

	public function write(int $playerProtocol, NetworkBinaryStream $out) : void
	{
		$out->putString($this->packId);
		$out->putString($this->version);
		$out->putLLong($this->sizeBytes);
		$out->putString($this->encryptionKey);
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_137) {
			$out->putString($this->subPackName);
			if ($playerProtocol >= ProtocolInfo::PROTOCOL_282) {
				$out->putString($this->contentId);
				if ($playerProtocol >= ProtocolInfo::PROTOCOL_332) {
					$out->putBool($this->hasScripts);
					if ($playerProtocol >= ProtocolInfo::PROTOCOL_712) {
						$out->putBool($this->isAddonPack);
					}
				}
			}
		}
	}

	public static function read(int $playerProtocol, NetworkBinaryStream $in) : self
	{
		$uuid = $in->getString();
		$version = $in->getString();
		$sizeBytes = $in->getLLong();
		$encryptionKey = $in->getString();
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_137) {
			$subPackName = $in->getString();
			if ($playerProtocol >= ProtocolInfo::PROTOCOL_282) {
				$contentId = $in->getString();
				if ($playerProtocol >= ProtocolInfo::PROTOCOL_332) {
					$hasScripts = $in->getBool();
					if ($playerProtocol >= ProtocolInfo::PROTOCOL_712) {
						$isAddonPack = $in->getBool();
					}
				}
			}
		}

		return new self(
			$uuid,
			$version,
			$sizeBytes,
			$encryptionKey,
			$subPackName ?? "",
			$contentId ?? $uuid,
			$hasScripts ?? false,
			$isAddonPack ?? false
		);
	}
}
