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

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\inventory\InventoryPickupArrowEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\Potion;
use pocketmine\level\Level;
use pocketmine\level\particle\MobSpellParticle;
use pocketmine\level\sound\ArrowHitSound;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\TakeItemActorPacket;
use pocketmine\Player;

use function mt_rand;
use function sqrt;

class Arrow extends Projectile
{
	public const NETWORK_ID = self::ARROW;

	public const PICKUP_NONE = 0;
	public const PICKUP_ANY = 1;
	public const PICKUP_CREATIVE = 2;

	private const TAG_PICKUP = "pickup"; //TAG_Byte
	private const TAG_POTION = "Potion"; //TAG_Short

	public float $width = 0.25;
	public float $height = 0.25;

	protected $gravity = 0.05;
	protected $drag = 0.01;

	/** @var float */
	protected $damage = 2.0;

	/** @var int */
	protected $pickupMode = self::PICKUP_ANY;

	/** @var float */
	protected $punchKnockback = 0.0;

	/** @var int */
	protected $collideTicks = 0;

	/** @var int */
	protected $potionId = 0;

	public function __construct(Level $level, CompoundTag $nbt, ?Entity $shootingEntity = null, bool $critical = false)
	{
		parent::__construct($level, $nbt, $shootingEntity);
		$this->setCritical($critical);
	}

	protected function initEntity() : void
	{
		parent::initEntity();

		$this->pickupMode = $this->namedtag->getByte(self::TAG_PICKUP, self::PICKUP_ANY, true);
		$this->collideTicks = $this->namedtag->getShort("life", $this->collideTicks);
		if (!$this->namedtag->hasTag(self::TAG_POTION, ShortTag::class)) {
			$this->namedtag->setShort(self::TAG_POTION, $this->potionId);
		}
		$this->potionId = $this->namedtag->getShort(self::TAG_POTION, 0);
	}

	public function setThrowableMotion(Vector3 $motion, float $velocity, float $inaccuracy) : bool
	{
		return $this->setMotion($motion->add(
			$this->random->nextFloat() * ($this->random->nextBoolean() ? 1 : -1) * 0.0075 * $inaccuracy,
			$this->random->nextFloat() * ($this->random->nextBoolean() ? 1 : -1) * 0.0075 * $inaccuracy,
			$this->random->nextFloat() * ($this->random->nextBoolean() ? 1 : -1) * 0.0075 * $inaccuracy
		)
			->multiply($velocity));
	}

	public function saveNBT() : void
	{
		parent::saveNBT();

		$this->namedtag->setByte(self::TAG_PICKUP, $this->pickupMode, true);
		$this->namedtag->setShort(self::TAG_POTION, $this->potionId);
		$this->namedtag->setShort("life", $this->collideTicks);
	}

	public function isCritical() : bool
	{
		return $this->getGenericFlag(self::DATA_FLAG_CRITICAL);
	}

	public function setCritical(bool $value = true) : void
	{
		$this->setGenericFlag(self::DATA_FLAG_CRITICAL, $value);
	}

	public function getResultDamage() : int
	{
		$base = parent::getResultDamage();
		if ($this->isCritical()) {
			return ($base + mt_rand(0, (int) ($base / 2) + 1));
		} else {
			return $base;
		}
	}

	public function getPunchKnockback() : float
	{
		return $this->punchKnockback;
	}

	public function setPunchKnockback(float $punchKnockback) : void
	{
		$this->punchKnockback = $punchKnockback;
	}

	public function entityBaseTick(int $tickDiff = 1) : bool
	{
		if ($this->closed) {
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if ($this->potionId !== 0) {
			if (!$this->onGround || ($this->onGround && ($tickDiff % 4) === 0)) {
				$color = Potion::getColor($this->potionId - 1);
				$this->level->addParticle(new MobSpellParticle($this->add(
					$this->width / 2 + mt_rand(-100, 100) / 500,
					$this->height / 2 + mt_rand(-100, 100) / 500,
					$this->width / 2 + mt_rand(-100, 100) / 500
				), $color[0], $color[1], $color[2]));
			}
			$hasUpdate = true;
		}

		if ($this->blockHit !== null) {
			$this->collideTicks += $tickDiff;
			if ($this->collideTicks > 1200) {
				$this->flagForDespawn();
				$hasUpdate = true;
			}
		} else {
			$this->collideTicks = 0;
		}

		return $hasUpdate;
	}

	protected function onHit(ProjectileHitEvent $event) : void
	{
		$this->setCritical(false);
		$this->broadcastSound(new ArrowHitSound($this));
	}

	protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void
	{
		parent::onHitBlock($blockHit, $hitResult);
		$this->broadcastEntityEvent(ActorEventPacket::ARROW_SHAKE, 7); //7 ticks
	}

	protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : bool
	{
		if (($hit = parent::onHitEntity($entityHit, $hitResult))) {
			if ($this->potionId !== 0 && $entityHit instanceof Living) {
				foreach (Potion::getPotionEffectsById($this->potionId - 1) as $effect) {
					$entityHit->addEffect($effect->setDuration((int) ($effect->getDuration() / 8)));
				}
			}
			if ($this->punchKnockback > 0) {
				$horizontalSpeed = sqrt($this->motion->x ** 2 + $this->motion->z ** 2);
				if ($horizontalSpeed > 0) {
					$multiplier = $this->punchKnockback * 0.6 / $horizontalSpeed;
					$entityHit->setMotion($entityHit->getMotion()->add($this->motion->x * $multiplier, 0.1, $this->motion->z * $multiplier));
				}
			}
		}

		return $hit;
	}

	public function getPickupMode() : int
	{
		return $this->pickupMode;
	}

	public function setPickupMode(int $pickupMode) : void
	{
		$this->pickupMode = $pickupMode;
	}

	public function getPotionId() : int
	{
		return $this->potionId;
	}

	public function setPotionId(int $potionId) : void
	{
		$this->potionId = $potionId;
	}

	public function onCollideWithPlayer(Player $player) : void
	{
		if ($this->blockHit === null) {
			return;
		}

		$item = ItemFactory::get(Item::ARROW, $this->potionId, 1);

		if ($player->isSurvival()) {
			if ($player->getOffHandInventory()->getItem(0)->canStackWith($item)) {
				$playerInventory = $player->getOffHandInventory();
			} elseif ($player->getInventory()->canAddItem($item)) {
				$playerInventory = $player->getInventory();
			} else {
				return;
			}
		} else {
			$playerInventory = $player->getInventory();
		}

		$ev = new InventoryPickupArrowEvent($playerInventory, $this);
		if ($this->pickupMode === self::PICKUP_NONE || ($this->pickupMode === self::PICKUP_CREATIVE && !$player->isCreative())) {
			$ev->setCancelled();
		}

		$ev->call();
		if ($ev->isCancelled()) {
			return;
		}

		$pk = new TakeItemActorPacket();
		$pk->eid = $player->getId();
		$pk->target = $this->getId();
		$this->server->broadcastPacket($this->getViewers(), $pk);

		$playerInventory->addItem(clone $item);
		$this->flagForDespawn();
	}
}
