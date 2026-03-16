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

use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\camera\CameraFadeInstruction;
use pocketmine\network\mcpe\protocol\types\camera\CameraFovInstruction;
use pocketmine\network\mcpe\protocol\types\camera\CameraSetInstruction;
use pocketmine\network\mcpe\protocol\types\camera\CameraSplineInstruction;
use pocketmine\network\mcpe\protocol\types\camera\CameraTargetInstruction;

class CameraInstructionPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CAMERA_INSTRUCTION_PACKET;

	public ?CameraSetInstruction $set = null;
	public ?bool $clear;
	public ?CameraFadeInstruction $fade = null;
	public ?CameraTargetInstruction $target;
	public ?bool $removeTarget;
	public ?CameraFovInstruction $fieldOfView;
	private ?CameraSplineInstruction $spline;
	private ?int $attachToEntity;
	private ?bool $detachFromEntity;

	/** @phpstan-var CompoundTag */
	public CompoundTag $data; //old

	protected function decodePayload() : void
	{
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_618) {
			$this->set = $this->readOptional(fn () => CameraSetInstruction::read($this, $this->getProtocol()));
			$this->clear = $this->readOptional($this->getBool(...));
			$this->fade = $this->readOptional(fn () => CameraFadeInstruction::read($this));
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_729) {
				$this->target = $this->readOptional(fn () => CameraTargetInstruction::read($this));
				$this->removeTarget = $this->readOptional($this->getBool(...));
				if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_827) {
					$this->fieldOfView = $this->readOptional(fn () => CameraFovInstruction::read($this));
					if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_859) {
						$this->spline = $this->readOptional(CameraSplineInstruction::read(...));
						$this->attachToEntity = $this->readOptional($this->getLLong(...)); //WHY IS THIS NON-STANDARD?
						$this->detachFromEntity = $this->readOptional($this->getBool(...));
					}
				}
			}
		} else {
			$this->data = $this->getNbtCompoundRoot();
		}
	}

	protected function encodePayload() : void
	{
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_618) {
			$this->writeOptional($this->set, fn (CameraSetInstruction $v) => $v->write($this, $this->getProtocol()));
			$this->writeOptional($this->clear, $this->putBool(...));
			$this->writeOptional($this->fade, fn (CameraFadeInstruction $v) => $v->write($this));
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_729) {
				$this->writeOptional($this->target, fn (CameraTargetInstruction $v) => $v->write($this));
				$this->writeOptional($this->removeTarget, $this->putBool(...));
				if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_827) {
					$this->writeOptional($this->fieldOfView, fn (CameraFovInstruction $v) => $v->write($this));
					if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_859) {
						$this->writeOptional($this->spline, fn(NetworkBinaryStream $out, CameraSplineInstruction $v) => $v->write($out));
						$this->writeOptional($this->attachToEntity, $this->putLLong(...)); //WHY IS THIS NON-STANDARD?
						$this->writeOptional($this->detachFromEntity, $this->putBool(...));
					}
				}
			}
		} else {
			$this->put((new NetworkLittleEndianNBTStream())->write($this->data));
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleCameraInstruction($this);
	}
}
