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
use pocketmine\network\mcpe\protocol\ProtocolInfo;

use function count;

final class CameraAimAssistPreset
{
	/**
	 * @param string[]                            $exclusionList
	 * @param string[]                            $liquidTargetingList
	 * @param CameraAimAssistPresetItemSettings[] $itemSettings
	 */
	public function __construct(
		private string $identifier,
		private string $categories,
		private array $exclusionList,
		private array $liquidTargetingList,
		private array $itemSettings,
		private ?string $defaultItemSettings,
		private ?string $defaultHandSettings,
	) {
	}

	public function getIdentifier() : string
	{
		return $this->identifier;
	}

	public function getCategories() : string
	{
		return $this->categories;
	}

	/**
	 * @return string[]
	 */
	public function getExclusionList() : array
	{
		return $this->exclusionList;
	}

	/**
	 * @return string[]
	 */
	public function getLiquidTargetingList() : array
	{
		return $this->liquidTargetingList;
	}

	/**
	 * @return CameraAimAssistPresetItemSettings[]
	 */
	public function getItemSettings() : array
	{
		return $this->itemSettings;
	}

	public function getDefaultItemSettings() : ?string
	{
		return $this->defaultItemSettings;
	}

	public function getDefaultHandSettings() : ?string
	{
		return $this->defaultHandSettings;
	}

	public static function read(NetworkBinaryStream $in, int $protocolVersion) : self
	{
		$identifier = $in->getString();
		if ($protocolVersion < ProtocolInfo::PROTOCOL_776) {
			$categories = $in->getString();
		}

		$exclusionList = [];
		for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
			$exclusionList[] = $in->getString();
		}

		$liquidTargetingList = [];
		for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
			$liquidTargetingList[] = $in->getString();
		}

		$itemSettings = [];
		for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
			$itemSettings[] = CameraAimAssistPresetItemSettings::read($in);
		}

		$defaultItemSettings = $in->readOptional(fn () => $in->getString());
		$defaultHandSettings = $in->readOptional(fn () => $in->getString());

		return new self(
			$identifier,
			$categories ?? "",
			$exclusionList,
			$liquidTargetingList,
			$itemSettings,
			$defaultItemSettings,
			$defaultHandSettings
		);
	}

	public function write(NetworkBinaryStream $out, int $protocolVersion) : void
	{
		$out->putString($this->identifier);
		if ($protocolVersion < ProtocolInfo::PROTOCOL_776) {
			$out->putString($this->categories);
		}

		$out->putUnsignedVarInt(count($this->exclusionList));
		foreach ($this->exclusionList as $exclusion) {
			$out->putString($exclusion);
		}

		$out->putUnsignedVarInt(count($this->liquidTargetingList));
		foreach ($this->liquidTargetingList as $liquidTargeting) {
			$out->putString($liquidTargeting);
		}

		$out->putUnsignedVarInt(count($this->itemSettings));
		foreach ($this->itemSettings as $itemSetting) {
			$itemSetting->write($out);
		}

		$out->writeOptional($this->defaultItemSettings, fn (string $v) => $out->putString($v));
		$out->writeOptional($this->defaultHandSettings, fn (string $v) => $out->putString($v));
	}
}
