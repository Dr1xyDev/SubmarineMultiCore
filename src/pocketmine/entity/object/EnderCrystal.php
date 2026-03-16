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

namespace pocketmine\entity\object;

use pocketmine\block\Block;
use pocketmine\block\Fire;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\Explosion;
use pocketmine\level\GameRules;
use pocketmine\level\generator\end\End;

class EnderCrystal extends Entity
{
	public const NETWORK_ID = self::ENDER_CRYSTAL;

	public float $height = 0.98;
	public float $width = 0.98;

	public $gravity = 0;
	public $drag = 0;

	public function onMovementUpdate() : void
	{
		// NOOP
	}

	public function onUpdate(int $currentTick) : bool
	{
		if ($this->level->getProvider()->getPath() === End::class) {
			if ($this->level->getBlock($this)->getId() !== Block::FIRE) {
				$this->level->setBlock($this, new Fire());
			}
		}

		return parent::onUpdate($currentTick);
	}

	public function attack(EntityDamageEvent $source) : void
	{
		parent::attack($source);

		if (!$this->isFlaggedForDespawn() && !$source->isCancelled() && $source->getCause() !== EntityDamageEvent::CAUSE_FIRE && $source->getCause() !== EntityDamageEvent::CAUSE_FIRE_TICK) {
			$this->flagForDespawn();

			if ($this->level->getGameRules()->getBool(GameRules::RULE_TNT_EXPLODES)) {
				$exp = new Explosion($this, 6, $this);

				$exp->explodeA();
				$exp->explodeB();
			}
		}
	}

	public function canBeCollidedWith() : bool
	{
		return false;
	}

	public function setShowBase(bool $value) : void
	{
		$this->setGenericFlag(self::DATA_FLAG_SHOWBASE, $value);
	}

	public function showBase() : bool
	{
		return $this->getGenericFlag(self::DATA_FLAG_SHOWBASE);
	}

	public function getPickedItem() : ?Item
	{
		return ItemFactory::get(Item::END_CRYSTAL);
	}
}
