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

use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\types\inventory\InventoryTransactionChangedSlotsHack;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemTransactionData;

use function count;

final class ItemInteractionData
{
	/**
	 * @param InventoryTransactionChangedSlotsHack[] $requestChangedSlots
	 */
	public function __construct(
		private int $requestId,
		private array $requestChangedSlots,
		private UseItemTransactionData $transactionData
	) {
	}

	public function getRequestId() : int
	{
		return $this->requestId;
	}

	/**
	 * @return InventoryTransactionChangedSlotsHack[]
	 */
	public function getRequestChangedSlots() : array
	{
		return $this->requestChangedSlots;
	}

	public function getTransactionData() : UseItemTransactionData
	{
		return $this->transactionData;
	}

	public static function read(NetworkBinaryStream $in, int $playerProtocol) : self
	{
		$requestId = $in->getVarInt();
		$requestChangedSlots = [];
		if ($requestId !== 0) {
			$len = $in->getUnsignedVarInt();
			for ($i = 0; $i < $len; ++$i) {
				$requestChangedSlots[] = InventoryTransactionChangedSlotsHack::read($in);
			}
		}
		$transactionData = new UseItemTransactionData();
		$transactionData->decode($in, $playerProtocol);
		return new ItemInteractionData($requestId, $requestChangedSlots, $transactionData);
	}

	public function write(NetworkBinaryStream $out, int $playerProtocol) : void
	{
		$out->putVarInt($this->requestId);
		if ($this->requestId !== 0) {
			$out->putUnsignedVarInt(count($this->requestChangedSlots));
			foreach ($this->requestChangedSlots as $changedSlot) {
				$changedSlot->write($out);
			}
		}
		$this->transactionData->encode($out, $playerProtocol);
	}
}
