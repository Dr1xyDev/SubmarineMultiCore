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
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;

class UpdateTradePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::UPDATE_TRADE_PACKET;

	//TODO: find fields

	/** @var int */
	public $windowId;
	/** @var int */
	public $windowType = WindowTypes::TRADING; //Mojang hardcoded this -_-
	/** @var int */
	public $thisIsAlwaysZero = 0; //hardcoded to 0
	/** @var int */
	public $uvarint;
	/** @var int */
	public $tradeTier;
	/** @var int */
	public $traderEid;
	/** @var int */
	public $playerEid;
	/** @var string */
	public $displayName;
	/** @var bool */
	public $isWilling;
	/** @var bool */
	public $isV2Trading;
	/** @var string */
	public $offers;

	protected function decodePayload() : void
	{
		$this->windowId = $this->getByte();
		$this->windowType = $this->getByte();
		$this->thisIsAlwaysZero = $this->getVarInt();
		if ($this->getProtocol() < ProtocolInfo::PROTOCOL_354) {
			$this->uvarint = $this->getVarInt();
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_313) {
				$this->tradeTier = $this->getVarInt();
			}
			$this->isWilling = $this->getBool();
		} else {
			$this->tradeTier = $this->getVarInt();
		}
		$this->traderEid = $this->getEntityUniqueId();
		$this->playerEid = $this->getEntityUniqueId();
		$this->displayName = $this->getString();
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_354) {
			$this->isWilling = $this->getBool();
			$this->isV2Trading = $this->getBool();
		}
		$this->offers = $this->getRemaining();
	}

	protected function encodePayload() : void
	{
		$this->putByte($this->windowId);
		$this->putByte($this->windowType);
		$this->putVarInt($this->thisIsAlwaysZero);
		if ($this->getProtocol() < ProtocolInfo::PROTOCOL_354) {
			$this->putVarInt($this->uvarint);
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_313) {
				$this->putVarInt($this->tradeTier);
			}

			$this->putBool($this->isWilling);
		} else {
			$this->putVarInt($this->tradeTier);
		}
		$this->putEntityUniqueId($this->traderEid);
		$this->putEntityUniqueId($this->playerEid);
		$this->putString($this->displayName);
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_354) {
			$this->putBool($this->isWilling);
			$this->putBool($this->isV2Trading);
		}
		$this->put($this->offers);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleUpdateTrade($this);
	}
}
