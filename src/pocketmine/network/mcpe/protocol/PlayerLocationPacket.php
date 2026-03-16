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

namespace pocketmine\network\mcpe\protocol;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\PlayerLocationType;

class PlayerLocationPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::PLAYER_LOCATION_PACKET;

	private PlayerLocationType $type;
	private int $actorUniqueId;
	private ?Vector3 $position;

	/**
	 * @generate-create-func
	 */
	private static function create(PlayerLocationType $type, int $actorUniqueId, ?Vector3 $position) : self
	{
		$result = new self();
		$result->type = $type;
		$result->actorUniqueId = $actorUniqueId;
		$result->position = $position;
		return $result;
	}

	public static function createCoordinates(int $actorUniqueId, Vector3 $position) : self
	{
		return self::create(PlayerLocationType::PLAYER_LOCATION_COORDINATES, $actorUniqueId, $position);
	}

	public static function createHide(int $actorUniqueId) : self
	{
		return self::create(PlayerLocationType::PLAYER_LOCATION_HIDE, $actorUniqueId, null);
	}

	public function getType() : PlayerLocationType
	{
		return $this->type;
	}

	public function getActorUniqueId() : int
	{
		return $this->actorUniqueId;
	}

	public function getPosition() : ?Vector3
	{
		return $this->position;
	}

	protected function decodePayload() : void
	{
		$this->type = PlayerLocationType::fromPacket($this->getLInt());
		$this->actorUniqueId = $this->getEntityUniqueId();

		if ($this->type === PlayerLocationType::PLAYER_LOCATION_COORDINATES) {
			$this->position = $this->getVector3();
		}
	}

	protected function encodePayload() : void
	{
		$this->putLInt($this->type->value);
		$this->putEntityUniqueId($this->actorUniqueId);

		if ($this->type === PlayerLocationType::PLAYER_LOCATION_COORDINATES) {
			if ($this->position === null) { // this should never be the case
				throw new \LogicException("PlayerLocationPacket with type PLAYER_LOCATION_COORDINATES require a position to be provided");
			}
			$this->putVector3($this->position);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handlePlayerLocation($this);
	}
}
