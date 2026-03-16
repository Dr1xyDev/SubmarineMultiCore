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

use InvalidArgumentException;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\network\mcpe\protocol\types\MapDecoration;
use pocketmine\network\mcpe\protocol\types\MapTrackedObject;
use pocketmine\utils\Color;

use function count;

class ClientboundMapItemDataPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_MAP_ITEM_DATA_PACKET;

	public const BITFLAG_TEXTURE_UPDATE = 0x02;
	public const BITFLAG_DECORATION_UPDATE = 0x04;

	/** @var int */
	public $mapId;
	/** @var int */
	public $type;
	/** @var int */
	public $dimensionId = DimensionIds::OVERWORLD;
	/** @var bool */
	public $isLocked = false;

	/** @var int */
	public $originX;
	/** @var int */
	public $originY;
	/** @var int */
	public $originZ;

	/** @var int[] */
	public $eids = [];
	/** @var int */
	public $scale;

	/** @var MapTrackedObject[] */
	public $trackedEntities = [];
	/** @var MapDecoration[] */
	public $decorations = [];

	/** @var int */
	public $width;
	/** @var int */
	public $height;
	/** @var int */
	public $xOffset = 0;
	/** @var int */
	public $yOffset = 0;
	/** @var Color[][] */
	public $colors = [];

	protected function decodePayload() : void
	{
		$this->mapId = $this->getEntityUniqueId();
		$this->type = $this->getUnsignedVarInt();
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_137) {
			$this->dimensionId = $this->getByte();
		}
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_354) {
			$this->isLocked = $this->getBool();
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_544) {
				$this->getSignedBlockPosition($this->originX, $this->originY, $this->originZ);
			}
		}

		if (($this->type & 0x08) !== 0) {
			$count = $this->getUnsignedVarInt();
			for ($i = 0; $i < $count; ++$i) {
				$this->eids[] = $this->getEntityUniqueId();
			}
		}

		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_141) {
			$flags = (0x08 | self::BITFLAG_DECORATION_UPDATE | self::BITFLAG_TEXTURE_UPDATE);
		} else {
			$flags = (self::BITFLAG_DECORATION_UPDATE | self::BITFLAG_TEXTURE_UPDATE);
		}
		if (($this->type & ($flags)) !== 0) { //Decoration bitflag or colour bitflag
			$this->scale = $this->getByte();
		}

		if (($this->type & self::BITFLAG_DECORATION_UPDATE) !== 0) {
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_137) {
				for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
					$object = new MapTrackedObject();
					$object->type = $this->getLInt();
					if ($object->type === MapTrackedObject::TYPE_BLOCK) {
						$this->getBlockPosition($object->x, $object->y, $object->z);
					} elseif ($object->type === MapTrackedObject::TYPE_ENTITY) {
						$object->entityUniqueId = $this->getEntityUniqueId();
					} else {
						throw new PacketDecodeException("Unknown map object type $object->type");
					}
					$this->trackedEntities[] = $object;
				}
			}

			for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
				if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_137) {
					$icon = $this->getByte();
					$rotation = $this->getByte();
				} else {
					$weird = $this->getVarInt();
					$rotation = $weird & 0x0f;
					$icon = $weird >> 4;
				}
				$xOffset = $this->getByte();
				$yOffset = $this->getByte();
				$label = $this->getString();
				if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_137) {
					$color = Color::fromABGR($this->getUnsignedVarInt());
				} else {
					$color = Color::fromARGB($this->getLInt()); //already BE, don't need to reverse it again
				}
				$this->decorations[] = new MapDecoration($icon, $rotation, $xOffset, $yOffset, $label, $color);
			}
		}

		if (($this->type & self::BITFLAG_TEXTURE_UPDATE) !== 0) {
			$this->width = $this->getVarInt();
			$this->height = $this->getVarInt();
			$this->xOffset = $this->getVarInt();
			$this->yOffset = $this->getVarInt();

			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_137) {
				$count = $this->getUnsignedVarInt();
				if ($count !== $this->width * $this->height) {
					throw new PacketDecodeException("Expected colour count of " . ($this->height * $this->width) . " (height $this->height * width $this->width), got $count");
				}
			}

			for ($y = 0; $y < $this->height; ++$y) {
				for ($x = 0; $x < $this->width; ++$x) {
					$this->colors[$y][$x] = Color::fromABGR($this->getUnsignedVarInt());
				}
			}
		}
	}

	protected function encodePayload() : void
	{
		$this->putEntityUniqueId($this->mapId);

		$type = 0;
		if (($eidsCount = count($this->eids)) > 0) {
			$type |= 0x08;
		}
		if (($decorationCount = count($this->decorations)) > 0) {
			$type |= self::BITFLAG_DECORATION_UPDATE;
		}
		if (count($this->colors) > 0) {
			$type |= self::BITFLAG_TEXTURE_UPDATE;
		}

		$this->putUnsignedVarInt($type);
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_137) {
			$this->putByte($this->dimensionId);
		}
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_354) {
			$this->putBool($this->isLocked);
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_544) {
				$this->putSignedBlockPosition($this->originX, $this->originY, $this->originZ);
			}
		}

		if (($type & 0x08) !== 0) { //TODO: find out what these are for
			$this->putUnsignedVarInt($eidsCount);
			foreach ($this->eids as $eid) {
				$this->putEntityUniqueId($eid);
			}
		}

		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_141) {
			$flags = (0x08 | self::BITFLAG_TEXTURE_UPDATE | self::BITFLAG_DECORATION_UPDATE);
		} else {
			$flags = (self::BITFLAG_TEXTURE_UPDATE | self::BITFLAG_DECORATION_UPDATE);
		}
		if (($type & ($flags)) !== 0) {
			$this->putByte($this->scale);
		}

		if (($type & self::BITFLAG_DECORATION_UPDATE) !== 0) {
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_137) {
				$this->putUnsignedVarInt(count($this->trackedEntities));
				foreach ($this->trackedEntities as $object) {
					$this->putLInt($object->type);
					if ($object->type === MapTrackedObject::TYPE_BLOCK) {
						$this->putBlockPosition($object->x, $object->y, $object->z);
					} elseif ($object->type === MapTrackedObject::TYPE_ENTITY) {
						$this->putEntityUniqueId($object->entityUniqueId);
					} else {
						throw new InvalidArgumentException("Unknown map object type $object->type");
					}
				}
			}

			$this->putUnsignedVarInt($decorationCount);
			foreach ($this->decorations as $decoration) {
				if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_137) {
					$this->putByte($decoration->getIcon());
					$this->putByte($decoration->getRotation());
				} else {
					$this->putVarInt(($decoration->getRotation() & 0x0f) | ($decoration->getIcon() << 4));
				}
				$this->putByte($decoration->getXOffset());
				$this->putByte($decoration->getYOffset());
				$this->putString($decoration->getLabel());
				if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_137) {
					$this->putUnsignedVarInt($decoration->getColor()->toABGR());
				} else {
					$this->putLInt($decoration->getColor()->toARGB());
				}
			}
		}

		if (($type & self::BITFLAG_TEXTURE_UPDATE) !== 0) {
			$this->putVarInt($this->width);
			$this->putVarInt($this->height);
			$this->putVarInt($this->xOffset);
			$this->putVarInt($this->yOffset);

			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_137) {
				$this->putUnsignedVarInt($this->width * $this->height); //list count, but we handle it as a 2D array... thanks for the confusion mojang
			}

			for ($y = 0; $y < $this->height; ++$y) {
				for ($x = 0; $x < $this->width; ++$x) {
					$this->putUnsignedVarInt($this->colors[$y][$x]->toABGR());
				}
			}
		}
	}

	/**
	 * Crops the texture to wanted size
	 */
	public function cropTexture(int $minX, int $minY, int $maxX, int $maxY) : void
	{
		$this->height = $maxY;
		$this->width = $maxX;
		$this->xOffset = $minX;
		$this->yOffset = $minY;
		$newColors = [];
		for ($y = 0; $y < $maxY; $y++) {
			for ($x = 0; $x < $maxX; $x++) {
				$newColors[$y][$x] = $this->colors[$minY + $y][$minX + $x];
			}
		}
		$this->colors = $newColors;
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleClientboundMapItemData($this);
	}
}
