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
use pocketmine\event\entity\EntityCombustByBlockEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;

abstract class BaseFire extends Flowable
{
	public function hasEntityCollision() : bool{
		return true;
	}

	public function canBeReplaced() : bool{
		return true;
	}

	public function onEntityCollide(Entity $entity) : void{
		$ev = new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_FIRE, $this->getFireDamage());
		$entity->attack($ev);

		$ev = new EntityCombustByBlockEvent($this, $entity, 8);
		if($entity instanceof Arrow){
			$ev->setCancelled();
		}
		$ev->call();
		if(!$ev->isCancelled()){
			$entity->setOnFire($ev->getDuration());
		}
	}

	abstract protected function getFireDamage() : int;

	public function getDropsForCompatibleTool(Item $item) : array{
		return [];
	}
}
