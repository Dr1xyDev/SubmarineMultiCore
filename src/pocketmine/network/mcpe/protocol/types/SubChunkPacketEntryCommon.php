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
use pocketmine\network\mcpe\protocol\PacketDecodeException;
use pocketmine\network\mcpe\protocol\ProtocolInfo;

final class SubChunkPacketEntryCommon
{
	public function __construct(
		private SubChunkPositionOffset $offset,
		private int $requestResult,
		private string $terrainData,
		private ?SubChunkPacketHeightMapInfo $heightMap,
		private ?SubChunkPacketHeightMapInfo $renderHeightMap
	) {
	}

	public function getOffset() : SubChunkPositionOffset
	{
		return $this->offset;
	}

	public function getRequestResult() : int
	{
		return $this->requestResult;
	}

	public function getTerrainData() : string
	{
		return $this->terrainData;
	}

	public function getHeightMap() : ?SubChunkPacketHeightMapInfo
	{
		return $this->heightMap;
	}

	public function getRenderHeightMap() : ?SubChunkPacketHeightMapInfo
	{
		return $this->renderHeightMap;
	}

	public static function read(NetworkBinaryStream $in, bool $cacheEnabled, int $protocolVersion) : self
	{
		$offset = SubChunkPositionOffset::read($in);

		$requestResult = $in->getByte();

		$data = !$cacheEnabled || $requestResult !== SubChunkRequestResult::SUCCESS_ALL_AIR ? $in->getString() : "";

		$heightMapDataType = $in->getByte();
		$heightMapData = match ($heightMapDataType) {
			SubChunkPacketHeightMapType::NO_DATA => null,
			SubChunkPacketHeightMapType::DATA => SubChunkPacketHeightMapInfo::read($in),
			SubChunkPacketHeightMapType::ALL_TOO_HIGH => SubChunkPacketHeightMapInfo::allTooHigh(),
			SubChunkPacketHeightMapType::ALL_TOO_LOW => SubChunkPacketHeightMapInfo::allTooLow(),
			default => throw new PacketDecodeException("Unknown heightmap data type $heightMapDataType")
		};

		if ($protocolVersion >= ProtocolInfo::PROTOCOL_818) {
			$renderHeightMapDataType = $in->getByte();
			$renderHeightMapData = match ($renderHeightMapDataType) {
				SubChunkPacketHeightMapType::NO_DATA => null,
				SubChunkPacketHeightMapType::DATA => SubChunkPacketHeightMapInfo::read($in),
				SubChunkPacketHeightMapType::ALL_TOO_HIGH => SubChunkPacketHeightMapInfo::allTooHigh(),
				SubChunkPacketHeightMapType::ALL_TOO_LOW => SubChunkPacketHeightMapInfo::allTooLow(),
				SubChunkPacketHeightMapType::ALL_COPIED => $heightMapData,
				default => throw new PacketDecodeException("Unknown render heightmap data type $renderHeightMapDataType")
			};
		}

		return new self(
			$offset,
			$requestResult,
			$data,
			$heightMapData,
			$renderHeightMapData ?? SubChunkPacketHeightMapType::NO_DATA
		);
	}

	public function write(NetworkBinaryStream $out, bool $cacheEnabled, int $protocolVersion) : void
	{
		$this->offset->write($out);

		$out->putByte($this->requestResult);

		if (!$cacheEnabled || $this->requestResult !== SubChunkRequestResult::SUCCESS_ALL_AIR) {
			$out->putString($this->terrainData);
		}

		if ($this->heightMap === null) {
			$out->putByte(SubChunkPacketHeightMapType::NO_DATA);
		} elseif ($this->heightMap->isAllTooLow()) {
			$out->putByte(SubChunkPacketHeightMapType::ALL_TOO_LOW);
		} elseif ($this->heightMap->isAllTooHigh()) {
			$out->putByte(SubChunkPacketHeightMapType::ALL_TOO_HIGH);
		} else {
			$heightMapData = $this->heightMap; //avoid PHPStan purity issue
			$out->putByte(SubChunkPacketHeightMapType::DATA);
			$heightMapData->write($out);
		}

		if ($protocolVersion >= ProtocolInfo::PROTOCOL_818) {
			if ($this->renderHeightMap === null) {
				$out->putByte(SubChunkPacketHeightMapType::ALL_COPIED);
			} elseif ($this->renderHeightMap->isAllTooLow()) {
				$out->putByte(SubChunkPacketHeightMapType::ALL_TOO_LOW);
			} elseif ($this->renderHeightMap->isAllTooHigh()) {
				$out->putByte(SubChunkPacketHeightMapType::ALL_TOO_HIGH);
			} else {
				$renderHeightMapData = $this->renderHeightMap; //avoid PHPStan purity issue
				$out->putByte(SubChunkPacketHeightMapType::DATA);
				$renderHeightMapData->write($out);
			}
		}
	}
}
