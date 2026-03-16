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

final class CreativeItemEntry
{
	public function __construct(
		private int $entryId,
		private ItemStack $item,
		private readonly int $groupId
	) {
	}

	public function getEntryId() : int
	{
		return $this->entryId;
	}

	public function getItem() : ItemStack
	{
		return $this->item;
	}

	public function getGroupId() : int
	{
		return $this->groupId;
	}

	public static function read(NetworkBinaryStream $in, int $protocolVersion) : self
	{
		$entryId = $in->readCreativeItemNetId();
		$item = $in->getItemStackWithoutStackId($protocolVersion);
		if ($protocolVersion >= ProtocolInfo::PROTOCOL_776) {
			$groupId = $in->getUnsignedVarInt();
		}
		return new self($entryId, $item, $groupId ?? 0);
	}

	public function write(NetworkBinaryStream $out, int $protocolVersion) : void
	{
		$out->writeCreativeItemNetId($this->entryId);
		$out->putItemStackWithoutStackId($this->item, $protocolVersion);
		if ($protocolVersion >= ProtocolInfo::PROTOCOL_776) {
			$out->putUnsignedVarInt($this->groupId);
		}
	}
}
