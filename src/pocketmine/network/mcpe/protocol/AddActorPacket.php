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
use pocketmine\entity\Attribute;
use pocketmine\entity\EntityIds;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\network\mcpe\protocol\types\EntityLink;

use function array_search;
use function count;

class AddActorPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::ADD_ACTOR_PACKET;

	public const LEGACY_ID_MAP_BC = [
		EntityIds::NPC => "minecraft:npc",
		EntityIds::PLAYER => "minecraft:player",
		EntityIds::WITHER_SKELETON => "minecraft:wither_skeleton",
		EntityIds::HUSK => "minecraft:husk",
		EntityIds::STRAY => "minecraft:stray",
		EntityIds::WITCH => "minecraft:witch",
		EntityIds::ZOMBIE_VILLAGER => "minecraft:zombie_villager",
		EntityIds::BLAZE => "minecraft:blaze",
		EntityIds::MAGMA_CUBE => "minecraft:magma_cube",
		EntityIds::GHAST => "minecraft:ghast",
		EntityIds::CAVE_SPIDER => "minecraft:cave_spider",
		EntityIds::SILVERFISH => "minecraft:silverfish",
		EntityIds::ENDERMAN => "minecraft:enderman",
		EntityIds::SLIME => "minecraft:slime",
		EntityIds::ZOMBIE_PIGMAN => "minecraft:zombie_pigman",
		EntityIds::SPIDER => "minecraft:spider",
		EntityIds::SKELETON => "minecraft:skeleton",
		EntityIds::CREEPER => "minecraft:creeper",
		EntityIds::ZOMBIE => "minecraft:zombie",
		EntityIds::SKELETON_HORSE => "minecraft:skeleton_horse",
		EntityIds::MULE => "minecraft:mule",
		EntityIds::DONKEY => "minecraft:donkey",
		EntityIds::DOLPHIN => "minecraft:dolphin",
		EntityIds::TROPICALFISH => "minecraft:tropicalfish",
		EntityIds::WOLF => "minecraft:wolf",
		EntityIds::SQUID => "minecraft:squid",
		EntityIds::DROWNED => "minecraft:drowned",
		EntityIds::SHEEP => "minecraft:sheep",
		EntityIds::MOOSHROOM => "minecraft:mooshroom",
		EntityIds::PANDA => "minecraft:panda",
		EntityIds::SALMON => "minecraft:salmon",
		EntityIds::PIG => "minecraft:pig",
		EntityIds::VILLAGER => "minecraft:villager",
		EntityIds::COD => "minecraft:cod",
		EntityIds::PUFFERFISH => "minecraft:pufferfish",
		EntityIds::COW => "minecraft:cow",
		EntityIds::CHICKEN => "minecraft:chicken",
		EntityIds::BALLOON => "minecraft:balloon",
		EntityIds::LLAMA => "minecraft:llama",
		EntityIds::IRON_GOLEM => "minecraft:iron_golem",
		EntityIds::RABBIT => "minecraft:rabbit",
		EntityIds::SNOW_GOLEM => "minecraft:snow_golem",
		EntityIds::BAT => "minecraft:bat",
		EntityIds::OCELOT => "minecraft:ocelot",
		EntityIds::HORSE => "minecraft:horse",
		EntityIds::CAT => "minecraft:cat",
		EntityIds::POLAR_BEAR => "minecraft:polar_bear",
		EntityIds::ZOMBIE_HORSE => "minecraft:zombie_horse",
		EntityIds::TURTLE => "minecraft:turtle",
		EntityIds::PARROT => "minecraft:parrot",
		EntityIds::GUARDIAN => "minecraft:guardian",
		EntityIds::ELDER_GUARDIAN => "minecraft:elder_guardian",
		EntityIds::VINDICATOR => "minecraft:vindicator",
		EntityIds::WITHER => "minecraft:wither",
		EntityIds::ENDER_DRAGON => "minecraft:ender_dragon",
		EntityIds::SHULKER => "minecraft:shulker",
		EntityIds::ENDERMITE => "minecraft:endermite",
		EntityIds::MINECART => "minecraft:minecart",
		EntityIds::HOPPER_MINECART => "minecraft:hopper_minecart",
		EntityIds::TNT_MINECART => "minecraft:tnt_minecart",
		EntityIds::CHEST_MINECART => "minecraft:chest_minecart",
		EntityIds::COMMAND_BLOCK_MINECART => "minecraft:command_block_minecart",
		EntityIds::ARMOR_STAND => "minecraft:armor_stand",
		EntityIds::ITEM => "minecraft:item",
		EntityIds::TNT => "minecraft:tnt",
		EntityIds::FALLING_BLOCK => "minecraft:falling_block",
		EntityIds::XP_BOTTLE => "minecraft:xp_bottle",
		EntityIds::XP_ORB => "minecraft:xp_orb",
		EntityIds::EYE_OF_ENDER_SIGNAL => "minecraft:eye_of_ender_signal",
		EntityIds::ENDER_CRYSTAL => "minecraft:ender_crystal",
		EntityIds::SHULKER_BULLET => "minecraft:shulker_bullet",
		EntityIds::FISHING_HOOK => "minecraft:fishing_hook",
		EntityIds::DRAGON_FIREBALL => "minecraft:dragon_fireball",
		EntityIds::ARROW => "minecraft:arrow",
		EntityIds::SNOWBALL => "minecraft:snowball",
		EntityIds::EGG => "minecraft:egg",
		EntityIds::PAINTING => "minecraft:painting",
		EntityIds::THROWN_TRIDENT => "minecraft:thrown_trident",
		EntityIds::FIREBALL => "minecraft:fireball",
		EntityIds::SPLASH_POTION => "minecraft:splash_potion",
		EntityIds::ENDER_PEARL => "minecraft:ender_pearl",
		EntityIds::LEASH_KNOT => "minecraft:leash_knot",
		EntityIds::WITHER_SKULL => "minecraft:wither_skull",
		EntityIds::WITHER_SKULL_DANGEROUS => "minecraft:wither_skull_dangerous",
		EntityIds::BOAT => "minecraft:boat",
		EntityIds::LIGHTNING_BOLT => "minecraft:lightning_bolt",
		EntityIds::SMALL_FIREBALL => "minecraft:small_fireball",
		EntityIds::LLAMA_SPIT => "minecraft:llama_spit",
		EntityIds::AREA_EFFECT_CLOUD => "minecraft:area_effect_cloud",
		EntityIds::LINGERING_POTION => "minecraft:lingering_potion",
		EntityIds::FIREWORKS_ROCKET => "minecraft:fireworks_rocket",
		EntityIds::EVOCATION_FANG => "minecraft:evocation_fang",
		EntityIds::EVOCATION_ILLAGER => "minecraft:evocation_illager",
		EntityIds::VEX => "minecraft:vex",
		EntityIds::AGENT => "minecraft:agent",
		EntityIds::ICE_BOMB => "minecraft:ice_bomb",
		EntityIds::PHANTOM => "minecraft:phantom",
		EntityIds::TRIPOD_CAMERA => "minecraft:tripod_camera"
	];

	public ?int $entityUniqueId = null; //TODO
	public int $entityRuntimeId;
	public int $type;
	public Vector3 $position;
	public ?Vector3 $motion = null;
	public float $pitch = 0.0;
	public float $yaw = 0.0;
	public float $headYaw = 0.0;
	public float $bodyYaw = 0.0;

	/** @var Attribute[] */
	public array $attributes = [];
	public array $metadata = [];
	public ?PropertySyncData $syncedProperties = null;
	/** @var EntityLink[] */
	public array $links = [];

	/**
	 * @generate-create-func
	 *
	 * @param Attribute[]  $attributes
	 * @param EntityLink[] $links
	 */
	public static function create(
		int $entityUniqueId,
		int $entityRuntimeId,
		int $type,
		Vector3 $position,
		?Vector3 $motion,
		float $pitch,
		float $yaw,
		float $headYaw,
		float $bodyYaw,
		array $attributes,
		array $metadata,
		PropertySyncData $syncedProperties,
		array $links
	) : self
	{
		$result = new self();
		$result->entityUniqueId = $entityUniqueId;
		$result->entityRuntimeId = $entityRuntimeId;
		$result->type = $type;
		$result->position = $position;
		$result->motion = $motion;
		$result->pitch = $pitch;
		$result->yaw = $yaw;
		$result->headYaw = $headYaw;
		$result->bodyYaw = $bodyYaw;
		$result->attributes = $attributes;
		$result->metadata = $metadata;
		$result->syncedProperties = $syncedProperties;
		$result->links = $links;
		return $result;
	}

	protected function decodePayload() : void
	{
		$this->entityUniqueId = $this->getEntityUniqueId();
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_313) {
			$this->type = array_search($t = $this->getString(), self::LEGACY_ID_MAP_BC, true);
			if ($this->type === false) {
				throw new PacketDecodeException("Can't map ID $t to legacy ID");
			}
		} else {
			$this->type = $this->getUnsignedVarInt();
		}
		$this->position = $this->getVector3();
		$this->motion = $this->getVector3();
		$this->pitch = $this->getLFloat();
		$this->yaw = $this->getLFloat();
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_274) {
			$this->headYaw = $this->getLFloat();
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_534) {
				$this->bodyYaw = $this->getLFloat();
			}
		}

		$attrCount = $this->getUnsignedVarInt();
		for ($i = 0; $i < $attrCount; ++$i) {
			$name = $this->getString();
			$min = $this->getLFloat();
			$current = $this->getLFloat();
			$max = $this->getLFloat();
			$attr = Attribute::getAttributeByName($name);

			if ($attr !== null) {
				$attr->setMinValue($min);
				$attr->setMaxValue($max);
				$attr->setValue($current);
				$attr->setModifiers([]);
				$this->attributes[] = $attr;
			} else {
				throw new PacketDecodeException("Unknown attribute type \"$name\"");
			}
		}

		$this->metadata = $this->getEntityMetadata($this->getProtocol());
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_557) {
			$this->syncedProperties = PropertySyncData::read($this);
		}
		$linkCount = $this->getUnsignedVarInt();
		for ($i = 0; $i < $linkCount; ++$i) {
			$this->links[] = $this->getEntityLink($this->getProtocol());
		}
	}

	protected function encodePayload() : void
	{
		$this->putEntityUniqueId($this->entityUniqueId ?? $this->entityRuntimeId);
		$this->putEntityRuntimeId($this->entityRuntimeId);
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_313) {
			if (!isset(self::LEGACY_ID_MAP_BC[$this->type])) {
				throw new InvalidArgumentException("Unknown entity numeric ID $this->type");
			}
			$this->putString(self::LEGACY_ID_MAP_BC[$this->type]);
		} else {
			$this->putUnsignedVarInt($this->type);
		}
		$this->putVector3($this->position);
		$this->putVector3Nullable($this->motion);
		$this->putLFloat($this->pitch);
		$this->putLFloat($this->yaw);
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_274) {
			$this->putLFloat($this->headYaw);
			if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_534) {
				$this->putLFloat($this->bodyYaw);
			}
		}

		$this->putUnsignedVarInt(count($this->attributes));
		foreach ($this->attributes as $attribute) {
			$this->putString($attribute->getName());
			$this->putLFloat($attribute->getMinValue());
			$this->putLFloat($attribute->getValue());
			$this->putLFloat($attribute->getMaxValue());
		}

		$this->putEntityMetadata($this->metadata, $this->getProtocol());
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_557) {
			if ($this->syncedProperties === null) {
				$this->syncedProperties = new PropertySyncData([], []);
			}

			$this->syncedProperties->write($this);
		}
		$this->putUnsignedVarInt(count($this->links));
		foreach ($this->links as $link) {
			$this->putEntityLink($link, $this->getProtocol());
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleAddActor($this);
	}
}
