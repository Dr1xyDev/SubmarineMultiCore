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

namespace pocketmine\network\mcpe\protocol\types;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\DebugDrawerPacket;
use pocketmine\utils\Color;

/**
 * @see DebugDrawerPacket
 */
final class PacketShapeData
{
	public function __construct(
		private int $networkId,
		private ?ScriptDebugShapeType $type,
		private ?Vector3 $location,
		private ?float $scale,
		private ?Vector3 $rotation,
		private ?float $totalTimeLeft,
		private ?Color $color,
		private ?string $text,
		private ?Vector3 $boxBound,
		private ?Vector3 $lineEndLocation,
		private ?float $arrowHeadLength,
		private ?float $arrowHeadRadius,
		private ?int $segments,
	) {
	}

	public function getNetworkId() : int
	{
		return $this->networkId;
	}

	public function getType() : ?ScriptDebugShapeType
	{
		return $this->type;
	}

	public function getLocation() : ?Vector3
	{
		return $this->location;
	}

	public function getScale() : ?float
	{
		return $this->scale;
	}

	public function getRotation() : ?Vector3
	{
		return $this->rotation;
	}

	public function getTotalTimeLeft() : ?float
	{
		return $this->totalTimeLeft;
	}

	public function getColor() : ?Color
	{
		return $this->color;
	}

	public function getText() : ?string
	{
		return $this->text;
	}

	public function getBoxBound() : ?Vector3
	{
		return $this->boxBound;
	}

	public function getLineEndLocation() : ?Vector3
	{
		return $this->lineEndLocation;
	}

	public function getArrowHeadLength() : ?float
	{
		return $this->arrowHeadLength;
	}

	public function getArrowHeadRadius() : ?float
	{
		return $this->arrowHeadRadius;
	}

	public function getSegments() : ?int
	{
		return $this->segments;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$networkId = $in->getUnsignedVarLong();
		$type = $in->readOptional(fn () => ScriptDebugShapeType::fromPacket($in->getByte()));
		$location = $in->readOptional($in->getVector3(...));
		$scale = $in->readOptional($in->getLFloat(...));
		$rotation = $in->readOptional($in->getVector3(...));
		$totalTimeLeft = $in->readOptional($in->getLFloat(...));
		$color = $in->readOptional(fn () => Color::fromARGB($in->getLInt()));
		$text = $in->readOptional($in->getString(...));
		$boxBound = $in->readOptional($in->getVector3(...));
		$lineEndLocation = $in->readOptional($in->getVector3(...));
		$arrowHeadLength = $in->readOptional($in->getLFloat(...));
		$arrowHeadRadius = $in->readOptional($in->getLFloat(...));
		$segments = $in->readOptional($in->getByte(...));

		return new self(
			$networkId,
			$type,
			$location,
			$scale,
			$rotation,
			$totalTimeLeft,
			$color,
			$text,
			$boxBound,
			$lineEndLocation,
			$arrowHeadLength,
			$arrowHeadRadius,
			$segments
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putUnsignedVarLong($this->networkId);
		$out->writeOptional($this->type, fn (ScriptDebugShapeType $type) => $out->putByte($type->value));
		$out->writeOptional($this->location, $out->putVector3(...));
		$out->writeOptional($this->scale, $out->putLFloat(...));
		$out->writeOptional($this->rotation, $out->putVector3(...));
		$out->writeOptional($this->totalTimeLeft, $out->putLFloat(...));
		$out->writeOptional($this->color, fn (Color $color) => $out->putLInt($color->toARGB()));
		$out->writeOptional($this->text, $out->putString(...));
		$out->writeOptional($this->boxBound, $out->putVector3(...));
		$out->writeOptional($this->lineEndLocation, $out->putVector3(...));
		$out->writeOptional($this->arrowHeadLength, $out->putLFloat(...));
		$out->writeOptional($this->arrowHeadRadius, $out->putLFloat(...));
		$out->writeOptional($this->segments, $out->putByte(...));
	}
}
