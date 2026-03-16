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

namespace pocketmine\network\mcpe\cache;

use pocketmine\maps\MapData;
use pocketmine\network\mcpe\compression\NetworkCompression;
use pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\serializer\PacketBatch;
use pocketmine\utils\BinaryStream;

class MapCache
{
	/** @var self[] */
	private static array $instances = [];

	public static function getInstance(int $protocolVersion) : MapCache
	{
		return self::$instances[$protocolVersion] ?? (self::$instances[$protocolVersion] = new MapCache($protocolVersion));
	}

	/** @var string[] */
	private array $caches = [];

	public function __construct(
		private int $protocolVersion
	) {
	}

	public function getCache(MapData $data) : string
	{
		$id = $data->getId();
		if (isset($this->caches[$id])) {
			return $this->caches[$id];
		}

		// this is for first appearance
		$pk = new ClientboundMapItemDataPacket();
		$pk->originX = $pk->originY = $pk->originZ = 0;
		$pk->height = $pk->width = 128;
		$pk->dimensionId = $data->getDimension();
		$pk->scale = $data->getScale();
		$pk->colors = $data->getColors();
		$pk->mapId = $id;
		$pk->decorations = $data->getDecorations();
		$pk->trackedEntities = $data->getTrackedObjects();
		if ($this->getProtocolVersion() >= ProtocolInfo::PROTOCOL_137) {
			$pk->eids[] = $data->getId();
		}

		$stream = new BinaryStream();
		PacketBatch::encodePackets($stream, [$pk], $this->protocolVersion);

		return $this->caches[$id] = NetworkCompression::compress($stream->getBuffer(), $this->protocolVersion);
	}

	public function getProtocolVersion() : int
	{
		return $this->protocolVersion;
	}
}
