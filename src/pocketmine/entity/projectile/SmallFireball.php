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

namespace pocketmine\entity\projectile;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\entity\Living;
use pocketmine\level\GameRules;
use pocketmine\math\RayTraceResult;

class SmallFireball extends Projectile
{
	public const NETWORK_ID = self::SMALL_FIREBALL;

	public float $height = 0.3125;
	public float $width = 0.3125;

	protected $damage = 5.0;
	protected $life = 0;

	public function getName() : string
	{
		return "SmallFireball";
	}

	public function initEntity() : void
	{
		parent::initEntity();

		$this->life = $this->namedtag->getInt("life", 0);
	}

	public function onUpdate(int $currentTick) : bool
	{
		if ($this->isAlive() && !$this->closed && !$this->isFlaggedForDespawn()) {
			$this->setOnFire(1);

			if ($this->life++ > 600) {
				$this->flagForDespawn();
			}
		}
		return parent::onUpdate($currentTick);
	}

	public function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void
	{
		parent::onHitBlock($blockHit, $hitResult);

		$this->flagForDespawn();

		$owner = $this->getOwningEntity();
		if ($owner instanceof Living) {
			if ($this->level->getGameRules()->getBool(GameRules::RULE_MOB_GRIEFING)) {
				$block = $this->level->getBlock($this);
				if ($block instanceof Air) {
					$this->level->setBlock($this, BlockFactory::get(Block::FIRE));
				}
			}
		}
	}

	public function saveNBT() : void
	{
		parent::saveNBT();

		$this->namedtag->setInt("life", $this->life);
	}
}
