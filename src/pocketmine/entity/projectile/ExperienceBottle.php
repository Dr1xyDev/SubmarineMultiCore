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

use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\level\sound\PotionSplashSound;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\utils\Color;

use function mt_rand;

class ExperienceBottle extends Throwable
{
	public const NETWORK_ID = self::XP_BOTTLE;

	protected $gravity = 0.07;

	public function getResultDamage() : int
	{
		return -1;
	}

	public function onHit(ProjectileHitEvent $event) : void
	{
		$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_PARTICLE_SPLASH, (new Color(0x38, 0x5d, 0xc6))->toARGB());
		$this->broadcastSound(new PotionSplashSound($this));

		$this->level->dropExperience($this, mt_rand(3, 11));
	}
}
