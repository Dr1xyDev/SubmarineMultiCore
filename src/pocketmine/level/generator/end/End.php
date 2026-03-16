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

namespace pocketmine\level\generator\end;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\level\biome\Biome;
use pocketmine\level\biome\BiomeIds;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\end\populator\EndPillar;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\noise\Simplex;
use pocketmine\level\generator\populator\Populator;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

use function abs;

class End extends Generator
{
	/** @var Populator[] */
	private array $populators = [];
	private int $emptyHeight = 32;
	private int $emptyAmplitude = 1;
	private float $density = 0.6;

	/** @var Populator[] */
	private array $generationPopulators = [];
	private Simplex $noiseBase;

	public function init(ChunkManager $level, Random $random) : void
	{
		parent::init($level, $random);

		$this->random->setSeed($this->level->getSeed());
		$this->noiseBase = new Simplex($this->random, 4, 1 / 4, 1 / 64);
		$this->random->setSeed($this->level->getSeed());

		$this->populators[] = new EndPillar();
	}

	public function getName() : string
	{
		return "end";
	}

	public function generateChunk(int $chunkX, int $chunkZ) : void
	{
		$this->random->setSeed(0xa6fe78dc ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());

		$noise = $this->noiseBase->getFastNoise3D(16, 128, 16, 4, 8, 4, $chunkX * 16, 0, $chunkZ * 16);

		$chunk = $this->level->getChunk($chunkX, $chunkZ);

		$endStone = BlockFactory::get(Block::END_STONE)->getFullId();
		for ($x = 0; $x < 16; ++$x) {
			for ($z = 0; $z < 16; ++$z) {

				$biome = Biome::getBiome(BiomeIds::THE_END);
				$biome->setGroundCover([
					BlockFactory::get(Block::OBSIDIAN, 0)
				]);
				$chunk->setBiomeId($x, $z, $biome->getId());
				$color = [0, 0, 0];
				$bColor = 2;
				$color[0] += (($bColor >> 16) ** 2);
				$color[1] += ((($bColor >> 8) & 0xff) ** 2);
				$color[2] += (($bColor & 0xff) ** 2);

				for ($y = 0; $y < 128; ++$y) {

					$noiseValue = (abs($this->emptyHeight - $y) / $this->emptyHeight) * $this->emptyAmplitude - $noise[$x][$z][$y];
					$noiseValue -= 1 - $this->density;

					$distance = new Vector3(0, 64, 0);
					$distance = $distance->distance(new Vector3($chunkX * 16 + $x, ($y / 1.3), $chunkZ * 16 + $z));

					if ($noiseValue < 0 && $distance < 100 || $noiseValue < -0.2 && $distance > 400) {
						$chunk->setFullBlock($x, $y, $z, $endStone);
					}
				}
			}
		}

		foreach ($this->generationPopulators as $populator) {
			$populator->populate($this->level, $chunkX, $chunkZ, $this->random);
		}
	}

	public function populateChunk(int $chunkX, int $chunkZ) : void
	{
		$this->random->setSeed(0xa6fe78dc ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
		foreach ($this->populators as $populator) {
			$populator->populate($this->level, $chunkX, $chunkZ, $this->random);
		}

		$chunk = $this->level->getChunk($chunkX, $chunkZ);
		$biome = Biome::getBiome($chunk->getBiomeId(7, 7));
		$biome->populateChunk($this->level, $chunkX, $chunkZ, $this->random);
	}

	public function getSpawn() : Vector3
	{
		return new Vector3(128, 128, 128);
	}
}
