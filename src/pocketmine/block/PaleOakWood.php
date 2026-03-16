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

use pocketmine\block\utils\PillarRotationHelper;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;

class PaleOakWood extends Wood
{
	protected $id = self::PALE_OAK_WOOD;

	public function getName() : string
	{
		return "Pale Oak Wood";
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool
	{
		$this->meta = PillarRotationHelper::getMetaFromFace($this->meta, $face, true);
		$this->getLevel()->setBlock($blockReplace, $this, true, true);
		return true;
	}

	public function getBlockProtocol(int $playerProtocol) : ?Block{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_766) {
			return BlockFactory::get(BlockIds::LOG, match ($this->meta) {
				0x01 => 0x04,
				0x02 => 0x08,
				default => 0,
			});
		}

		return null;
	}
}
