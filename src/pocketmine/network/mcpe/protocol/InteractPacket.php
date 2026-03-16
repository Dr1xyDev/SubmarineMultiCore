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

use pocketmine\network\mcpe\NetworkSession;

class InteractPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::INTERACT_PACKET;

	public const ACTION_RIGHT_CLICK = 1;
	public const ACTION_LEFT_CLICK = 2;
	public const ACTION_LEAVE_VEHICLE = 3;
	public const ACTION_MOUSEOVER = 4;

	public const ACTION_OPEN_INVENTORY = 6;

	/** @var int */
	public $action;
	/** @var int */
	public $target;

	/** @var float */
	public $x;
	/** @var float */
	public $y;
	/** @var float */
	public $z;

	protected function decodePayload() : void
	{
		$this->action = $this->getByte();
		$this->target = $this->getEntityRuntimeId();

		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_137) {
			if ($this->action === self::ACTION_MOUSEOVER || ($this->getProtocol() >= ProtocolInfo::PROTOCOL_486 && $this->action === self::ACTION_LEAVE_VEHICLE)) {
				//TODO: should this be a vector3?
				$this->x = $this->getLFloat();
				$this->y = $this->getLFloat();
				$this->z = $this->getLFloat();
			}
		}
	}

	protected function encodePayload() : void
	{
		$this->putByte($this->action);
		$this->putEntityRuntimeId($this->target);

		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_137) {
			if ($this->action === self::ACTION_MOUSEOVER || ($this->getProtocol() >= ProtocolInfo::PROTOCOL_486 && $this->action === self::ACTION_LEAVE_VEHICLE)) {
				$this->putLFloat($this->x);
				$this->putLFloat($this->y);
				$this->putLFloat($this->z);
			}
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleInteract($this);
	}
}
