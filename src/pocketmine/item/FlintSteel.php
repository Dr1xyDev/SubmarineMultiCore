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

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\level\sound\FlintSteelSound;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;

class FlintSteel extends Tool
{
	public function __construct(int $meta = 0)
	{
		parent::__construct(self::FLINT_STEEL, $meta, "Flint and Steel");
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool
	{
		if ($blockReplace->getId() === self::AIR) {
			$fire = BlockFactory::get((($did = $blockReplace->getSide(Facing::DOWN)->getId()) == BlockIds::SOUL_SAND || $did == BlockIds::SOUL_SOIL) ? BlockIds::SOUL_FIRE : BlockIds::FIRE);
			$level = $player->getLevel();
			$level->setBlock($blockReplace, $fire, true);
			$level->addSound(new FlintSteelSound($blockReplace->add(0.5, 0.5, 0.5)));

			$this->applyDamage(1);

			return true;
		}

		return false;
	}

	public function getMaxDurability() : int
	{
		return 65;
	}
}
