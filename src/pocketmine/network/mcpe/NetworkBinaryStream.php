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

namespace pocketmine\network\mcpe;

use pocketmine\entity\Attribute;
use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\nbt\LittleEndianNBTStream;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\NamedTag;
use pocketmine\network\mcpe\convert\EntityMetadataTranslator;
use pocketmine\network\mcpe\convert\ItemTranslator;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\PacketDecodeException;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\command\CommandOriginData;
use pocketmine\network\mcpe\protocol\types\entity\AttributeModifier;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\network\mcpe\protocol\types\GameRuleType;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\recipe\ComplexAliasItemDescriptor;
use pocketmine\network\mcpe\protocol\types\recipe\IntIdMetaItemDescriptor;
use pocketmine\network\mcpe\protocol\types\recipe\ItemDescriptorType;
use pocketmine\network\mcpe\protocol\types\recipe\MolangItemDescriptor;
use pocketmine\network\mcpe\protocol\types\recipe\RecipeIngredient;
use pocketmine\network\mcpe\protocol\types\recipe\StringIdMetaItemDescriptor;
use pocketmine\network\mcpe\protocol\types\recipe\TagItemDescriptor;
use pocketmine\network\mcpe\protocol\types\skin\PersonaPieceTintColor;
use pocketmine\network\mcpe\protocol\types\skin\PersonaSkinPiece;
use pocketmine\network\mcpe\protocol\types\skin\SerializedSkin;
use pocketmine\network\mcpe\protocol\types\skin\SkinAnimation;
use pocketmine\network\mcpe\protocol\types\skin\SkinImage;
use pocketmine\network\mcpe\protocol\types\StructureEditorData;
use pocketmine\network\mcpe\protocol\types\StructureSettings;
use pocketmine\utils\BinaryDataException;
use pocketmine\utils\BinaryStream;
use pocketmine\utils\Color;
use pocketmine\utils\UUID;
use SplFixedArray;
use UnexpectedValueException;

use function assert;
use function chr;
use function count;
use function json_decode;
use function json_encode;
use function ord;
use function strlen;

class NetworkBinaryStream extends BinaryStream
{

	/** @var int[] */
	public static array $shieldItemRuntimeIds = [];

	public function getString() : string
	{
		return $this->get($this->getUnsignedVarInt());
	}

	public function putString(string $v) : void
	{
		$this->putUnsignedVarInt(strlen($v));
		$this->put($v);
	}

	public function getUUID() : UUID
	{
		//This is actually two little-endian longs: UUID Most followed by UUID Least
		$part1 = $this->getLInt();
		$part0 = $this->getLInt();
		$part3 = $this->getLInt();
		$part2 = $this->getLInt();

		return new UUID($part0, $part1, $part2, $part3);
	}

	public function putUUID(UUID $uuid) : void
	{
		$this->putLInt($uuid->getPart(1));
		$this->putLInt($uuid->getPart(0));
		$this->putLInt($uuid->getPart(3));
		$this->putLInt($uuid->getPart(2));
	}

	public function getSkin(int $playerProtocol) : Skin
	{
		$skinId = $this->getString();
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_428) {
			$skinPlayFabId = $this->getString();
		}
		$skinResourcePatch = $this->getString();
		$skinData = $this->getSkinImage();
		$animationCount = $this->getLInt();
		if ($animationCount > 128) {
			throw new UnexpectedValueException("Too many skin animations: $animationCount");
		}
		$animations = [];
		for ($i = 0; $i < $animationCount; ++$i) {
			$animations[] = new SkinAnimation(
				$this->getSkinImage(),
				$this->getLInt(),
				$this->getLFloat(),
				($playerProtocol >= ProtocolInfo::PROTOCOL_419 ? $this->getLInt() : 0)
			);
		}
		$capeData = $this->getSkinImage();
		$geometryData = $this->getString();
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_465) {
			$geometryDataVersion = $this->getString();
		}
		$animationData = $this->getString();
		if ($playerProtocol < ProtocolInfo::PROTOCOL_465) {
			$premium = $this->getBool();
			$persona = $this->getBool();
			$capeOnClassic = $this->getBool();
		}
		$capeId = $this->getString();
		$fullSkinId = $this->getString();

		if ($playerProtocol === ProtocolInfo::PROTOCOL_390 || $playerProtocol >= ProtocolInfo::PROTOCOL_407) {
			$armSize = $this->getString();
			$skinColor = Color::fromHexString($this->getString());
			$personaPieceCount = $this->getLInt();
			if ($personaPieceCount > 128) {
				throw new UnexpectedValueException("Too many persona pieces: $personaPieceCount");
			}
			$personaPieces = [];
			for ($i = 0; $i < $personaPieceCount; ++$i) {
				$personaPieces[] = new PersonaSkinPiece(
					$this->getString(),
					$this->getString(),
					$this->getString(),
					$this->getBool(),
					$this->getString()
				);
			}
			$pieceTintColorCount = $this->getLInt();
			if ($pieceTintColorCount > 128) {
				throw new UnexpectedValueException("Too many piece tint colors: $pieceTintColorCount");
			}
			$pieceTintColors = [];
			for ($i = 0; $i < $pieceTintColorCount; ++$i) {
				$pieceType = $this->getString();
				$colorCount = $this->getLInt();
				$colors = [];
				for ($j = 0; $j < $colorCount; ++$j) {
					$colors[] = $this->getString();
				}
				$pieceTintColors[] = new PersonaPieceTintColor(
					$pieceType,
					$colors
				);
			}
			$personaPieces = SplFixedArray::fromArray($personaPieces);
			$pieceTintColors = SplFixedArray::fromArray($pieceTintColors);
			if ($playerProtocol >= ProtocolInfo::PROTOCOL_465) {
				$premium = $this->getBool();
				$persona = $this->getBool();
				$capeOnClassic = $this->getBool();
				$isPrimaryUser = $this->getBool();
				if ($playerProtocol >= ProtocolInfo::PROTOCOL_568) {
					$override = $this->getBool();
				}
			}
		} else {
			$armSize = "wide";
			$skinColor = new Color(0, 0, 0);
			$personaPieces = SplFixedArray::fromArray([]);
			$pieceTintColors = SplFixedArray::fromArray([]);
		}

		return (new SerializedSkin($skinId, $skinPlayFabId ?? "", $skinData, $capeId, $capeData, $skinResourcePatch, $geometryData, $geometryDataVersion ?? "", $animationData, $animations, $premium ?? false, $persona ?? false, $capeOnClassic ?? false, $fullSkinId, $armSize, $skinColor, $personaPieces, $pieceTintColors, $isPrimaryUser ?? true, $override ?? true))->toSkin();
	}

	public function putSkin(Skin $skin, int $playerProtocol) : void
	{
		$skin = $skin->getSerializedSkin();
		$this->putString($skin->getSkinId());
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_428) {
			$this->putString($skin->getPlayFabId());
		}
		$this->putString($skin->getResourcePatch());
		$this->putSkinImage($skin->getSkinImage());
		$this->putLInt(count($skin->getAnimationFrames()));
		foreach ($skin->getAnimationFrames() as $animation) {
			$this->putSkinImage($animation->getImage());
			$this->putLInt($animation->getType());
			$this->putLFloat($animation->getFrames());
			if ($playerProtocol >= ProtocolInfo::PROTOCOL_419) {
				$this->putLInt($animation->getExpressionType());
			}
		}
		$this->putSkinImage($skin->getCapeImage());
		$this->putString($skin->getGeometryData());
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_465) {
			$this->putString($skin->getGeometryDataEngineVersion());
		}
		$this->putString($skin->getAnimationData());
		if ($playerProtocol < ProtocolInfo::PROTOCOL_465) {
			$this->putBool($skin->isPremiumSkin());
			$this->putBool($skin->isPersonaSkin());
			$this->putBool($skin->isCapeOnClassicSkin());
		}
		$this->putString($skin->getCapeId());
		$this->putString($skin->getFullSkinId());
		if ($playerProtocol === ProtocolInfo::PROTOCOL_390 || $playerProtocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putString($skin->getArmSize());
			$this->putString($skin->getSkinColor()->toHexString());
			$this->putLInt(count($skin->getPersonaPieces()));
			foreach ($skin->getPersonaPieces() as $piece) {
				$this->putString($piece->getPieceId());
				$this->putString($piece->getPieceType());
				$this->putString($piece->getPackId());
				$this->putBool($piece->isDefaultPiece());
				$this->putString($piece->getProductId());
			}
			$this->putLInt(count($skin->getPieceTintColors()));
			foreach ($skin->getPieceTintColors() as $tint) {
				$this->putString($tint->getPieceType());
				$this->putLInt(count($tint->getColors()));
				foreach ($tint->getColors() as $color) {
					$this->putString($color);
				}
			}
			if ($playerProtocol >= ProtocolInfo::PROTOCOL_465) {
				$this->putBool($skin->isPremiumSkin());
				$this->putBool($skin->isPersonaSkin());
				$this->putBool($skin->isCapeOnClassicSkin());
				$this->putBool($skin->isPrimaryUser());
				if ($playerProtocol >= ProtocolInfo::PROTOCOL_568) {
					$this->putBool($skin->isOverride());
				}
			}
		}
	}

	private function getSkinImage() : SkinImage
	{
		$width = $this->getLInt();
		$height = $this->getLInt();
		$data = $this->getString();
		return new SkinImage($height, $width, $data);
	}

	private function putSkinImage(SkinImage $image) : void
	{
		$this->putLInt($image->getWidth());
		$this->putLInt($image->getHeight());
		$this->putString($image->getData());
	}

	/**
	 * @return int[]
	 * @phpstan-return array{0: int, 1: int, 2: int}
	 * @throws BinaryDataException
	 */
	private function getItemStackHeader(int $protocol) : array{
		$id = $this->getVarInt();
		if($id === 0){
			return [0, 0, 0];
		}

		if ($protocol >= ProtocolInfo::PROTOCOL_431) {
			$count = $this->getLShort();
			$meta = $this->getUnsignedVarInt();
		} else {
			$auxValue = $this->getVarInt();
			$meta = $auxValue >> 8;
			if ($protocol < ProtocolInfo::PROTOCOL_137 && $meta === 0x7fff) {
				$meta = -1;
			}
			$count = $auxValue & 0xff;
		}

		return [$id, $count, $meta];
	}

	private function putItemStackHeader(ItemStack $itemStack, int $protocol) : bool{
		if($itemStack->getId() === 0){
			$this->putVarInt(0);
			return false;
		}

		$this->putVarInt($itemStack->getId());
		if ($protocol >= ProtocolInfo::PROTOCOL_431) {
			$this->putLShort($itemStack->getCount());
			$this->putUnsignedVarInt($itemStack->getMeta());
		} else {
			$auxValue = (($itemStack->getMeta() & 0x7fff) << 8) | $itemStack->getCount();
			$this->putVarInt($auxValue);
		}

		return true;
	}

	private function getItemStackFooter(int $id, int $meta, int $count, int $protocol) : ItemStack{
		if ($protocol >= ProtocolInfo::PROTOCOL_431) {
			$blockRuntimeId = $this->getVarInt();
			$binaryExtraData = new NetworkBinaryStream($this->getString());
		} else {
			$binaryExtraData = $this;
			$blockRuntimeId = 0;
		}

		[$compound, $canPlaceOn, $canDestroy, $shieldBlockingTick] = $this->getItemStackExtraData($id, $binaryExtraData, $protocol);
		return new ItemStack($id, $meta, $count, $blockRuntimeId, $compound, $canPlaceOn, $canDestroy, $shieldBlockingTick);
	}

	private function putItemStackFooter(ItemStack $itemStack, int $protocol) : void{
		if ($protocol >= ProtocolInfo::PROTOCOL_431) {
			$this->putVarInt($itemStack->getBlockRuntimeId());
			$this->putItemStackExtraData($itemStack, ($extraData = new NetworkBinaryStream()), $protocol);
			$this->putString($extraData->getBuffer());
		} else {
			$this->putItemStackExtraData($itemStack, $this, $protocol);
		}
	}

	public function getItemStackExtraData(int $id, NetworkBinaryStream $extraData, int $protocol) : array{
		$nbtLen = $extraData->getLShort();

		/** @var CompoundTag|null $compound */
		$compound = null;
		if ($protocol >= ProtocolInfo::PROTOCOL_332) {
			if ($nbtLen === 0xffff) {
				$nbtDataVersion = $extraData->getByte();
				if ($nbtDataVersion !== 1) {
					throw new PacketDecodeException("Unexpected NBT data version $nbtDataVersion");
				}

				if ($protocol >= ProtocolInfo::PROTOCOL_431) {
					$decodedNBT = (new LittleEndianNBTStream())->read($extraData->buffer, false, $extraData->offset, 512);
				} else {
					$decodedNBT = (new NetworkLittleEndianNBTStream())->read($extraData->buffer, false, $extraData->offset, 512);
				}

				if (!($decodedNBT instanceof CompoundTag)) {
					throw new PacketDecodeException("Unexpected root tag type for itemstack");
				}
			} elseif ($nbtLen !== 0) {
				throw new PacketDecodeException("Unexpected fake NBT length $nbtLen");
			}
		} elseif ($nbtLen > 0) {
			$decodedNBT = (new LittleEndianNBTStream())->read($extraData->get($nbtLen));
			if (!($decodedNBT instanceof CompoundTag)) {
				throw new PacketDecodeException("Unexpected root tag type for itemstack");
			}

			$compound = $decodedNBT;
		}

		if ($protocol >= ProtocolInfo::PROTOCOL_431) {
			$canPlaceOn = [];
			for ($i = 0, $canPlaceOnCount = $extraData->getLInt(); $i < $canPlaceOnCount; ++$i) {
				$canPlaceOn[] = $extraData->get($extraData->getLShort());
			}

			$canDestroy = [];
			for ($i = 0, $canDestroyCount = $extraData->getLInt(); $i < $canDestroyCount; ++$i) {
				$canDestroy[] = $extraData->get($extraData->getLShort());
			}
		} else {
			$canPlaceOn = [];
			for ($i = 0, $canPlaceOnCount = $extraData->getVarInt(); $i < $canPlaceOnCount; ++$i) {
				$canPlaceOn[] = $extraData->getString();
			}

			$canDestroy = [];
			for ($i = 0, $canDestroyCount = $extraData->getVarInt(); $i < $canDestroyCount; ++$i) {
				$canDestroy[] = $extraData->getString();
			}
		}

		$shieldBlockingTick = null;
		if ($protocol >= ProtocolInfo::PROTOCOL_340) {
			if (!isset(self::$shieldItemRuntimeIds[$protocol])) {
				if ($protocol >= ProtocolInfo::PROTOCOL_419) {
					self::$shieldItemRuntimeIds[$protocol] = ItemTranslator::getInstance($protocol)->toNetworkId(ItemIds::SHIELD, 0)[0];
				} else {
					self::$shieldItemRuntimeIds[$protocol] = ItemIds::SHIELD;
				}
			}

			if ($id === self::$shieldItemRuntimeIds[$protocol]) {
				if ($protocol >= ProtocolInfo::PROTOCOL_431) {
					$shieldBlockingTick = $extraData->getLLong();
				} else {
					$shieldBlockingTick = $extraData->getVarLong();
				}
			}
		}

		if ($protocol >= ProtocolInfo::PROTOCOL_431) {
			if(!$extraData->feof()){
				throw new PacketDecodeException("Unexpected trailing extradata for network item $id");
			}
		}

		return [$compound, $canPlaceOn, $canDestroy, $shieldBlockingTick];
	}

	public function putItemStackExtraData(ItemStack $itemStack, NetworkBinaryStream $extraData, int $protocol) : void{
		$nbt = $itemStack->getNbt();

		if ($nbt !== null) {
			if ($protocol >= ProtocolInfo::PROTOCOL_332) {
				$extraData->putLShort(0xffff);
				$extraData->putByte(1); //TODO: some kind of count field? always 1 as of 1.9.0
				if ($protocol >= ProtocolInfo::PROTOCOL_431) {
					$extraData->put((new LittleEndianNBTStream())->write($nbt));
				} else {
					$extraData->put((new NetworkLittleEndianNBTStream())->write($nbt));
				}
			} else {
				$nbt = (new LittleEndianNBTStream())->write($nbt);
				$extraData->putLShort(strlen($nbt));
				$extraData->put($nbt);
			}
		} else {
			$extraData->putLShort(0);
		}

		if ($protocol >= ProtocolInfo::PROTOCOL_431) {
			$extraData->putLInt(count($itemStack->getCanPlaceOn()));
			foreach ($itemStack->getCanPlaceOn() as $entry) {
				$extraData->putLShort(strlen($entry));
				$extraData->put($entry);
			}
			$extraData->putLInt(count($itemStack->getCanDestroy()));
			foreach ($itemStack->getCanDestroy() as $entry) {
				$extraData->putLShort(strlen($entry));
				$extraData->put($entry);
			}
		} else {
			$extraData->putVarInt(count($itemStack->getCanPlaceOn()));
			foreach ($itemStack->getCanPlaceOn() as $entry) {
				$extraData->putString($entry);
			}
			$extraData->putVarInt(count($itemStack->getCanDestroy()));
			foreach ($itemStack->getCanDestroy() as $entry) {
				$extraData->putString($entry);
			}
		}

		if ($protocol >= ProtocolInfo::PROTOCOL_340) {
			if (!isset(self::$shieldItemRuntimeIds[$protocol])) {
				if ($protocol >= ProtocolInfo::PROTOCOL_419) {
					self::$shieldItemRuntimeIds[$protocol] = ItemTranslator::getInstance($protocol)->toNetworkId(ItemIds::SHIELD, 0)[0];
				} else {
					self::$shieldItemRuntimeIds[$protocol] = ItemIds::SHIELD;
				}
			}

			if ($itemStack->getId() === self::$shieldItemRuntimeIds[$protocol]) {
				$blockingTick = $itemStack->getShieldBlockingTick() ?? 0; //"blocking tick" (ffs mojang)
				if ($protocol >= ProtocolInfo::PROTOCOL_431) {
					$extraData->putLLong($blockingTick);
				} else {
					$extraData->putVarLong($blockingTick);
				}
			}
		}
	}

	/**
	 * @throws PacketDecodeException
	 * @throws BinaryDataException
	 */
	public function getItemStackWithoutStackId(int $protocol) : ItemStack{
		[$id, $count, $meta] = $this->getItemStackHeader($protocol);

		return $id !== 0 ? $this->getItemStackFooter($id, $meta, $count, $protocol) : ItemStack::null();
	}

	public function putItemStackWithoutStackId(Item|ItemStack $itemStack, int $protocol) : void{
		if ($itemStack instanceof Item) {
			$itemStack = TypeConverter::getInstance()->coreItemStackToNet($itemStack, $protocol);
		}

		if($this->putItemStackHeader($itemStack, $protocol)){
			$this->putItemStackFooter($itemStack, $protocol);
		}
	}

	public function getItemStackWrapper(int $protocol) : ItemStackWrapper{
		[$id, $count, $meta] = $this->getItemStackHeader($protocol);
		if($id === 0){
			return new ItemStackWrapper(0, ItemStack::null());
		}

		if ($protocol >= ProtocolInfo::PROTOCOL_431) {
			$hasNetId = $this->getBool();
			$stackId = $hasNetId ? $this->readServerItemStackId() : 0;
		}

		$itemStack = $this->getItemStackFooter($id, $meta, $count, $protocol);

		return new ItemStackWrapper($stackId ?? 1, $itemStack);
	}

	public function putItemStackWrapper(Item|ItemStackWrapper $itemStackWrapper, int $protocol) : void{
		if ($itemStackWrapper instanceof Item) {
			$itemStackWrapper = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($itemStackWrapper, $protocol));
		}

		$itemStack = $itemStackWrapper->getItemStack();
		if($this->putItemStackHeader($itemStack, $protocol)){
			if ($protocol >= ProtocolInfo::PROTOCOL_431) {
				$hasNetId = $itemStackWrapper->getStackId() !== 0;
				$this->putBool($hasNetId);
				if ($hasNetId) {
					$this->writeServerItemStackId($itemStackWrapper->getStackId());
				}
			}

			$this->putItemStackFooter($itemStack, $protocol);
		}
	}

	public function getRecipeIngredient(int $playerProtocol) : RecipeIngredient
	{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_554) {
			if ($playerProtocol < ProtocolInfo::PROTOCOL_361) {
				$item = $this->getItemStackWithoutStackId($playerProtocol);
				$id = $item->getId();
				$meta = $item->getMeta();
			} else {
				$id = $this->getVarInt();
				if ($id !== 0) {
					$meta = $this->getVarInt();
					$count = $this->getVarInt();
				}
			}

			$descriptor = new IntIdMetaItemDescriptor($id, $meta ?? 0);
		} else {
			$descriptorType = $this->getByte();
			$descriptor = match ($descriptorType) {
				ItemDescriptorType::INT_ID_META => IntIdMetaItemDescriptor::read($this),
				ItemDescriptorType::STRING_ID_META => StringIdMetaItemDescriptor::read($this),
				ItemDescriptorType::TAG => TagItemDescriptor::read($this),
				ItemDescriptorType::MOLANG => MolangItemDescriptor::read($this),
				ItemDescriptorType::COMPLEX_ALIAS => ComplexAliasItemDescriptor::read($this),
				default => null
			};

			$count = $this->getVarInt();
		}

		return new RecipeIngredient($descriptor, $count ?? 0);
	}

	public function putRecipeIngredient(RecipeIngredient $ingredient, int $playerProtocol) : void
	{
		$type = $ingredient->getDescriptor();
		if ($playerProtocol < ProtocolInfo::PROTOCOL_554) {
			if (!($type instanceof IntIdMetaItemDescriptor)) {
				$this->putVarInt(0);
				return;
			}

			if ($playerProtocol < ProtocolInfo::PROTOCOL_361) {
				$this->putItemStackWithoutStackId(new ItemStack(
					$type->getId(),
					$type->getMeta(),
					$ingredient->getCount(),
					0,
					null,
					[],
					[]
				), $playerProtocol);
			} else {
				$this->putVarInt($type->getId());
				$this->putVarInt($type->getMeta());
				$this->putVarInt($ingredient->getCount());
			}
		} else {
			if ($playerProtocol < ProtocolInfo::PROTOCOL_575 && $type instanceof ComplexAliasItemDescriptor) {
				$type = null;
			}

			$this->putByte($type?->getTypeId() ?? 0);
			$type?->write($this);
			$this->putVarInt($ingredient->getCount());
		}
	}

	/**
	 * Decodes entity metadata from the stream.
	 *
	 * @param bool $types Whether to include metadata types along with values in the returned array
	 */
	public function getEntityMetadata(int $playerProtocol, bool $types = true) : array
	{
		$count = $this->getUnsignedVarInt();
		if ($count > 128) {
			throw new UnexpectedValueException("Too many actor metadata: $count");
		}
		$data = [];
		for ($i = 0; $i < $count; ++$i) {
			$key = $this->getUnsignedVarInt();
			$type = $this->getUnsignedVarInt();
			$value = null;
			switch ($type) {
				case Entity::DATA_TYPE_BYTE:
					$value = (ord($this->get(1)));
					break;
				case Entity::DATA_TYPE_SHORT:
					$value = $this->getSignedLShort();
					break;
				case Entity::DATA_TYPE_INT:
					$value = $this->getVarInt();
					break;
				case Entity::DATA_TYPE_FLOAT:
					$value = $this->getLFloat();
					break;
				case Entity::DATA_TYPE_STRING:
					$value = $this->getString();
					break;
				case Entity::DATA_TYPE_SLOT:
					if ($playerProtocol >= ProtocolInfo::PROTOCOL_361) {
						$value = (new NetworkLittleEndianNBTStream())->read($this->buffer, false, $this->offset, 512);
					} else {
						$value = $this->getItemStackWithoutStackId($playerProtocol);
					}
					break;
				case Entity::DATA_TYPE_POS:
					$value = new Vector3(0, 0, 0);
					$this->getSignedBlockPosition($value->x, $value->y, $value->z);
					break;
				case Entity::DATA_TYPE_LONG:
					$value = $this->getVarLong();
					break;
				case Entity::DATA_TYPE_VECTOR3F:
					$value = $this->getVector3();
					break;
				default:
					throw new UnexpectedValueException("Invalid data type " . $type);
			}
			if ($types) {
				$data[$key] = [$type, $value];
			} else {
				$data[$key] = $value;
			}
		}

		return EntityMetadataTranslator::getInstance()->fromNetworkIds($data, $playerProtocol);
	}

	/**
	 * Writes entity metadata to the packet buffer.
	 */
	public function putEntityMetadata(array $metadata, int $playerProtocol) : void
	{
		$metadata = EntityMetadataTranslator::getInstance()->toNetworkIds($metadata, $playerProtocol);

		$this->putUnsignedVarInt(count($metadata));
		foreach ($metadata as $key => $d) {
			$this->putUnsignedVarInt($key); //data key
			$this->putUnsignedVarInt($d[0]); //data type
			switch ($d[0]) {
				case Entity::DATA_TYPE_BYTE:
					$this->putByte($d[1]);
					break;
				case Entity::DATA_TYPE_SHORT:
					$this->putLShort($d[1]);
					break;
				case Entity::DATA_TYPE_INT:
					$this->putVarInt($d[1]);
					break;
				case Entity::DATA_TYPE_FLOAT:
					$this->putLFloat($d[1]);
					break;
				case Entity::DATA_TYPE_STRING:
					$this->putString($d[1]);
					break;
				case Entity::DATA_TYPE_SLOT:
					/** @var Item $item */
					$item = $d[1];
					if ($playerProtocol >= ProtocolInfo::PROTOCOL_361) {
						$this->put((new NetworkLittleEndianNBTStream())->write($item->getNamedTag()));
					} else {
						$this->putItemStackWithoutStackId(TypeConverter::getInstance()->coreItemStackToNet($item, $playerProtocol), $playerProtocol);
					}
					break;
				case Entity::DATA_TYPE_POS:
					$v = $d[1];
					if ($v !== null) {
						$this->putSignedBlockPosition($v->x, $v->y, $v->z);
					} else {
						$this->putSignedBlockPosition(0, 0, 0);
					}
					break;
				case Entity::DATA_TYPE_LONG:
					$this->putVarLong($d[1]);
					break;
				case Entity::DATA_TYPE_VECTOR3F:
					$this->putVector3Nullable($d[1]);
					break;
				default:
					throw new UnexpectedValueException("Invalid data type " . $d[0]);
			}
		}
	}

	/**
	 * Reads a list of Attributes from the stream.
	 * @return Attribute[]
	 *
	 * @throws UnexpectedValueException if reading an attribute with an unrecognized name
	 */
	public function getAttributeList(int $playerProtocol) : array
	{
		$list = [];
		$count = $this->getUnsignedVarInt();

		for ($i = 0; $i < $count; ++$i) {
			$min = $this->getLFloat();
			$max = $this->getLFloat();
			$current = $this->getLFloat();
			if ($playerProtocol >= ProtocolInfo::PROTOCOL_729) {
				$this->getLFloat(); //default min value
				$this->getLFloat(); //default max value
			}
			$default = $this->getLFloat();
			$name = $this->getString();
			$modifiers = [];
			if ($playerProtocol >= ProtocolInfo::PROTOCOL_544) {
				for ($j = 0, $modifierCount = $this->getUnsignedVarInt(); $j < $modifierCount; $j++) {
					$modifiers[] = AttributeModifier::read($this);
				}
			}

			$attr = Attribute::getAttributeByName($name);
			if ($attr !== null) {
				$attr->setMinValue($min);
				$attr->setMaxValue($max);
				$attr->setValue($current);
				$attr->setDefaultValue($default);
				$attr->setModifiers($modifiers);

				$list[] = $attr;
			} else {
				throw new UnexpectedValueException("Unknown attribute type \"$name\"");
			}
		}

		return $list;
	}

	/**
	 * Writes a list of Attributes to the packet buffer using the standard format.
	 */
	public function putAttributeList(int $playerProtocol, Attribute ...$attributes) : void
	{
		$this->putUnsignedVarInt(count($attributes));
		foreach ($attributes as $attribute) {
			$this->putLFloat($attribute->getMinValue());
			$this->putLFloat($attribute->getMaxValue());
			$this->putLFloat($attribute->getValue());
			if ($playerProtocol >= ProtocolInfo::PROTOCOL_729) {
				$this->putLFloat($attribute->getMinValue()); //default min value
				$this->putLFloat($attribute->getMaxValue()); //default max value
			}
			$this->putLFloat($attribute->getDefaultValue());
			$this->putString($attribute->getName());
			if ($playerProtocol >= ProtocolInfo::PROTOCOL_544) {
				$this->putUnsignedVarInt(count($attribute->getModifiers()));
				foreach ($attribute->getModifiers() as $modifier) {
					$modifier->write($this);
				}
			}
		}
	}

	/**
	 * Reads and returns an EntityUniqueID
	 */
	final public function getEntityUniqueId() : int
	{
		return $this->getVarLong();
	}

	/**
	 * Writes an EntityUniqueID
	 */
	public function putEntityUniqueId(int $eid) : void
	{
		$this->putVarLong($eid);
	}

	/**
	 * Reads and returns an EntityRuntimeID
	 */
	final public function getEntityRuntimeId() : int
	{
		return $this->getUnsignedVarLong();
	}

	/**
	 * Writes an EntityRuntimeID
	 */
	public function putEntityRuntimeId(int $eid) : void
	{
		$this->putUnsignedVarLong($eid);
	}

	/**
	 * Reads an block position with unsigned Y coordinate.
	 *
	 * @param int &$x
	 * @param int &$y
	 * @param int &$z
	 */
	public function getBlockPosition(&$x, &$y, &$z) : void
	{
		$x = $this->getVarInt();
		$y = $this->getUnsignedVarInt();
		$z = $this->getVarInt();
	}

	/**
	 * Writes a block position with unsigned Y coordinate.
	 */
	public function putBlockPosition(int $x, int $y, int $z) : void
	{
		$this->putVarInt($x);
		$this->putUnsignedVarInt($y);
		$this->putVarInt($z);
	}

	/**
	 * Reads a block position with a signed Y coordinate.
	 *
	 * @param int &$x
	 * @param int &$y
	 * @param int &$z
	 */
	public function getSignedBlockPosition(&$x, &$y, &$z) : void
	{
		$x = $this->getVarInt();
		$y = $this->getVarInt();
		$z = $this->getVarInt();
	}

	/**
	 * Writes a block position with a signed Y coordinate.
	 */
	public function putSignedBlockPosition(int $x, int $y, int $z) : void
	{
		$this->putVarInt($x);
		$this->putVarInt($y);
		$this->putVarInt($z);
	}

	/**
	 * Reads a floating-point Vector3 object with coordinates rounded to 4 decimal places.
	 */
	public function getVector3() : Vector3
	{
		return new Vector3(
			$this->getLFloat(),
			$this->getLFloat(),
			$this->getLFloat()
		);
	}

	/**
	 * Reads a floating-point Vector2 object with coordinates rounded to 4 decimal places.
	 *
	 * @throws BinaryDataException
	 */
	public function getVector2() : Vector2
	{
		$x = $this->getLFloat();
		$y = $this->getLFloat();
		return new Vector2($x, $y);
	}

	/**
	 * Writes a floating-point Vector3 object, or 3x zero if null is given.
	 *
	 * Note: ONLY use this where it is reasonable to allow not specifying the vector.
	 * For all other purposes, use the non-nullable version.
	 *
	 * @see NetworkBinaryStream::putVector3()
	 */
	public function putVector3Nullable(?Vector3 $vector) : void
	{
		if ($vector) {
			$this->putVector3($vector);
		} else {
			$this->putLFloat(0.0);
			$this->putLFloat(0.0);
			$this->putLFloat(0.0);
		}
	}

	/**
	 * Writes a floating-point Vector3 object
	 */
	public function putVector3(Vector3 $vector) : void
	{
		$this->putLFloat($vector->x);
		$this->putLFloat($vector->y);
		$this->putLFloat($vector->z);
	}

	/**
	 * Writes a floating-point Vector2 object
	 */
	public function putVector2(Vector2 $vector2) : void
	{
		$this->putLFloat($vector2->x);
		$this->putLFloat($vector2->y);
	}

	public function getByteRotation() : float
	{
		return (float) ((ord($this->get(1))) * (360 / 256));
	}

	public function putByteRotation(float $rotation) : void
	{
		($this->buffer .= chr((int) ($rotation / (360 / 256))));
	}

	/**
	 * Reads gamerules
	 * TODO: implement this properly
	 *
	 * @return array, members are in the structure [name => [type, value, isPlayerModifiable]]
	 */
	public function getGameRules(bool $isStartGame, int $playerProtocol) : array
	{
		$count = $this->getUnsignedVarInt();
		$rules = [];
		for ($i = 0; $i < $count; ++$i) {
			$name = $this->getString();
			if ($playerProtocol >= ProtocolInfo::PROTOCOL_440) {
				$isPlayerModifiable = $this->getBool();
			}

			$type = $this->getUnsignedVarInt();
			$value = null;
			switch ($type) {
				case GameRuleType::BOOL:
					$value = $this->getBool();
					break;
				case GameRuleType::INT:
					if ($playerProtocol >= ProtocolInfo::PROTOCOL_844) {
						$value = $isStartGame ? $this->getUnsignedVarInt() : $this->getLInt();
					} else {
						$value = $this->getUnsignedVarInt();
					}
					break;
				case GameRuleType::FLOAT:
					$value = $this->getLFloat();
					break;
			}

			$rules[$name] = [$type, $value, $isPlayerModifiable ?? false];
		}

		return $rules;
	}

	/**
	 * Writes a gamerule array, members should be in the structure [name => [type, value, isPlayerModifiable]]
	 * TODO: implement this properly
	 */
	public function putGameRules(array $rules, bool $isStartGame, int $playerProtocol) : void
	{
		$this->putUnsignedVarInt(count($rules));
		foreach ($rules as $name => $rule) {
			$this->putString($name);
			if ($playerProtocol >= ProtocolInfo::PROTOCOL_440) {
				$this->putBool($rule[2] ?? false);
			}
			$this->putUnsignedVarInt($rule[0]);
			switch ($rule[0]) {
				case GameRuleType::BOOL:
					$this->putBool($rule[1]);
					break;
				case GameRuleType::INT:
					if ($playerProtocol >= ProtocolInfo::PROTOCOL_844) {
						if ($isStartGame) {
							$this->putUnsignedVarInt($rule[1]);
						} else {
							$this->putLInt($rule[1]);
						}
					} else {
						$this->putUnsignedVarInt($rule[1]);
					}
					break;
				case GameRuleType::FLOAT:
					$this->putLFloat($rule[1]);
					break;
			}
		}
	}

	protected function getEntityLink(int $playerProtocol) : EntityLink
	{
		$link = new EntityLink();

		$link->fromEntityUniqueId = $this->getEntityUniqueId();
		$link->toEntityUniqueId = $this->getEntityUniqueId();
		$link->type = $this->getByte();
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_137) {
			$link->immediate = $this->getBool();
			if ($playerProtocol >= ProtocolInfo::PROTOCOL_407) {
				$link->causedByRider = $this->getBool();
				if ($playerProtocol >= ProtocolInfo::PROTOCOL_712) {
					$link->vehicleAngularVelocity = $this->getLFloat();
				}
			}
		}

		return $link;
	}

	protected function putEntityLink(EntityLink $link, int $playerProtocol) : void
	{
		$this->putEntityUniqueId($link->fromEntityUniqueId);
		$this->putEntityUniqueId($link->toEntityUniqueId);
		$this->putByte($link->type);
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_137) {
			$this->putBool($link->immediate);
			if ($playerProtocol >= ProtocolInfo::PROTOCOL_407) {
				$this->putBool($link->causedByRider);
				if ($playerProtocol >= ProtocolInfo::PROTOCOL_712) {
					$this->putLFloat($link->vehicleAngularVelocity);
				}
			}
		}
	}

	protected function getCommandOriginData() : CommandOriginData
	{
		$result = new CommandOriginData();

		$result->type = $this->getUnsignedVarInt();
		$result->uuid = $this->getUUID();
		$result->requestId = $this->getString();

		if ($result->type === CommandOriginData::ORIGIN_DEV_CONSOLE || $result->type === CommandOriginData::ORIGIN_TEST) {
			$result->playerActorUniqueId = $this->getVarLong();
		}

		return $result;
	}

	protected function putCommandOriginData(CommandOriginData $data) : void
	{
		$this->putUnsignedVarInt($data->type);
		$this->putUUID($data->uuid);
		$this->putString($data->requestId);

		if ($data->type === CommandOriginData::ORIGIN_DEV_CONSOLE || $data->type === CommandOriginData::ORIGIN_TEST) {
			$this->putVarLong($data->playerActorUniqueId);
		}
	}

	protected function getStructureSettings() : StructureSettings
	{
		$result = new StructureSettings();

		$result->paletteName = $this->getString();

		$result->ignoreEntities = $this->getBool();
		$result->ignoreBlocks = $this->getBool();

		$this->getBlockPosition($result->structureSizeX, $result->structureSizeY, $result->structureSizeZ);
		$this->getBlockPosition($result->structureOffsetX, $result->structureOffsetY, $result->structureOffsetZ);

		$result->lastTouchedByPlayerID = $this->getEntityUniqueId();
		$result->rotation = $this->getByte();
		$result->mirror = $this->getByte();
		$result->integrityValue = $this->getFloat();
		$result->integritySeed = $this->getInt();

		return $result;
	}

	protected function putStructureSettings(StructureSettings $structureSettings) : void
	{
		$this->putString($structureSettings->paletteName);

		$this->putBool($structureSettings->ignoreEntities);
		$this->putBool($structureSettings->ignoreBlocks);

		$this->putBlockPosition($structureSettings->structureSizeX, $structureSettings->structureSizeY, $structureSettings->structureSizeZ);
		$this->putBlockPosition($structureSettings->structureOffsetX, $structureSettings->structureOffsetY, $structureSettings->structureOffsetZ);

		$this->putEntityUniqueId($structureSettings->lastTouchedByPlayerID);
		$this->putByte($structureSettings->rotation);
		$this->putByte($structureSettings->mirror);
		$this->putFloat($structureSettings->integrityValue);
		$this->putInt($structureSettings->integritySeed);
	}

	protected function getStructureEditorData(int $protocolVersion) : StructureEditorData
	{
		$result = new StructureEditorData();

		$result->structureName = $this->getString();
		if ($protocolVersion >= ProtocolInfo::PROTOCOL_776) {
			$result->filteredStructureName = $this->getString();
		}
		$result->structureDataField = $this->getString();

		$result->includePlayers = $this->getBool();
		$result->showBoundingBox = $this->getBool();

		$result->structureBlockType = $this->getVarInt();
		$result->structureSettings = $this->getStructureSettings();
		$result->structureRedstoneSaveMode = $this->getVarInt();

		return $result;
	}

	protected function putStructureEditorData(StructureEditorData $structureEditorData, int $protocolVersion) : void
	{
		$this->putString($structureEditorData->structureName);
		if ($protocolVersion >= ProtocolInfo::PROTOCOL_776) {
			$this->putString($structureEditorData->filteredStructureName);
		}
		$this->putString($structureEditorData->structureDataField);

		$this->putBool($structureEditorData->includePlayers);
		$this->putBool($structureEditorData->showBoundingBox);

		$this->putVarInt($structureEditorData->structureBlockType);
		$this->putStructureSettings($structureEditorData->structureSettings);
		$this->putVarInt($structureEditorData->structureRedstoneSaveMode);
	}

	public function getNbtRoot() : NamedTag
	{
		$offset = $this->getOffset();
		try {
			$result = (new NetworkLittleEndianNBTStream())->read($this->getBuffer(), false, $offset, 512);
			assert($result instanceof NamedTag, "doMultiple is false so we should definitely have a NamedTag here");
			return $result;
		} finally {
			$this->setOffset($offset);
		}
	}

	public function getNbtCompoundRoot() : CompoundTag
	{
		$root = $this->getNbtRoot();
		if (!($root instanceof CompoundTag)) {
			throw new UnexpectedValueException("Expected TAG_Compound root");
		}
		return $root;
	}

	public function readRecipeNetId() : int
	{
		return $this->getUnsignedVarInt();
	}

	public function writeRecipeNetId(int $id) : void
	{
		$this->putUnsignedVarInt($id);
	}

	public function readCreativeItemNetId() : int
	{
		return $this->getUnsignedVarInt();
	}

	public function writeCreativeItemNetId(int $id) : void
	{
		$this->putUnsignedVarInt($id);
	}

	/**
	 * This is a union of ItemStackRequestId, LegacyItemStackRequestId, and ServerItemStackId, used in serverbound
	 * packets to allow the client to refer to server known items, or items which may have been modified by a previous
	 * as-yet unacknowledged request from the client.
	 *
	 * - Server itemstack ID is positive
	 * - InventoryTransaction "legacy" request ID is negative and even
	 * - ItemStackRequest request ID is negative and odd
	 * - 0 refers to an empty itemstack (air)
	 */
	public function readItemStackNetIdVariant() : int
	{
		return $this->getVarInt();
	}

	/**
	 * This is a union of ItemStackRequestId, LegacyItemStackRequestId, and ServerItemStackId, used in serverbound
	 * packets to allow the client to refer to server known items, or items which may have been modified by a previous
	 * as-yet unacknowledged request from the client.
	 */
	public function writeItemStackNetIdVariant(int $id) : void
	{
		$this->putVarInt($id);
	}

	public function readItemStackRequestId() : int
	{
		return $this->getVarInt();
	}

	public function writeItemStackRequestId(int $id) : void
	{
		$this->putVarInt($id);
	}

	public function readLegacyItemStackRequestId() : int
	{
		return $this->getVarInt();
	}

	public function writeLegacyItemStackRequestId(int $id) : void
	{
		$this->putVarInt($id);
	}

	public function readServerItemStackId() : int
	{
		return $this->getVarInt();
	}

	public function writeServerItemStackId(int $id) : void
	{
		$this->putVarInt($id);
	}

	/**
	 * @phpstan-template T
	 * @phpstan-param \Closure() : T $reader
	 * @phpstan-return T|null
	 */
	public function readOptional(\Closure $reader) : mixed
	{
		if ($this->getBool()) {
			return $reader();
		}
		return null;
	}

	/**
	 * @phpstan-template T
	 * @phpstan-param T|null $value
	 * @phpstan-param \Closure(T) : void $writer
	 */
	public function writeOptional(mixed $value, \Closure $writer) : void
	{
		if ($value !== null) {
			$this->putBool(true);
			$writer($value);
		} else {
			$this->putBool(false);
		}
	}
	protected function prepareGeometryDataForOld(?string $skinGeometryData) : ?string
	{
		if (!empty($skinGeometryData)) {
			if (($tempData = @json_decode($skinGeometryData, true))) {
				unset($tempData["format_version"]);
				return json_encode($tempData);
			}
		}

		return $skinGeometryData;
	}
}
