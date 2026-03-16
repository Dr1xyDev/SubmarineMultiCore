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

class TransferPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::TRANSFER_PACKET;

	public string $address;
	public int $port = 19132;
	public bool $reloadWorld = false;

	protected function decodePayload() : void
	{
		$this->address = $this->getString();
		$this->port = $this->getLShort();
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_729) {
			$this->reloadWorld = $this->getBool();
		}
	}

	protected function encodePayload() : void
	{
		$this->putString($this->address);
		$this->putLShort($this->port);
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_729) {
			$this->putBool($this->reloadWorld);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleTransfer($this);
	}
}
