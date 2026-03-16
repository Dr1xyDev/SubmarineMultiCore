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
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;

use function min;
use function mt_rand;

class DragonEgg extends Fallable
{
	protected $id = self::DRAGON_EGG;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Dragon Egg";
	}

	public function getHardness() : float
	{
		return 3;
	}

	public function getBlastResistance() : float
	{
		return 45;
	}

	public function getLightLevel() : int
	{
		return 1;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function onActivate(Item $item, Player $player = null) : bool
	{
		$level = $player->getLevel();
		for ($i = 0; $i < 1000; $i++) {
			$x = $this->x + mt_rand(-15, 15);
			$y = $this->y + mt_rand(-7, 7);
			$z = $this->z + mt_rand(-15, 15);
			if ($level->getBlockAt($x, $y, $z)->getId() === Block::AIR && $y < Level::Y_MAX && $y > Level::Y_MIN) {
				$source = $this->asVector3();
				$target = new Vector3($x, $y, $z);

				$level->setBlock($source, BlockFactory::get(Block::AIR));
				$level->setBlock($target, BlockFactory::get(Block::DRAGON_EGG));

				$dir = $target->subtractVector($source)->normalize();
				$max = min(128, $source->distance($target));
				for ($j = 0; $j <= $max; $j++) {
					$this->getLevel()->broadcastLevelEvent($source->addVector($dir->multiply($j)->add(0, 1.5, 0)), LevelEventPacket::EVENT_PARTICLE_DRAGON_EGG_TELEPORT);
				}
				break;
			}
		}

		return true;
	}
}
