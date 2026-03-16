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

use pocketmine\entity\behavior\AvoidMobTypeBehavior;
use pocketmine\entity\behavior\FleeSunBehavior;
use pocketmine\entity\behavior\FloatBehavior;
use pocketmine\entity\behavior\LookAtPlayerBehavior;
use pocketmine\entity\behavior\NearestAttackableTargetBehavior;
use pocketmine\entity\behavior\RandomLookAroundBehavior;
use pocketmine\entity\behavior\RandomStrollBehavior;
use pocketmine\entity\behavior\RangedAttackBehavior;
use pocketmine\entity\behavior\RestrictSunBehavior;
use pocketmine\entity\Entity;
use pocketmine\entity\Monster;
use pocketmine\entity\passive\Wolf;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\RangedAttackerMob;
use pocketmine\entity\Smite;
use pocketmine\inventory\AltayEntityEquipment;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\Player;

use function rand;
use function sqrt;

class Skeleton extends Monster implements RangedAttackerMob, Smite
{
	public const NETWORK_ID = self::SKELETON;

	public float $width = 0.6;
	public float $height = 1.99;

	/** @var AltayEntityEquipment */
	protected $equipment;

	protected function initEntity() : void
	{
		$this->setMovementSpeed(0.25);
		$this->setFollowRange(35);
		$this->setAttackDamage(2);

		parent::initEntity();

		$this->equipment = new AltayEntityEquipment($this);

		$this->equipment->setItemInHand(ItemFactory::get(Item::BOW));

		// TODO: Armors
	}

	public function getName() : string
	{
		return "Skeleton";
	}

	public function getDrops() : array
	{
		return [
			ItemFactory::get(Item::BONE, 0, rand(0, 2)), ItemFactory::get(Item::ARROW, 0, rand(0, 2))
		];
	}

	public function getXpDropAmount() : int
	{
		return 5;
	}

	protected function addBehaviors() : void
	{
		$this->behaviorPool->setBehavior(0, new FloatBehavior($this));
		$this->behaviorPool->setBehavior(1, new RestrictSunBehavior($this));
		$this->behaviorPool->setBehavior(2, new FleeSunBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(3, new AvoidMobTypeBehavior($this, Wolf::class, 6.0, 1.0, 1.2));
		$this->behaviorPool->setBehavior(4, new RandomStrollBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(5, new RangedAttackBehavior($this, 1.0, 20, 60, 15.0));
		$this->behaviorPool->setBehavior(6, new LookAtPlayerBehavior($this, 8.0));
		$this->behaviorPool->setBehavior(7, new RandomLookAroundBehavior($this));

		$this->targetBehaviorPool->setBehavior(0, new NearestAttackableTargetBehavior($this, Player::class, true));
		//$this->targetBehaviorPool->setBehavior(2, new NearestAttackableTargetBehavior($this, IronGolem::class, false));
	}

	public function onRangedAttackToTarget(Entity $target, float $power) : void
	{
		$pos = $this->add(0, $this->getEyeHeight() - 0.1, 0);
		$motion = $target->add(0, $target->height / 3, 0)->subtractVector($pos)->normalize();
		$f = sqrt($motion->x ** 2 + $motion->z ** 2);

		/** @var Arrow $arrow */
		$arrow = Entity::createEntity("Arrow", $this->level, Entity::createBaseNBT($pos->addVector($motion)));
		// TODO: Enchants
		$arrow->setThrowableMotion($motion->add(0, $f * 0.2, 0), 1.6, (14 - $this->level->getDifficulty() * 4));
		$arrow->setPickupMode(Arrow::PICKUP_NONE);
		$arrow->setBaseDamage($power * 2 + $this->random->nextFloat() * 0.25 + ($this->level->getDifficulty() * 0.11));

		$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_BOW);
		$arrow->spawnToAll();
	}

	public function entityBaseTick(int $diff = 1) : bool
	{
		if (!$this->isOnFire() && $this->level->isDayTime() && !$this->isImmobile()) {
			if (!$this->isInsideOfWater() && $this->level->canSeeSky($this)) {
				$this->setOnFire(5);
			}
		}
		return parent::entityBaseTick($diff);
	}

	public function sendSpawnPacket(Player $player) : void
	{
		parent::sendSpawnPacket($player);

		$this->equipment->sendContents([$player]);

		// stupid hack for 1.1
		$player->sendDataPacket(MobEquipmentPacket::create(
			$this->getId(),
			ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($this->equipment->getItemInHand(), $player->getProtocolVersion())),
			10,
			10,
			ContainerIds::INVENTORY
		));
	}
}
