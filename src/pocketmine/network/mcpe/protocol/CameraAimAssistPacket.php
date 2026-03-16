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

use pocketmine\math\Vector2;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\camera\CameraAimAssistActionType;
use pocketmine\network\mcpe\protocol\types\camera\CameraAimAssistTargetMode;

class CameraAimAssistPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CAMERA_AIM_ASSIST_PACKET;

	private string $presetId;
	private Vector2 $viewAngle;
	private float $distance;
	private CameraAimAssistTargetMode $targetMode;
	private CameraAimAssistActionType $actionType;
	private bool $showDebugRender;

	/**
	 * @generate-create-func
	 */
	public static function create(string $presetId, Vector2 $viewAngle, float $distance, CameraAimAssistTargetMode $targetMode, CameraAimAssistActionType $actionType, bool $showDebugRender) : self
	{
		$result = new self();
		$result->presetId = $presetId;
		$result->viewAngle = $viewAngle;
		$result->distance = $distance;
		$result->targetMode = $targetMode;
		$result->actionType = $actionType;
		$result->showDebugRender = $showDebugRender;
		return $result;
	}

	public function getPresetId() : string
	{
		return $this->presetId;
	}
	public function getViewAngle() : Vector2
	{
		return $this->viewAngle;
	}
	public function getDistance() : float
	{
		return $this->distance;
	}
	public function getTargetMode() : CameraAimAssistTargetMode
	{
		return $this->targetMode;
	}
	public function getActionType() : CameraAimAssistActionType
	{
		return $this->actionType;
	}

	public function getShowDebugRender() : bool
	{
		return $this->showDebugRender;
	}

	protected function decodePayload() : void
	{
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_766) {
			$this->presetId = $this->getString();
		}

		$this->viewAngle = $this->getVector2();
		$this->distance = $this->getLFloat();
		$this->targetMode = CameraAimAssistTargetMode::fromPacket($this->getByte());
		$this->actionType = CameraAimAssistActionType::fromPacket($this->getByte());
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_827) {
			$this->showDebugRender = $this->getBool();
		}
	}

	protected function encodePayload() : void
	{
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_766) {
			$this->putString($this->presetId);
		}

		$this->putVector2($this->viewAngle);
		$this->putLFloat($this->distance);
		$this->putByte($this->targetMode->value);
		$this->putByte($this->actionType->value);
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_827) {
			$this->putBool($this->showDebugRender);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleCameraAimAssist($this);
	}
}
