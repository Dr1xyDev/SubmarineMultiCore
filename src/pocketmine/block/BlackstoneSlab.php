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

namespace pocketmine\block;

use pocketmine\network\mcpe\protocol\ProtocolInfo;

class BlackstoneSlab extends StoneSlab
{
	protected int $doubleSlabId;

	public function __construct(int $id, int $meta, string $name, int $doubleSlabId)
	{
		$this->id = $id;
		$this->meta = $meta;
		$this->fallbackName = $name;
		$this->doubleSlabId = $doubleSlabId;
	}

	public function getDoubleSlabId() : int
	{
		return $this->doubleSlabId;
	}

	public function getHardness() : float
	{
		return 1.5;
	}

	public function getVariantBitmask() : int
	{
		return 0x00;
	}

	public function getTopBitmask() : int
	{
		return 0x01;
	}

	public function getName() : string
	{
		return ($this->isTop() ? "Upper " : "") . $this->fallbackName;
	}

	public function getBlockProtocol(int $playerProtocol) : ?Block
	{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_419) {
			return BlockFactory::get(BlockIds::STONE_SLAB, ($this->isTop() ? 0x08 : 0));
		}

		return null;
	}
}
