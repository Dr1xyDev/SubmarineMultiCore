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

namespace pocketmine\network\mcpe\protocol\types\inventory\stackrequest;

use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

/**
 * Swaps two stacks. These don't have to be in the same inventory. This action does not modify the stacks themselves.
 */
final class SwapStackRequestAction extends ItemStackRequestAction
{
	use GetTypeIdFromConstTrait;

	public const ID = ItemStackRequestActionType::SWAP;

	public function __construct(
		private ItemStackRequestSlotInfo $slot1,
		private ItemStackRequestSlotInfo $slot2
	) {
	}

	public function getSlot1() : ItemStackRequestSlotInfo
	{
		return $this->slot1;
	}

	public function getSlot2() : ItemStackRequestSlotInfo
	{
		return $this->slot2;
	}

	public static function read(NetworkBinaryStream $in, int $playerProtocol) : self
	{
		$slot1 = ItemStackRequestSlotInfo::read($in, $playerProtocol);
		$slot2 = ItemStackRequestSlotInfo::read($in, $playerProtocol);
		return new self($slot1, $slot2);
	}

	public function write(NetworkBinaryStream $out, int $playerProtocol) : void
	{
		$this->slot1->write($out, $playerProtocol);
		$this->slot2->write($out, $playerProtocol);
	}
}
