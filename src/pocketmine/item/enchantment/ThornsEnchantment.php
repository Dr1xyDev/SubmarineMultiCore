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

namespace pocketmine\item\enchantment;

use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

class ThornsEnchantment extends Enchantment
{
	public function getMinEnchantAbility(int $level) : int
	{
		return 10 + ($level - 1) * 20;
	}

	public function getMaxEnchantAbility(int $level) : int
	{
		return $this->getMinEnchantAbility($level) + 50;
	}

	public function onHurtEntity(Entity $attacker, Entity $victim, Item $item, int $enchantmentLevel) : void
	{
		if ($attacker instanceof Human) {
			if ($enchantmentLevel > 0 && $victim->random->nextFloat() < 0.15 * $enchantmentLevel) {
				$victim->attack(new EntityDamageByEntityEvent($attacker, $victim, EntityDamageEvent::CAUSE_ENTITY_ATTACK, ($enchantmentLevel > 10 ? $enchantmentLevel - 10 : 1 + $victim->random->nextBoundedInt(4))));
				$victim->level->broadcastLevelSoundEvent($victim, LevelSoundEventPacket::SOUND_THORNS);

				if ($item instanceof Durable) {
					$item->applyDamage(3);
				}
			} elseif ($item instanceof Durable) {
				$item->applyDamage(1);
			}
		}
	}
}
