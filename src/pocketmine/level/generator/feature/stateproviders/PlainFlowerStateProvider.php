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
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use function count;

class PlainFlowerStateProvider extends BlockStateProvider {
	/** @var Block[] */
	private array $rareFlowers;
	/** @var Block[] */
	private array $commonFlowers;

	public function __construct(){
		$this->rareFlowers = [
			BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_ORANGE_TULIP),
			BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_RED_TULIP),
			BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_PINK_TULIP),
			BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_WHITE_TULIP)
		];
		$this->commonFlowers = [
			BlockFactory::get(BlockIds::POPPY),
			BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_AZURE_BLUET),
			BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_OXEYE_DAISY),
			BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_CORNFLOWER)
		];
	}

	public function type() : BlockStateProviderType{
		return BlockStateProviderType::PLAIN_FLOWER_STATE_PROVIDER;
	}

	public function getState(Random $random, Vector3 $pos) : Block {
		$noise = BiomeNoise::getInstance()->getInfoNoise()->getValue2D($pos->getX() / 200.0, $pos->getZ() / 200.0);
		if ($noise < -0.8) {
			return clone $this->rareFlowers[$random->nextBoundedInt(count($this->rareFlowers))];
		} else {
			return $random->nextBoundedInt(3) > 0 ? clone $this->commonFlowers[$random->nextBoundedInt(count($this->commonFlowers))] : BlockFactory::get(BlockIds::DANDELION);
		}
	}
}
