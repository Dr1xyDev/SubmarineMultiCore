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

use pocketmine\block\Block;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\NetworkSession;

class UpdateBlockPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::UPDATE_BLOCK_PACKET;

	public const FLAG_NONE = 0b0000;
	public const FLAG_NEIGHBORS = 0b0001;
	public const FLAG_NETWORK = 0b0010;
	public const FLAG_NOGRAPHIC = 0b0100;
	public const FLAG_PRIORITY = 0b1000;

	public const FLAG_ALL = self::FLAG_NEIGHBORS | self::FLAG_NETWORK;
	public const FLAG_ALL_PRIORITY = self::FLAG_ALL | self::FLAG_PRIORITY;

	public const DATA_LAYER_NORMAL = 0;
	public const DATA_LAYER_LIQUID = 1;

	public int $x;
	public int $z;
	public int $y;
	public int $blockId;
	public int $blockMeta;
	public int $flags;
	public int $dataLayerId = self::DATA_LAYER_NORMAL;

	protected function decodePayload() : void
	{
		$this->getBlockPosition($this->x, $this->y, $this->z);
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_223) {
			$fullState = RuntimeBlockMapping::getInstance($this->getProtocol())->fromRuntimeId($this->getUnsignedVarInt());
			$this->blockId = $fullState >> Block::INTERNAL_METADATA_BITS;
			$this->blockMeta = $fullState & Block::INTERNAL_METADATA_MASK;
			$this->flags = $this->getUnsignedVarInt();
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_261) {
				$this->dataLayerId = $this->getUnsignedVarInt();
			}
		} else {
			$this->blockId = $this->getUnsignedVarInt();
			$aux = $this->getUnsignedVarInt();
			$this->blockMeta = $aux & 0x0f;
			$this->flags = $aux >> 4;
		}
	}

	protected function encodePayload() : void
	{
		$this->putBlockPosition($this->x, $this->y, $this->z);
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_223) {
			$runtimeId = RuntimeBlockMapping::getInstance($this->getProtocol())->toRuntimeId(($this->blockId << Block::INTERNAL_METADATA_BITS) | $this->blockMeta);
			$this->putUnsignedVarInt($runtimeId);
			$this->putUnsignedVarInt($this->flags);
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_261) {
				$this->putUnsignedVarInt($this->dataLayerId);
			}
		} else {
			$this->putUnsignedVarInt($this->blockId);
			$this->putUnsignedVarInt(($this->flags << 4) | $this->blockMeta);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleUpdateBlock($this);
	}
}
