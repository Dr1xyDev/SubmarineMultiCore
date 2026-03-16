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
use pocketmine\network\mcpe\protocol\types\command\CommandOriginData;

class CommandRequestPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::COMMAND_REQUEST_PACKET;

	/** @var string */
	public $command;
	/** @var int */
	public $playerUniqueId;
	/** @var CommandOriginData */
	public $originData;
	/** @var bool */
	public $isInternal;
	/** @var int */
	public $version;

	protected function decodePayload() : void
	{
		$this->command = $this->getString();
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_141) {
			$this->originData = $this->getCommandOriginData();
			$this->isInternal = $this->getBool();
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_567) {
				$this->version = $this->getVarInt();
			}
		} else {
			$this->playerUniqueId = $this->getEntityUniqueId();
		}
	}

	protected function encodePayload() : void
	{
		$this->putString($this->command);
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_141) {
			$this->putCommandOriginData($this->originData);
			$this->putBool($this->isInternal);
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_567) {
				$this->putVarInt($this->version);
			}
		} else {
			$this->putEntityUniqueId($this->playerUniqueId);
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleCommandRequest($this);
	}
}
