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

class StoneSlab3 extends StoneSlab
{
	public const TYPE_END_STONE_BRICK = 0;
	public const TYPE_SMOOTH_RED_SANDSTONE = 1;
	public const TYPE_POLISHED_ANDESITE = 2;
	public const TYPE_ANDESITE = 3;
	public const TYPE_DIORITE = 4;
	public const TYPE_POLISHED_DIORITE = 5;
	public const TYPE_GRANITE = 6;
	public const TYPE_POLISHED_GRANITE = 7;

	protected $id = self::STONE_SLAB3;

	public function getDoubleSlabId() : int
	{
		return self::DOUBLE_STONE_SLAB3;
	}

	public function getName() : string
	{
		static $names = [
			self::TYPE_END_STONE_BRICK => "End Stone Slab",
			self::TYPE_SMOOTH_RED_SANDSTONE => "Smooth Red Sandstone",
			self::TYPE_POLISHED_ANDESITE => "Polized Andessite",
			self::TYPE_ANDESITE => "Andesite",
			self::TYPE_DIORITE => "Diorite",
			self::TYPE_POLISHED_DIORITE => "Polized Diorite",
			self::TYPE_GRANITE => "Granite",
			self::TYPE_POLISHED_GRANITE => "Polized Granite",
		];

		return ($this->isTop() ? "Upper " : "") . ($names[$this->getVariant()] ?? "") . " Slab";
	}

	public function getBlockProtocol(int $playerProtocol) : ?Block
	{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_332) {
			return BlockFactory::get(BlockIds::STONE_SLAB, $this->isTop() ? 0x08 : 0);
		}

		return null;
	}
}
