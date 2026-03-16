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

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

class UseItemTransactionData extends TransactionData
{
	use GetTypeIdFromConstTrait;

	public const ID = InventoryTransactionPacket::TYPE_USE_ITEM;

	public const ACTION_CLICK_BLOCK = 0;
	public const ACTION_CLICK_AIR = 1;
	public const ACTION_BREAK_BLOCK = 2;
	public const ACTION_USE_AS_ATTACK = 3;

	private int $actionType;
	private TriggerType $triggerType;
	private Vector3 $blockPos;
	private int $face;
	private int $hotbarSlot;
	private ItemStackWrapper $itemInHand;
	private Vector3 $playerPos;
	private Vector3 $clickPos;
	private int $blockRuntimeId;
	private PredictedResult $clientInteractPrediction;

	public function getActionType() : int
	{
		return $this->actionType;
	}

	public function getTriggerType() : TriggerType
	{
		return $this->triggerType;
	}

	public function getBlockPos() : Vector3
	{
		return $this->blockPos;
	}

	public function getFace() : int
	{
		return $this->face;
	}

	public function getHotbarSlot() : int
	{
		return $this->hotbarSlot;
	}

	public function getItemInHand() : ItemStackWrapper
	{
		return $this->itemInHand;
	}

	public function getPlayerPos() : Vector3
	{
		return $this->playerPos;
	}

	public function getClickPos() : Vector3
	{
		return $this->clickPos;
	}

	public function getBlockRuntimeId() : int
	{
		return $this->blockRuntimeId;
	}

	public function getClientInteractPrediction() : PredictedResult
	{
		return $this->clientInteractPrediction;
	}

	protected function decodeData(NetworkBinaryStream $stream, int $playerProtocol) : void
	{
		$this->actionType = $stream->getUnsignedVarInt();
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_712) {
			$this->triggerType = TriggerType::fromPacket($stream->getUnsignedVarInt());
		}
		$x = $y = $z = 0;
		$stream->getBlockPosition($x, $y, $z);
		$this->blockPos = new Vector3($x, $y, $z);
		$this->face = $stream->getVarInt();
		$this->hotbarSlot = $stream->getVarInt();
		$this->itemInHand = $stream->getItemStackWrapper($playerProtocol);
		$this->playerPos = $stream->getVector3();
		$this->clickPos = $stream->getVector3();
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_340) {
			$this->blockRuntimeId = $stream->getUnsignedVarInt();
			if ($playerProtocol >= ProtocolInfo::PROTOCOL_712) {
				$this->clientInteractPrediction = PredictedResult::fromPacket($stream->getUnsignedVarInt());
			}
		}
	}

	protected function encodeData(NetworkBinaryStream $stream, int $playerProtocol) : void
	{
		$stream->putUnsignedVarInt($this->actionType);
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_712) {
			$stream->putUnsignedVarInt($this->triggerType->value);
		}
		$stream->putBlockPosition($this->blockPos->x, $this->blockPos->y, $this->blockPos->z);
		$stream->putVarInt($this->face);
		$stream->putVarInt($this->hotbarSlot);
		$stream->putItemStackWrapper($this->itemInHand, $playerProtocol);
		$stream->putVector3($this->playerPos);
		$stream->putVector3($this->clickPos);
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_340) {
			$stream->putUnsignedVarInt($this->blockRuntimeId);
			if ($playerProtocol >= ProtocolInfo::PROTOCOL_712) {
				$stream->putUnsignedVarInt($this->clientInteractPrediction->value);
			}
		}
	}

	/**
	 * @param NetworkInventoryAction[] $actions
	 */
	public static function new(array $actions, int $actionType, TriggerType $triggerType, Vector3 $blockPos, int $face, int $hotbarSlot, ItemStackWrapper $itemInHand, Vector3 $playerPos, Vector3 $clickPos, int $blockRuntimeId, PredictedResult $clientPrediction) : self
	{
		$result = new self();
		$result->actions = $actions;
		$result->actionType = $actionType;
		$result->triggerType = $triggerType;
		$result->blockPos = $blockPos;
		$result->face = $face;
		$result->hotbarSlot = $hotbarSlot;
		$result->itemInHand = $itemInHand;
		$result->playerPos = $playerPos;
		$result->clickPos = $clickPos;
		$result->blockRuntimeId = $blockRuntimeId;
		$result->clientInteractPrediction = $clientPrediction;
		return $result;
	}
}
