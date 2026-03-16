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

namespace pocketmine\level\generator\end\populator;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\populator\Populator;
use pocketmine\utils\Random;

use function cos;
use function deg2rad;
use function intval;
use function mt_rand;
use function pi;
use function sin;

class EndPillar implements Populator
{
	/** @var ChunkManager */
	private $level;
	private $randomAmount;
	private $baseAmount;

	public function __construct(int $randomAmount = 0, int $baseAmount = 0)
	{
		$this->baseAmount = $baseAmount;
		$this->randomAmount = $randomAmount;
	}

	public function setRandomAmount($amount)
	{
		$this->randomAmount = $amount;
	}

	public function setBaseAmount($amount)
	{
		$this->baseAmount = $amount;
	}

	public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random) : void
	{
		if (mt_rand(0, 99) < 10) {
			$this->level = $level;
			$amount = $random->nextRange(0, $this->randomAmount + 1) + $this->baseAmount;
			$obsidian = BlockFactory::get(Block::OBSIDIAN);
			for ($i = 0; $i < $amount; ++$i) {
				$x = $random->nextRange($chunkX * 16, $chunkX * 16 + 15);
				$z = $random->nextRange($chunkZ * 16, $chunkZ * 16 + 15);
				$y = $this->getHighestWorkableBlock($x, $z);
				if ($this->level->getBlockAt($x, $y, $z)->getId() == Block::END_STONE) {
					$height = mt_rand(28, 50);
					for ($ny = $y; $ny < $y + $height; $ny++) {
						for ($r = 0.5; $r < 5; $r += 0.5) {
							$nd = 360 / (2 * pi() * $r);
							for ($d = 0; $d < 360; $d += $nd) {
								$level->setBlockAt(intval($x + (cos(deg2rad($d)) * $r)), $ny, intval($z + (sin(deg2rad($d)) * $r)), $obsidian);
							}
						}
					}
				}
			}
		}
	}

	private function getHighestWorkableBlock($x, $z)
	{
		for ($y = 127; $y >= 0; --$y) {
			$b = $this->level->getBlockAt($x, $y, $z)->getId();
			if ($b == Block::END_STONE) {
				break;
			}
		}

		return $y === 0 ? -1 : $y;
	}
}
