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

namespace pocketmine\level\generator\feature\stateproviders;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\block\Flower;
use pocketmine\level\biome\BiomeNoise;
use pocketmine\level\generator\MathHelper;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use function count;

class ForestFlowerStateProvider extends BlockStateProvider {
	/** @var Block[] */
	private array $states;

	public function __construct(){
		$this->states = [
			BlockFactory::get(BlockIds::DANDELION),
			BlockFactory::get(BlockIds::POPPY),
			BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_ALLIUM),
			BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_AZURE_BLUET),
			BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_RED_TULIP),
			BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_ORANGE_TULIP),
			BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_WHITE_TULIP),
			BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_PINK_TULIP),
			BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_OXEYE_DAISY),
			BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_CORNFLOWER),
			BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_LILY_OF_THE_VALLEY)
		];
	}

	public function type() : BlockStateProviderType{
		return BlockStateProviderType::FOREST_FLOWER_STATE_PROVIDER;
	}

	public function getState(Random $random, Vector3 $pos) : Block {
		$noise = MathHelper::clamp((1.0 + BiomeNoise::getInstance()->getInfoNoise()->getValue2D($pos->getX() / 48.0, $pos->getZ() / 48.0)) / 2.0, 0.0, 0.9999);
		return clone $this->states[(int) ($noise * count($this->states))];
	}
}
