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

namespace pocketmine\level\sound;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;

final class BlastFurnaceSound extends Sound
{
	public function __construct(Vector3 $pos)
	{
		parent::__construct($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
	}

	public function encode()
	{
		if ($this->protocol <= ProtocolInfo::PROTOCOL_332) {
			return [LevelSoundEventPacket::nonActorSound(LevelSoundEventPacket::SOUND_BLOCK_FURNACE_LIT, $this, false)];
		} else {
			return [LevelSoundEventPacket::nonActorSound(LevelSoundEventPacket::SOUND_BLOCK_BLASTFURNACE_FIRE_CRACKLE, $this, false)];
		}
	}

	public function isUseProtocol() : bool{
		return true;
	}
}
