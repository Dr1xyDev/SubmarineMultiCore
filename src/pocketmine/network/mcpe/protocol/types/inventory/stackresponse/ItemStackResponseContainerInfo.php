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

namespace pocketmine\network\mcpe\protocol\types\inventory\stackresponse;

use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\types\inventory\FullContainerName;

use function count;

final class ItemStackResponseContainerInfo
{
	/**
	 * @param ItemStackResponseSlotInfo[] $slots
	 */
	public function __construct(
		private FullContainerName $containerName,
		private array $slots
	) {
	}

	public function getContainerName() : FullContainerName
	{
		return $this->containerName;
	}

	/** @return ItemStackResponseSlotInfo[] */
	public function getSlots() : array
	{
		return $this->slots;
	}

	public static function read(NetworkBinaryStream $in, int $playerProtocol) : self
	{
		$containerName = FullContainerName::read($in, $playerProtocol);
		$slots = [];
		for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
			$slots[] = ItemStackResponseSlotInfo::read($in, $playerProtocol);
		}
		return new self($containerName, $slots);
	}

	public function write(NetworkBinaryStream $out, int $playerProtocol) : void
	{
		$this->containerName->write($out, $playerProtocol);
		$out->putUnsignedVarInt(count($this->slots));
		foreach ($this->slots as $slot) {
			$slot->write($out, $playerProtocol);
		}
	}
}
