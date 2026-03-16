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

use pocketmine\block\utils\TreeType;
use pocketmine\network\mcpe\protocol\ProtocolInfo;

class CherrySapling extends Sapling
{
	protected $id = self::CHERRY_SAPLING;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Cherry Sapling";
	}

	public function getTreeType() : ?TreeType
	{
		return TreeType::CHERRY();
	}

	public function getVariantBitmask() : int
	{
		return 0x00;
	}

	public function getReadyBitmask() : int {
		return 0x01;
	}

	public function getBlockProtocol(int $playerProtocol) : ?Block
	{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_582) {
			return BlockFactory::get(BlockIds::SAPLING);
		}

		return null;
	}
}
