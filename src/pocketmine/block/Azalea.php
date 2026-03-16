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

use pocketmine\block\utils\ColorBlockMetaHelper;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;

class Azalea extends Transparent
{
	protected $id = self::AZALEA;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Azalea";
	}

	private function canBeSupportedBy(Block $b) : bool
	{
		return match ($b->getId()) {
			BlockIds::GRASS, BlockIds::DIRT, BlockIds::DIRT_WITH_ROOTS, BlockIds::PODZOL, BlockIds::MOSS_BLOCK, BlockIds::FARMLAND, BlockIds::MUD, BlockIds::CLAY_BLOCK => true,
			default => false,
		};
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool
	{
		if ($this->canBeSupportedBy($this->getSide(Facing::DOWN))) {
			return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}

	public function onNearbyBlockChange() : void
	{
		if (!$this->canBeSupportedBy($this->getSide(Facing::DOWN))) {
			$this->level->useBreakOn($this);
		}
	}

	public function getBlockProtocol(int $playerProtocol) : ?Block
	{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_440) {
			return BlockFactory::get(BlockIds::GRASS, ColorBlockMetaHelper::GRAY);
		}

		return null;
	}
}
