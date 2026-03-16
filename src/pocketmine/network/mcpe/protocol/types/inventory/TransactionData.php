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

namespace pocketmine\network\mcpe\protocol\types\inventory;

use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\utils\BinaryDataException;
use UnexpectedValueException as PacketDecodeException;

use function count;

abstract class TransactionData
{
	/** @var NetworkInventoryAction[] */
	protected array $actions = [];

	protected bool $hasItemStackIds = true;

	/**
	 * @return NetworkInventoryAction[]
	 */
	final public function getActions() : array
	{
		return $this->actions;
	}

	abstract public function getTypeId() : int;

	/**
	 * @throws BinaryDataException
	 * @throws PacketDecodeException
	 */
	final public function decode(NetworkBinaryStream $stream, int $playerProtocol) : void
	{
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_407 && $playerProtocol < ProtocolInfo::PROTOCOL_431) {
			$this->hasItemStackIds = $stream->getBool();
		}

		$actionCount = $stream->getUnsignedVarInt();
		for ($i = 0; $i < $actionCount; ++$i) {
			$this->actions[] = (new NetworkInventoryAction())->read($stream, $this->hasItemStackIds, $playerProtocol);
		}
		$this->decodeData($stream, $playerProtocol);
	}

	/**
	 * @throws BinaryDataException
	 * @throws PacketDecodeException
	 */
	abstract protected function decodeData(NetworkBinaryStream $stream, int $playerProtocol) : void;

	final public function encode(NetworkBinaryStream $stream, int $playerProtocol) : void
	{
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_407 && $playerProtocol < ProtocolInfo::PROTOCOL_431) {
			$stream->putBool($this->hasItemStackIds);
		}

		$stream->putUnsignedVarInt(count($this->actions));
		foreach ($this->actions as $action) {
			$action->write($stream, $this->hasItemStackIds, $playerProtocol);
		}
		$this->encodeData($stream, $playerProtocol);
	}

	abstract protected function encodeData(NetworkBinaryStream $stream, int $playerProtocol) : void;
}
