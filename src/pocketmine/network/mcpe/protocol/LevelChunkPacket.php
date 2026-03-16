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

namespace pocketmine\network\mcpe\protocol;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\ChunkPosition;

use function count;

class LevelChunkPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::LEVEL_CHUNK_PACKET;

	private ChunkPosition $chunkPosition;
	private int $dimensionId;
	private int $subChunkCount;
	private bool $cacheEnabled;
	/** @var int[] */
	private array $usedBlobHashes = [];
	private string $extraPayload;

	//this appears large enough for a world height of 1024 blocks - it may need to be increased in the future
	private const MAX_BLOB_HASHES = 64;

	public static function withoutCache(ChunkPosition $chunkPosition, int $dimensionId, int $subChunkCount, string $payload) : self
	{
		$result = new self();
		$result->chunkPosition = $chunkPosition;
		$result->dimensionId = $dimensionId;
		$result->subChunkCount = $subChunkCount;
		$result->extraPayload = $payload;

		$result->cacheEnabled = false;

		return $result;
	}

	public static function withCache(ChunkPosition $chunkPosition, int $dimensionId, int $subChunkCount, array $usedBlobHashes, string $extraPayload) : self
	{
		(static function (int ...$hashes) {})(...$usedBlobHashes);
		$result = new self();
		$result->chunkPosition = $chunkPosition;
		$result->dimensionId = $dimensionId;
		$result->subChunkCount = $subChunkCount;
		$result->extraPayload = $extraPayload;

		$result->cacheEnabled = true;
		$result->usedBlobHashes = $usedBlobHashes;

		return $result;
	}

	public function getChunkPosition() : ChunkPosition
	{
		return $this->chunkPosition;
	}

	public function getDimensionId() : int
	{
		return $this->dimensionId;
	}

	public function getSubChunkCount() : int
	{
		return $this->subChunkCount;
	}

	public function isCacheEnabled() : bool
	{
		return $this->cacheEnabled;
	}

	/**
	 * @return int[]
	 */
	public function getUsedBlobHashes() : array
	{
		return $this->usedBlobHashes;
	}

	public function getExtraPayload() : string
	{
		return $this->extraPayload;
	}

	protected function decodePayload() : void
	{
		$this->chunkPosition = ChunkPosition::read($this);
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_361) {
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_649) {
				$this->dimensionId = $this->getVarInt();
			}
			$this->subChunkCount = $this->getUnsignedVarInt();
			$this->cacheEnabled = $this->getBool();
			if ($this->cacheEnabled) {
				$count = $this->getUnsignedVarInt();
				if ($count > self::MAX_BLOB_HASHES) {
					throw new PacketDecodeException("Expected at most " . self::MAX_BLOB_HASHES . " blob hashes, got " . $count);
				}
				for ($i = 0; $i < $count; ++$i) {
					$this->usedBlobHashes[] = $this->getLLong();
				}
			}
		}
		$this->extraPayload = $this->getString();
	}

	protected function encodePayload() : void
	{
		$this->chunkPosition->write($this);
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_361) {
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_649) {
				$this->putVarInt($this->dimensionId);
			}
			$this->putUnsignedVarInt($this->subChunkCount);
			$this->putBool($this->cacheEnabled);
			if ($this->cacheEnabled) {
				$this->putUnsignedVarInt(count($this->usedBlobHashes));
				foreach ($this->usedBlobHashes as $hash) {
					$this->putLLong($hash);
				}
			}
		}
		$this->putString($this->extraPayload);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleLevelChunk($this);
	}
}
