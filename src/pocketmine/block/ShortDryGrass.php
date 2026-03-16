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

use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;

class ShortDryGrass extends TallGrass
{
	protected $id = self::SHORT_DRY_GRASS;

	public function getName() : string
	{
		return "Short Dry Grass";
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool
	{
		$down = $this->getSide(Facing::DOWN)->getId();
		if (
			$down === self::GRASS ||
			$down === self::DIRT ||
			$down === self::SAND ||
			$down === self::SUSPICIOUS_SAND ||
			$down === self::MOSS_BLOCK ||
			$down === self::FARMLAND ||
			$down === self::TERRACOTTA ||
			$down === self::MUD ||
			$down === self::MUDDY_MANGROVE_ROOTS ||
			$down === self::PALE_MOSS_BLOCK
		) {
			$this->getLevel()->setBlock($blockReplace, $this, true);

			return true;
		}

		return false;
	}

	public function getDropsForIncompatibleTool(Item $item) : array
	{
		return [];
	}

	public function getBlockProtocol(int $playerProtocol) : ?Block
	{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_786) {
			return BlockFactory::get(BlockIds::TALL_GRASS, TallGrass::TYPE_FERN);
		}

		return null;
	}
}
