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
use pocketmine\network\mcpe\protocol\types\camera\CameraAimAssistCategories;
use pocketmine\network\mcpe\protocol\types\camera\CameraAimAssistCategory;
use pocketmine\network\mcpe\protocol\types\camera\CameraAimAssistPreset;

use function count;

class CameraAimAssistPresetsPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CAMERA_AIM_ASSIST_PRESETS_PACKET;

	/** @var CameraAimAssistCategory[]|CameraAimAssistCategories[] */
	private array $categories;
	/** @var CameraAimAssistPreset[] */
	private array $presets;
	private int $operation;

	/**
	 * @generate-create-func
	 * @param CameraAimAssistCategory[]|CameraAimAssistCategories[] $categories
	 * @param CameraAimAssistPreset[]                               $presets
	 */
	public static function create(array $categories, array $presets, int $operation) : self
	{
		$result = new self();
		$result->categories = $categories;
		$result->presets = $presets;
		$result->operation = $operation;
		return $result;
	}

	/**
	 * @return CameraAimAssistCategory[]|CameraAimAssistCategories[]
	 */
	public function getCategories() : array
	{
		return $this->categories;
	}

	/**
	 * @return CameraAimAssistPreset[]
	 */
	public function getPresets() : array
	{
		return $this->presets;
	}

	public function getOperation() : int
	{
		return $this->operation;
	}

	protected function decodePayload() : void
	{
		$this->categories = [];
		for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_800) {
				$this->categories[] = CameraAimAssistCategories::read($this);
			} else {
				$this->categories[] = CameraAimAssistCategory::read($this);
			}
		}
		$this->presets = [];
		for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
			$this->presets[] = CameraAimAssistPreset::read($this, $this->getProtocol());
		}

		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_776) {
			$this->operation = $this->getByte();
		}
	}

	protected function encodePayload() : void
	{
		$this->putUnsignedVarInt(count($this->categories));
		foreach ($this->categories as $category) {
			$category->write($this);
		}
		$this->putUnsignedVarInt(count($this->presets));
		foreach ($this->presets as $preset) {
			$preset->write($this, $this->getProtocol());
		}

		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_776) {
			$this->putByte($this->operation);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleCameraAimAssistPresets($this);
	}
}
