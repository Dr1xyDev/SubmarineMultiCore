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

use pocketmine\math\Facing;
use pocketmine\network\mcpe\protocol\ProtocolInfo;

class SoulFire extends Fire
{
	protected $id = self::SOUL_FIRE;

	public function getName() : string
	{
		return "Soul Fire Block";
	}

	public function getLightLevel() : int
	{
		return 10;
	}

	public static function canBeSupportedBy(Block $block) : bool
	{
		//TODO: this really ought to use some kind of tag system
		$id = $block->getId();
		return $id === BlockIds::SOUL_SAND || $id === BlockIds::SOUL_SOIL;
	}

	public function onNearbyBlockChange() : void
	{
		if (!self::canBeSupportedBy($this->getSide(Facing::DOWN))) {
			$this->level->setBlock($this, BlockFactory::get(BlockIds::AIR));
		}
	}

	public function getBlockProtocol(int $playerProtocol) : ?Block
	{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_419) {
			return BlockFactory::get(BlockIds::FIRE, $this->meta);
		}

		return null;
	}
}
