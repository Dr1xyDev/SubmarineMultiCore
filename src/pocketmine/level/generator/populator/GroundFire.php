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

namespace pocketmine\level\generator\populator;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;

class GroundFire implements Populator
{
	private int $randomAmount;
	private int $baseAmount;

	public function setRandomAmount(int $amount) : void
	{
		$this->randomAmount = $amount;
	}

	public function setBaseAmount(int $amount) : void
	{
		$this->baseAmount = $amount;
	}

	public function populate(ChunkManager $level, int $chunkX, int $chunkZ, Random $random) : void
	{
		$fire = BlockFactory::get(BlockIds::FIRE);
		$amount = $random->nextRange(0, $this->randomAmount + 1) + $this->baseAmount;
		for ($i = 0; $i < $amount; ++$i) {
			$x = $random->nextRange($chunkX * 16, $chunkX * 16 + 15);
			$z = $random->nextRange($chunkZ * 16, $chunkZ * 16 + 15);
			$y = $this->getHighestWorkableBlock($level, $x, $z);
			if ($y !== -1 && $this->canGroundFireStay($level, $x, $y, $z)) {
				$level->setBlockAt($x, $y, $z, $fire);
			}
		}
	}

	private function canGroundFireStay(ChunkManager $level, int $x, int $y, int $z) : bool
	{
		$b = $level->getBlockAt($x, $y, $z)->getId();
		return ($b === BlockIds::AIR || $b === BlockIds::SNOW_LAYER) && $level->getBlockAt($x, $y - 1, $z)->getId() === BlockIds::NETHERRACK;
	}

	private function getHighestWorkableBlock(ChunkManager $level, int $x, int $z) : int
	{
		for ($y = 0; $y <= 127; ++$y) {
			$b = $level->getBlockAt($x, $y, $z)->getId();
			if ($b == BlockIds::AIR) {
				break;
			}
		}

		return $y === 0 ? -1 : $y;
	}
}
