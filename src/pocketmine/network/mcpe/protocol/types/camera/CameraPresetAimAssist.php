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

namespace pocketmine\network\mcpe\protocol\types\camera;

use pocketmine\math\Vector2;
use pocketmine\network\mcpe\NetworkBinaryStream;

final class CameraPresetAimAssist
{
	public function __construct(
		private ?string $presetId,
		private ?CameraAimAssistTargetMode $targetMode,
		private ?Vector2 $viewAngle,
		private ?float $distance,
	) {
	}

	public function getPresetId() : ?string
	{
		return $this->presetId;
	}

	public function getTargetMode() : ?CameraAimAssistTargetMode
	{
		return $this->targetMode;
	}

	public function getViewAngle() : ?Vector2
	{
		return $this->viewAngle;
	}

	public function getDistance() : ?float
	{
		return $this->distance;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$presetId = $in->readOptional($in->getString(...));
		$targetMode = $in->readOptional(fn () => CameraAimAssistTargetMode::fromPacket($in->getByte()));
		$viewAngle = $in->readOptional($in->getVector2(...));
		$distance = $in->readOptional($in->getLFloat(...));

		return new self(
			$presetId,
			$targetMode,
			$viewAngle,
			$distance
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->writeOptional($this->presetId, $out->putString(...));
		$out->writeOptional($this->targetMode, fn (CameraAimAssistTargetMode $v) => $out->putByte($v->value));
		$out->writeOptional($this->viewAngle, $out->putVector2(...));
		$out->writeOptional($this->distance, $out->putLFloat(...));
	}
}
