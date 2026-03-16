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

namespace pocketmine\entity\hostile;

use pocketmine\block\Block;
use pocketmine\entity\Ageable;
use pocketmine\entity\behavior\AvoidMobTypeBehavior;
use pocketmine\entity\behavior\CreeperSwellBehavior;
use pocketmine\entity\behavior\FloatBehavior;
use pocketmine\entity\behavior\HurtByTargetBehavior;
use pocketmine\entity\behavior\LookAtPlayerBehavior;
use pocketmine\entity\behavior\MeleeAttackBehavior;
use pocketmine\entity\behavior\NearestAttackableTargetBehavior;
use pocketmine\entity\behavior\RandomLookAroundBehavior;
use pocketmine\entity\behavior\RandomStrollBehavior;
use pocketmine\entity\Monster;
use pocketmine\entity\passive\Cat;
use pocketmine\item\FlintSteel;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\Explosion;
use pocketmine\level\GameRules;
use pocketmine\level\sound\IgniteSound;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\tile\Skull;

use function boolval;
use function intval;
use function rand;

class Creeper extends Monster implements Ageable
{
	public const NETWORK_ID = self::CREEPER;

	public float $width = 0.6;
	public float $height = 1.7;

	protected $lastActiveTime = 0;
	protected $timeSinceIgnited = 0;
	protected $fuseTime = 30;
	protected $explosionRadius = 3;

	protected function initEntity() : void
	{
		$this->setMaxHealth(20);
		$this->setMovementSpeed(0.25);
		$this->setFollowRange(35);
		$this->setAttackDamage(1.0);

		$this->explosionRadius = $this->namedtag->getByte("ExplosionRadius", $this->explosionRadius);
		$this->fuseTime = $this->namedtag->getShort("Fuse", $this->fuseTime);

		$this->setIgnited(boolval($this->namedtag->getByte("ignited", 0)));
		$this->setPowered(boolval($this->namedtag->getByte("powered", 0)));
		$this->propertyManager->setInt(self::DATA_FUSE_LENGTH, $this->fuseTime);
		$this->propertyManager->setByte(self::DATA_CREEPER_SWELL_DIRECTION, -1);

		parent::initEntity();
	}

	public function saveNBT() : void
	{
		parent::saveNBT();

		$this->namedtag->setByte("ignited", intval($this->isIgnited()));
		$this->namedtag->setByte("powered", intval($this->isPowered()));
		$this->namedtag->setShort("Fuse", $this->fuseTime);
		$this->namedtag->setByte("ExplosionRadius", $this->explosionRadius);
	}

	public function getName() : string
	{
		return "Creeper";
	}

	protected function addBehaviors() : void
	{
		$this->behaviorPool->setBehavior(0, new FloatBehavior($this));
		$this->behaviorPool->setBehavior(1, new CreeperSwellBehavior($this));
		// TODO: Avoid from ocelot
		$this->behaviorPool->setBehavior(2, new AvoidMobTypeBehavior($this, Cat::class, 6, 1, 1.2));
		$this->behaviorPool->setBehavior(3, new MeleeAttackBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(4, new RandomStrollBehavior($this, 0.8));
		$this->behaviorPool->setBehavior(5, new LookAtPlayerBehavior($this, 8.0));
		$this->behaviorPool->setBehavior(6, new RandomLookAroundBehavior($this));

		$this->targetBehaviorPool->setBehavior(0, new NearestAttackableTargetBehavior($this, Player::class, true));
		$this->targetBehaviorPool->setBehavior(1, new HurtByTargetBehavior($this));
	}

	public function getXpDropAmount() : int
	{
		return $this->getRevengeTarget() instanceof Player ? 5 : 0;
	}

	public function getDrops() : array
	{
		$drops = [];
		if ($this->timeSinceIgnited !== $this->fuseTime) {
			$drops[] = ItemFactory::get(Item::GUNPOWDER, 0, rand(0, 2));
		}
		$attacker = $this->getRevengeTarget();
		if ($attacker instanceof Skeleton) {
			$drops[] = ItemFactory::get(rand(Item::RECORD_13, Item::RECORD_WAIT));
		} elseif ($attacker instanceof Creeper && $attacker->isPowered()) {
			$drops[] = ItemFactory::get(Block::SKULL_BLOCK, Skull::TYPE_CREEPER);
		}
		return $drops;
	}

	public function isIgnited() : bool
	{
		return $this->getGenericFlag(self::DATA_FLAG_IGNITED);
	}

	public function isPowered() : bool
	{
		return $this->getGenericFlag(self::DATA_FLAG_POWERED);
	}

	public function setPowered(bool $value) : void
	{
		$this->setGenericFlag(self::DATA_FLAG_POWERED, $value);
	}

	public function setIgnited(bool $value) : void
	{
		$this->setGenericFlag(self::DATA_FLAG_IGNITED, $value);
	}

	public function explode() : void
	{
		if ($this->isValid()) {
			$f = $this->isPowered() ? 2 : 1;
			$exp = new Explosion($this, $this->explosionRadius * $f, $this);
			$this->flagForDespawn();

			if ($this->level->getGameRules()->getBool(GameRules::RULE_MOB_GRIEFING, true)) {
				$exp->explodeA();
			}
			$exp->explodeB();
		}
	}

	public function entityBaseTick(int $diff = 1) : bool
	{
		$hasUpdate = parent::entityBaseTick($diff);

		$this->lastActiveTime = $this->timeSinceIgnited;

		if ($this->isIgnited()) {
			if ($this->timeSinceIgnited === 0) {
				$this->broadcastSound(new IgniteSound($this));
				$this->propertyManager->setByte(self::DATA_CREEPER_SWELL_DIRECTION, 1);
			}

			if ($this->timeSinceIgnited % 5 == 0) {
				$this->propertyManager->setInt(self::DATA_FUSE_LENGTH, $this->fuseTime - $this->timeSinceIgnited);
			}

			if ($this->timeSinceIgnited++ >= $this->fuseTime) {
				$this->timeSinceIgnited = $this->fuseTime;
				$this->explode();
			}

			$this->propertyManager->setByte(self::DATA_CREEPER_SWELL, $this->timeSinceIgnited);
			if ($this->timeSinceIgnited > 1) {
				$this->propertyManager->setByte(self::DATA_CREEPER_SWELL_PREVIOUS, $this->timeSinceIgnited - 1);
			}
		} else {
			if ($this->timeSinceIgnited > 0) {
				$this->timeSinceIgnited = 0;

				$this->propertyManager->setByte(self::DATA_CREEPER_SWELL_DIRECTION, -1);
				$this->propertyManager->setByte(self::DATA_CREEPER_SWELL, 0);
				$this->propertyManager->setByte(self::DATA_CREEPER_SWELL_PREVIOUS, 0);
			}
		}

		return $hasUpdate;
	}

	public function fall(float $fallDistance) : void
	{
		parent::fall($fallDistance);

		$this->timeSinceIgnited += intval($fallDistance * 1.5);

		if ($this->timeSinceIgnited >= $this->fuseTime - 5) {
			$this->timeSinceIgnited = $this->fuseTime - 5;
		}
	}

	public function onInteract(Player $player, Vector3 $clickPos) : bool
	{
		$item = $player->getInventory()->getItemInHand();
		if ($item instanceof FlintSteel) {
			$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_IGNITE);

			if ($this->isValid()) {
				$this->setIgnited(true);
				$item->applyDamage(1);

				return true;
			}
		}

		return parent::onInteract($player, $clickPos);
	}
}
