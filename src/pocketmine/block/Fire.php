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

use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\event\entity\EntityCombustByBlockEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\math\Facing;

use function min;
use function mt_rand;

class Fire extends Flowable
{
	protected $id = self::FIRE;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function hasEntityCollision() : bool
	{
		return true;
	}

	public function getName() : string
	{
		return "Fire Block";
	}

	public function getLightLevel() : int
	{
		return 15;
	}

	public function isBreakable(Item $item) : bool
	{
		return false;
	}

	public function canBeReplaced() : bool
	{
		return true;
	}

	public function onEntityCollide(Entity $entity) : void
	{
		$ev = new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_FIRE, 1);
		$entity->attack($ev);

		$ev = new EntityCombustByBlockEvent($this, $entity, 8);
		if ($entity instanceof Arrow) {
			$ev->setCancelled();
		}
		$ev->call();
		if (!$ev->isCancelled()) {
			$entity->setOnFire($ev->getDuration());
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [];
	}

	private function canBeSupportedBy(Block $block) : bool
	{
		return $block->isSolid();
	}

	public function onNearbyBlockChange() : void
	{
		$level = $this->level;
		$down = $this->getSide(Facing::DOWN);
		if (SoulFire::canBeSupportedBy($down)) {
			$level->setBlock($this, BlockFactory::get(BlockIds::SOUL_FIRE), true);
		} elseif (!$this->canBeSupportedBy($this->getSide(Facing::DOWN)) && !$this->hasAdjacentFlammableBlocks()) {
			$level->setBlock($this, BlockFactory::get(BlockIds::AIR), true);
		} else {
			$level->scheduleDelayedBlockUpdate($this, mt_rand(30, 40));
		}
	}

	public function ticksRandomly() : bool
	{
		return true;
	}

	public function onRandomTick() : void
	{
		$down = $this->getSide(Facing::DOWN);

		$result = null;
		if ($this->meta < 15 && mt_rand(0, 2) === 0) {
			$this->meta++;
			$result = $this;
		}
		$canSpread = true;

		if (!$down->burnsForever()) {
			//TODO: check rain
			if ($this->meta === 15) {
				if (!$down->isFlammable() && mt_rand(0, 3) === 3) { //1/4 chance to extinguish
					$canSpread = false;
					$result = BlockFactory::get(Block::AIR);
				}
			} elseif (!$this->hasAdjacentFlammableBlocks()) {
				$canSpread = false;
				if (!$down->isSolid() || $this->meta > 3) { //fire older than 3, or without a solid block below
					$result = BlockFactory::get(Block::AIR);
				}
			}
		}

		if ($result !== null) {
			$this->level->setBlock($this, $result);
		}

		$this->level->scheduleDelayedBlockUpdate($this, mt_rand(30, 40));

		if ($canSpread) {
			//TODO: raise upper bound for chance in humid biomes

			foreach ($this->getHorizontalSides() as $side) {
				$this->burnBlock($side, 300);
			}

			//vanilla uses a 250 upper bound here, but I don't think they intended to increase the chance of incineration
			$this->burnBlock($this->getSide(Facing::UP), 350);
			$this->burnBlock($this->getSide(Facing::DOWN), 350);

			//TODO: fire spread
		}
	}

	public function onScheduledUpdate() : void
	{
		$this->onRandomTick();
	}

	private function hasAdjacentFlammableBlocks() : bool
	{
		for ($i = 0; $i <= 5; ++$i) {
			if ($this->getSide($i)->isFlammable()) {
				return true;
			}
		}

		return false;
	}

	private function burnBlock(Block $block, int $chanceBound) : void
	{
		if (mt_rand(0, $chanceBound) < $block->getFlammability()) {
			$ev = new BlockBurnEvent($block, $this);
			$ev->call();
			if (!$ev->isCancelled()) {
				$block->onIncinerate();

				if (mt_rand(0, $this->meta + 9) < 5) { //TODO: check rain
					$this->level->setBlock($block, BlockFactory::get(Block::FIRE, min(15, $this->meta + (mt_rand(0, 4) >> 2))));
				} else {
					$this->level->setBlock($block, BlockFactory::get(Block::AIR));
				}
			}
		}
	}
}
