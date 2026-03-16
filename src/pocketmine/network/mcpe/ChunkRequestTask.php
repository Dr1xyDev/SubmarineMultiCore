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

namespace pocketmine\network\mcpe;

use pocketmine\block\BlockFactory;
use pocketmine\level\format\Chunk;
use pocketmine\level\format\io\FastChunkSerializer;
use pocketmine\level\Level;
use pocketmine\network\mcpe\compression\NetworkCompression;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\LevelChunkPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\serializer\PacketBatch;
use pocketmine\network\mcpe\protocol\types\ChunkPosition;
use pocketmine\network\mcpe\serializer\ChunkSerializer;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\tile\Spawnable;
use pocketmine\utils\BinaryStream;

class ChunkRequestTask extends AsyncTask
{
	protected int $levelId;
	protected string $chunk;
	protected int $chunkX;
	protected int $chunkZ;
	protected int $dimensionId;
	protected string $tiles;
	protected int $compressionLevel;
	protected int $protocol;

	public function __construct(Level $level, int $dimensionId, Chunk $chunk, int $protocol)
	{
		$this->levelId = $level->getId();
		$this->compressionLevel = $level->getServer()->networkCompressionLevel;

		$this->chunkX = $chunk->getX();
		$this->chunkZ = $chunk->getZ();
		$this->dimensionId = $dimensionId;
		$this->chunk = FastChunkSerializer::serializeTerrain($chunk);

		$tiles = "";
		foreach ($chunk->getTiles() as $tile) {
			if ($tile instanceof Spawnable) {
				$tiles .= $tile->getProtocolSerializedSpawnCompound($protocol);
			}
		}
		$this->tiles = $tiles;

		$this->protocol = $protocol;
	}

	public function onRun() : void
	{
		BlockFactory::init();

		$chunk = FastChunkSerializer::deserializeTerrain($this->chunk);
		$dimensionId = $this->dimensionId;

		$protocol = $this->protocol;

		if ($protocol >= ProtocolInfo::PROTOCOL_223) {
			$legacyToRuntime = function (int $fullId) use ($protocol) : int {
				$block = BlockFactory::fromFullBlock($fullId);
				$block = $block->getBlockProtocol($protocol) ?? $block;
				return RuntimeBlockMapping::getInstance($protocol)->toRuntimeId($block->getFullId());
			};
		}

		$pk = LevelChunkPacket::withoutCache(
			new ChunkPosition($this->chunkX, $this->chunkZ),
			$dimensionId,
			ChunkSerializer::getSubChunkCount($chunk, $dimensionId, $protocol),
			ChunkSerializer::serializeFullChunk($chunk, $protocol, $legacyToRuntime ?? null, $dimensionId, (mt_rand(0, 100) === 1)) . $this->tiles
		);
		$pk->setProtocol($protocol);

		$stream = new BinaryStream();
		PacketBatch::encodePackets($stream, [$pk], $protocol);

		$this->setResult(NetworkCompression::compress($stream->getBuffer(), $protocol, $this->compressionLevel));
	}

	public function onCompletion(Server $server) : void
	{
		$level = $server->getLevel($this->levelId);
		if ($level instanceof Level) {
			if ($this->hasResult()) {
				$level->chunkRequestCallback($this->chunkX, $this->chunkZ, $this->protocol, $this->getResult());
			} else {
				$server->getLogger()->error("Chunk request (protocol: {$this->protocol}) for world #" . $this->levelId . ", x=" . $this->chunkX . ", z=" . $this->chunkZ . " doesn't have any result data");
			}
		} else {
			$server->getLogger()->debug("Dropped chunk task due to world not loaded");
		}
	}
}
