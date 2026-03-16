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

namespace pocketmine\level\biome;

use pocketmine\block\utils\TreeType;
use pocketmine\entity\passive\Wolf;
use pocketmine\level\generator\populator\TallGrass;
use pocketmine\level\generator\populator\Tree;

class ForestBiome extends GrassyBiome
{
	private TreeType $type;

	public function __construct(?TreeType $type = null)
	{
		parent::__construct();

		$this->type = $type ?? TreeType::OAK();

		$trees = new Tree($type);
		$trees->setBaseAmount(5);
		$this->addPopulator($trees);

		$tallGrass = new TallGrass();
		$tallGrass->setBaseAmount(3);

		$this->addPopulator($tallGrass);

		$this->setElevation(63, 81);

		if ($this->type->equals(TreeType::BIRCH())) {
			$this->temperature = 0.6;
			$this->rainfall = 0.5;
		} else {
			$this->temperature = 0.7;
			$this->rainfall = 0.8;
		}

		if (!$this->type->equals(TreeType::BIRCH())) {
			$this->spawnableCreatureList[] = new SpawnListEntry(Wolf::class, 5, 4, 4);
		}
	}

	public function getName() : string
	{
		return $this->type->getDisplayName() . " Forest";
	}
}
