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

use pocketmine\network\mcpe\convert\ConstantTranslator;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\resourcepacks\ResourcePackType;

class ResourcePackDataInfoPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::RESOURCE_PACK_DATA_INFO_PACKET;

	public string $packId;
	public int $maxChunkSize;
	public int $chunkCount;
	public int $compressedPackSize;
	public string $sha256;
	public bool $isPremium = false;
	public int $packType = ResourcePackType::RESOURCES; //TODO: check the values for this

	/**
	 * @generate-create-func
	 */
	public static function create(
		string $packId,
		int $maxChunkSize,
		int $chunkCount,
		int $compressedPackSize,
		string $sha256,
		bool $isPremium,
		int $packType,
	) : self {
		$result = new self();
		$result->packId = $packId;
		$result->maxChunkSize = $maxChunkSize;
		$result->chunkCount = $chunkCount;
		$result->compressedPackSize = $compressedPackSize;
		$result->sha256 = $sha256;
		$result->isPremium = $isPremium;
		$result->packType = $packType;
		return $result;
	}

	protected function decodePayload() : void
	{
		$this->packId = $this->getString();
		$this->maxChunkSize = $this->getLInt();
		$this->chunkCount = $this->getLInt();
		$this->compressedPackSize = $this->getLLong();
		$this->sha256 = $this->getString();
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_361) {
			$this->isPremium = $this->getBool();
			$this->packType = ConstantTranslator::getInstance()->fromNetworkId(ResourcePackType::class, $this->getByte(), $this->getProtocol());
		}
	}

	protected function encodePayload() : void
	{
		$this->putString($this->packId);
		$this->putLInt($this->maxChunkSize);
		$this->putLInt($this->chunkCount);
		$this->putLLong($this->compressedPackSize);
		$this->putString($this->sha256);
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_361) {
			$this->putBool($this->isPremium);
			$this->putByte(ConstantTranslator::getInstance()->toNetworkId(ResourcePackType::class, $this->packType, $this->getProtocol(), ResourcePackType::INVALID));
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleResourcePackDataInfo($this);
	}
}
