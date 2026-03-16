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

use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;

final class PlayerMovementSettings
{
	public function __construct(
		private ServerAuthMovementMode $movementType,
		private int $rewindHistorySize,
		private bool $serverAuthoritativeBlockBreaking
	) {
	}

	public function getMovementType() : ServerAuthMovementMode
	{
		return $this->movementType;
	}

	public function getRewindHistorySize() : int
	{
		return $this->rewindHistorySize;
	}

	public function isServerAuthoritativeBlockBreaking() : bool
	{
		return $this->serverAuthoritativeBlockBreaking;
	}

	public static function read(DataPacket $in) : self
	{
		if ($in->getProtocol() < ProtocolInfo::PROTOCOL_818) {
			$movementType = ServerAuthMovementMode::fromPacket($in->getVarInt());
		}
		if ($in->getProtocol() >= ProtocolInfo::PROTOCOL_428) {
			$rewindHistorySize = $in->getVarInt();
			$serverAuthBlockBreaking = $in->getBool();
		}
		return new self($movementType ?? ServerAuthMovementMode::SERVER_AUTHORITATIVE_V2->value, $rewindHistorySize ?? 0, $serverAuthBlockBreaking ?? false);
	}

	public function write(DataPacket $out) : void
	{
		if ($out->getProtocol() < ProtocolInfo::PROTOCOL_818) {
			$out->putVarInt($this->movementType->value);
		}
		if ($out->getProtocol() >= ProtocolInfo::PROTOCOL_428) {
			$out->putVarInt($this->rewindHistorySize);
			$out->putBool($this->serverAuthoritativeBlockBreaking);
		}
	}
}
