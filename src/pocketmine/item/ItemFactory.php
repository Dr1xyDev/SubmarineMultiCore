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

namespace pocketmine\item;

use InvalidArgumentException;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\block\SignPost;
use pocketmine\block\Skull as BlockSkull;
use pocketmine\block\StillLava;
use pocketmine\block\StillWater;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\tile\Skull as TileSkull;
use RuntimeException;
use TypeError;

use function constant;
use function defined;
use function explode;
use function get_class;
use function gettype;
use function is_numeric;
use function is_object;
use function is_string;
use function mb_strtoupper;
use function str_replace;
use function trim;

/**
 * Manages Item instance creation and registration
 */
class ItemFactory
{
	public static ?array $list = null;

	public static function init() : void
	{
		if (self::$list === null) {
			self::$list = [];

			//TODO: ARMOR
			self::registerItem(new LeatherCap());
			self::registerItem(new LeatherTunic());
			self::registerItem(new LeatherPants());
			self::registerItem(new LeatherBoots());
			self::registerItem(new ChainHelmet());
			self::registerItem(new ChainChestplate());
			self::registerItem(new ChainLeggings());
			self::registerItem(new ChainBoots());
			self::registerItem(new GoldHelmet());
			self::registerItem(new GoldChestplate());
			self::registerItem(new GoldLeggings());
			self::registerItem(new GoldBoots());
			self::registerItem(new IronHelmet());
			self::registerItem(new IronChestplate());
			self::registerItem(new IronLeggings());
			self::registerItem(new IronBoots());
			self::registerItem(new DiamondHelmet());
			self::registerItem(new DiamondChestplate());
			self::registerItem(new DiamondLeggings());
			self::registerItem(new DiamondBoots());
			self::registerItem(new NetheriteHelmet());
			self::registerItem(new NetheriteChestplate());
			self::registerItem(new NetheriteLeggings());
			self::registerItem(new NetheriteBoots());

			//TODO: SPAWN EGGS
			for ($entityId = 10; $entityId <= 128; $entityId++) {
				self::registerItem(new SpawnEgg($entityId));
			}

			//TODO: TIER TOOL ITEMS
			self::registerItem(new Axe(Item::WOODEN_AXE, 0, "Wooden Axe", TieredTool::TIER_WOODEN));
			self::registerItem(new Axe(Item::STONE_AXE, 0, "Stone Axe", TieredTool::TIER_STONE));
			self::registerItem(new Axe(Item::IRON_AXE, 0, "Iron Axe", TieredTool::TIER_IRON));
			self::registerItem(new Axe(Item::DIAMOND_AXE, 0, "Diamond Axe", TieredTool::TIER_DIAMOND));
			self::registerItem(new Axe(Item::NETHERITE_AXE, 0, "Netherite Axe", TieredTool::TIER_NETHERITE));
			self::registerItem(new Axe(Item::GOLDEN_AXE, 0, "Gold Axe", TieredTool::TIER_GOLD));
			self::registerItem(new Hoe(Item::WOODEN_HOE, 0, "Wooden Hoe", TieredTool::TIER_WOODEN));
			self::registerItem(new Hoe(Item::STONE_HOE, 0, "Stone Hoe", TieredTool::TIER_STONE));
			self::registerItem(new Hoe(Item::IRON_HOE, 0, "Iron Hoe", TieredTool::TIER_IRON));
			self::registerItem(new Hoe(Item::DIAMOND_HOE, 0, "Diamond Hoe", TieredTool::TIER_DIAMOND));
			self::registerItem(new Hoe(Item::NETHERITE_HOE, 0, "Netherite Hoe", TieredTool::TIER_NETHERITE));
			self::registerItem(new Hoe(Item::GOLDEN_HOE, 0, "Golden Hoe", TieredTool::TIER_GOLD));
			self::registerItem(new Pickaxe(Item::WOODEN_PICKAXE, 0, "Wooden Pickaxe", TieredTool::TIER_WOODEN));
			self::registerItem(new Pickaxe(Item::STONE_PICKAXE, 0, "Stone Pickaxe", TieredTool::TIER_STONE));
			self::registerItem(new Pickaxe(Item::IRON_PICKAXE, 0, "Iron Pickaxe", TieredTool::TIER_IRON));
			self::registerItem(new Pickaxe(Item::DIAMOND_PICKAXE, 0, "Diamond Pickaxe", TieredTool::TIER_DIAMOND));
			self::registerItem(new Pickaxe(Item::NETHERITE_PICKAXE, 0, "Netherite Pickaxe", TieredTool::TIER_NETHERITE));
			self::registerItem(new Pickaxe(Item::GOLDEN_PICKAXE, 0, "Gold Pickaxe", TieredTool::TIER_GOLD));
			self::registerItem(new Shovel(Item::WOODEN_SHOVEL, 0, "Wooden Shovel", TieredTool::TIER_WOODEN));
			self::registerItem(new Shovel(Item::STONE_SHOVEL, 0, "Stone Shovel", TieredTool::TIER_STONE));
			self::registerItem(new Shovel(Item::IRON_SHOVEL, 0, "Iron Shovel", TieredTool::TIER_IRON));
			self::registerItem(new Shovel(Item::DIAMOND_SHOVEL, 0, "Diamond Shovel", TieredTool::TIER_DIAMOND));
			self::registerItem(new Shovel(Item::NETHERITE_SHOVEL, 0, "Netherite Shovel", TieredTool::TIER_NETHERITE));
			self::registerItem(new Shovel(Item::GOLDEN_SHOVEL, 0, "Gold Shovel", TieredTool::TIER_GOLD));
			self::registerItem(new Sword(Item::WOODEN_SWORD, 0, "Wooden Sword", TieredTool::TIER_WOODEN));
			self::registerItem(new Sword(Item::STONE_SWORD, 0, "Stone Sword", TieredTool::TIER_STONE));
			self::registerItem(new Sword(Item::IRON_SWORD, 0, "Iron Sword", TieredTool::TIER_IRON));
			self::registerItem(new Sword(Item::DIAMOND_SWORD, 0, "Diamond Sword", TieredTool::TIER_DIAMOND));
			self::registerItem(new Sword(Item::NETHERITE_SWORD, 0, "Netherite Sword", TieredTool::TIER_NETHERITE));
			self::registerItem(new Sword(Item::GOLDEN_SWORD, 0, "Gold Sword", TieredTool::TIER_GOLD));

			//TODO: ALL ITEMS

			self::registerItem(new Apple());
			self::registerItem(new ArmorStand());

			for ($meta = 0; $meta <= 32; $meta++) {
				self::registerItem(new Arrow($meta));
			}

			self::registerItem(new BakedPotato());
			self::registerItem(new Beetroot());
			self::registerItem(new BeetrootSeeds());
			self::registerItem(new BeetrootSoup());
			self::registerItem(new BlazeRod());
			self::registerItem(new Bleach(Item::BLEACH, 0, "Bleach")); //EDU
			self::registerItem(new Book());
			self::registerItem(new Bow());
			self::registerItem(new Bowl());
			self::registerItem(new Bread());
			self::registerItem(new Bucket(Item::BUCKET, 0, "Bucket"));
			self::registerItem(new Carrot());
			self::registerItem(new ChorusFruit());
			self::registerItem(new Clock());
			self::registerItem(new Clownfish());
			self::registerItem(new Coal(Item::COAL, 0, "Coal"));

			//TODO: corals

			self::registerItem(new Coal(Item::COAL, 1, "Charcoal"));
			self::registerItem(new CocoaBeans(Item::DYE, 3, "Cocoa Beans"));
			self::registerItem(new Compass());
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::SALT, "Salt"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::SODIUM_OXIDE, "Sodium Oxide"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::SODIUM_HYDROXIDE, "Sodium Hydroxide"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::MAGNESIUM_NITRATE, "Magnesium Nitrate"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::IRON_SULPHIDE, "Iron Sulphide"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::LITHIUM_HYDRIDE, "Lithium Hydride"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::SODIUM_HYDRIDE, "Sodium Hydride"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::CALCIUM_BROMIDE, "Calcium Bromide"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::MAGNESIUM_OXIDE, "Magnesium Oxide"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::SODIUM_ACETATE, "Sodium Acetate"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::LUMINOL, "Luminol"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::CHARCOAL, "Charcoal")); //??? maybe bug
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::SUGAR, "Sugar")); //??? maybe bug
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::ALUMINIUM_OXIDE, "Aluminium Oxide"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::BORON_TRIOXIDE, "Boron Trioxide"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::SOAP, "Soap"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::POLYETHYLENE, "Polyethylene"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::RUBBISH, "Rubbish"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::MAGNESIUM_SALTS, "Magnesium Salts"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::SULPHATE, "Sulphate"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::BARIUM_SULPHATE, "Barium Sulphate"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::POTASSIUM_CHLORIDE, "Potassium Chloride"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::MERCURIC_CHLORIDE, "Mercuric Chloride"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::CERIUM_CHLORIDE, "Cerium Chloride"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::TUNGSTEN_CHLORIDE, "Tungsten Chloride"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::CALCIUM_CHLORIDE, "Calcium Chloride"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::WATER, "Water")); //???
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::GLUE, "Glue"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::HYPOCHLORITE, "Hypochlorite"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::CRUDE_OIL, "Crude Oil"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::LATEX, "Latex"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::POTASSIUM_IODIDE, "Potassium Iodide"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::SODIUM_FLUORIDE, "Sodium Fluoride"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::BENZENE, "Benzene"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::INK, "Ink"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::HYDROGEN_PEROXIDE, "Hydrogen Peroxide"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::AMMONIA, "Ammonia"));
			self::registerItem(new Compound(Item::COMPOUND, CompoundTypeIds::SODIUM_HYPOCHLORITE, "Sodium Hypochlorite"));
			self::registerItem(new CookedChicken());
			self::registerItem(new CookedFish());
			self::registerItem(new CookedMutton());
			self::registerItem(new CookedPorkchop());
			self::registerItem(new CookedRabbit());
			self::registerItem(new CookedSalmon());
			self::registerItem(new Cookie());
			self::registerItem(new DriedKelp());
			self::registerItem(new Egg());
			self::registerItem(new EmptyMap());
			self::registerItem(new Elytra());
			self::registerItem(new EnchantedBook());
			self::registerItem(new EndCrystal());
			self::registerItem(new EnderPearl());
			self::registerItem(new ExperienceBottle());
			self::registerItem(new Fertilizer(Item::DYE, 15, "Bone Meal"));
			self::registerItem(new FishingRod());
			self::registerItem(new FlintSteel());
			self::registerItem(new GlassBottle());
			self::registerItem(new GoldenApple());
			self::registerItem(new GoldenAppleEnchanted());
			self::registerItem(new GoldenCarrot());

			self::registerItem(new HorseArmor(Item::LEATHER_HORSE_ARMOR, 0, "Leather Horse Armor"));
			self::registerItem(new HorseArmor(Item::IRON_HORSE_ARMOR, 0, "Iron Horse Armor"));
			self::registerItem(new HorseArmor(Item::GOLD_HORSE_ARMOR, 0, "Golden Horse Armor"));
			self::registerItem(new HorseArmor(Item::DIAMOND_HORSE_ARMOR, 0, "Diamond Horse Armor"));

			self::registerItem(new Honeycomb());

			self::registerItem(new Item(Item::BLAZE_POWDER, 0, "Blaze Powder"));
			self::registerItem(new Item(Item::BONE, 0, "Bone"));
			self::registerItem(new Item(Item::BRICK, 0, "Brick"));
			self::registerItem(new Item(Item::CHORUS_FRUIT_POPPED, 0, "Popped Chorus Fruit"));
			self::registerItem(new Item(Item::CLAY_BALL, 0, "Clay"));
			self::registerItem(new Item(Item::DIAMOND, 0, "Diamond"));
			self::registerItem(new Item(Item::DRAGON_BREATH, 0, "Dragon's Breath"));
			self::registerItem(new Item(Item::DYE, 0, "Ink Sac"));
			self::registerItem(new Item(Item::DYE, 4, "Lapis Lazuli"));
			self::registerItem(new Item(Item::EMERALD, 0, "Emerald"));
			self::registerItem(new Item(Item::FEATHER, 0, "Feather"));
			self::registerItem(new Item(Item::FLINT, 0, "Flint"));
			self::registerItem(new Item(Item::GHAST_TEAR, 0, "Ghast Tear"));
			self::registerItem(new Item(Item::GLISTERING_MELON, 0, "Glistering Melon"));
			self::registerItem(new Item(Item::GLOWSTONE_DUST, 0, "Glowstone Dust"));
			self::registerItem(new Item(Item::GOLD_INGOT, 0, "Gold Ingot"));
			self::registerItem(new Item(Item::GOLD_NUGGET, 0, "Gold Nugget"));
			self::registerItem(new Item(Item::GUNPOWDER, 0, "Gunpowder"));
			self::registerItem(new Item(Item::HEART_OF_THE_SEA, 0, "Heart of the Sea"));
			self::registerItem(new Item(Item::IRON_INGOT, 0, "Iron Ingot"));
			self::registerItem(new Item(Item::IRON_NUGGET, 0, "Iron Nugget"));
			self::registerItem(new Item(Item::LEAD, 0, "Lead"));
			self::registerItem(new Item(Item::LEATHER, 0, "Leather"));
			self::registerItem(new Item(Item::MAGMA_CREAM, 0, "Magma Cream"));
			self::registerItem(new Item(Item::NAUTILUS_SHELL, 0, "Nautilus Shell"));
			self::registerItem(new Item(Item::NETHER_BRICK, 0, "Nether Brick"));
			self::registerItem(new Item(Item::NETHER_QUARTZ, 0, "Nether Quartz"));
			self::registerItem(new Item(Item::NETHER_STAR, 0, "Nether Star"));
			self::registerItem(new Item(Item::PAPER, 0, "Paper"));
			self::registerItem(new Item(Item::PRISMARINE_CRYSTALS, 0, "Prismarine Crystals"));
			self::registerItem(new Item(Item::PRISMARINE_SHARD, 0, "Prismarine Shard"));
			self::registerItem(new Item(Item::RABBIT_FOOT, 0, "Rabbit's Foot"));
			self::registerItem(new Item(Item::RABBIT_HIDE, 0, "Rabbit Hide"));
			self::registerItem(new Item(Item::SHULKER_SHELL, 0, "Shulker Shell"));
			self::registerItem(new Item(Item::SLIME_BALL, 0, "Slimeball"));
			self::registerItem(new Item(Item::SUGAR, 0, "Sugar"));
			self::registerItem(new Item(Item::TURTLE_SHELL_PIECE, 0, "Scute"));
			self::registerItem(new Item(Item::WHEAT, 0, "Wheat"));

			self::registerItem(new ItemBlock(Item::ACACIA_DOOR, 0, Block::get(Block::ACACIA_DOOR_BLOCK)));
			self::registerItem(new ItemBlock(Item::BIRCH_DOOR, 0, Block::get(Block::BIRCH_DOOR_BLOCK)));
			self::registerItem(new ItemBlock(Item::BREWING_STAND, 0, Block::get(Block::BREWING_STAND_BLOCK)));
			self::registerItem(new ItemBlock(Item::CAKE, 0, Block::get(Block::CAKE_BLOCK)));
			self::registerItem(new ItemBlock(Item::CAULDRON, 0, Block::get(Block::CAULDRON_BLOCK)));
			self::registerItem(new ItemBlock(Item::COMPARATOR, 0, Block::get(Block::COMPARATOR_BLOCK)));
			self::registerItem(new ItemBlock(Item::CRIMSON_DOOR, 0, Block::get(Block::CRIMSON_DOOR)));
			self::registerItem(new ItemBlock(Item::DARK_OAK_DOOR, 0, Block::get(Block::DARK_OAK_DOOR_BLOCK)));
			self::registerItem(new ItemBlock(Item::FLOWER_POT, 0, Block::get(Block::FLOWER_POT_BLOCK)));
			self::registerItem(new ItemBlock(Item::GLOW_FRAME, 0, Block::get(Block::GLOW_FRAME)));
			self::registerItem(new ItemBlock(Item::HOPPER, 0, Block::get(Block::HOPPER_BLOCK)));
			self::registerItem(new ItemBlock(Item::IRON_DOOR, 0, Block::get(Block::IRON_DOOR_BLOCK)));
			self::registerItem(new ItemBlock(Item::ITEM_FRAME, 0, Block::get(Block::ITEM_FRAME_BLOCK)));
			self::registerItem(new ItemBlock(Item::JUNGLE_DOOR, 0, Block::get(Block::JUNGLE_DOOR_BLOCK)));
			self::registerItem(new ItemBlock(Item::NETHER_WART, 0, Block::get(Block::NETHER_WART_PLANT)));
			self::registerItem(new ItemBlock(Item::OAK_DOOR, 0, Block::get(Block::OAK_DOOR_BLOCK)));
			self::registerItem(new ItemBlock(Item::REPEATER, 0, Block::get(Block::REPEATER_BLOCK)));
			self::registerItem(new ItemBlock(Item::SPRUCE_DOOR, 0, Block::get(Block::SPRUCE_DOOR_BLOCK)));
			self::registerItem(new ItemBlock(Item::SUGARCANE, 0, Block::get(Block::SUGARCANE_BLOCK)));
			self::registerItem(new ItemBlock(Item::WARPED_DOOR, 0, Block::get(Block::WARPED_DOOR)));
			self::registerItem(new ItemBlock(Item::MANGROVE_DOOR, 0, Block::get(Block::MANGROVE_DOOR)));
			self::registerItem(new ItemBlock(Item::BAMBOO_DOOR, 0, Block::get(Block::BAMBOO_DOOR)), true);
			self::registerItem(new ItemBlock(Item::PALE_OAK_DOOR, 0, Block::get(Block::PALE_OAK_DOOR)), true);
			self::registerItem(new Pumpkin(Item::PUMPKIN, 0, Block::get(Block::PUMPKIN)), true);
			self::registerItem(new Pumpkin(Item::CARVED_PUMPKIN, 0, Block::get(Block::CARVED_PUMPKIN)), true);
			self::registerItem(new LiquidBucket(Item::BUCKET, 8, "Water Bucket", new StillWater()));
			self::registerItem(new LiquidBucket(Item::BUCKET, 10, "Lava Bucket", new StillLava()));
			self::registerItem(new Map());
			self::registerItem(new Melon());
			self::registerItem(new MelonSeeds());
			self::registerItem(new MilkBucket(Item::BUCKET, 1, "Milk Bucket"));
			self::registerItem(new Minecart());
			self::registerItem(new MushroomStew());
			self::registerItem(new NetheriteScarp(Item::NETHERITE_SCRAP, 0, "Netherite Scrap"));
			self::registerItem(new NetheriteIngot(Item::NETHERITE_INGOT, 0, "Netherite Ingot"));
			self::registerItem(new PaintingItem());
			self::registerItem(new PoisonousPotato());
			self::registerItem(new Potato());
			self::registerItem(new Pufferfish());
			self::registerItem(new PumpkinPie());
			self::registerItem(new PumpkinSeeds());
			self::registerItem(new RabbitStew());
			self::registerItem(new RawBeef());
			self::registerItem(new RawChicken());
			self::registerItem(new RawFish());
			self::registerItem(new RawMutton());
			self::registerItem(new RawPorkchop());
			self::registerItem(new RawRabbit());
			self::registerItem(new RawSalmon());
			self::registerItem(new Record(Item::RECORD_13, LevelSoundEventPacket::SOUND_RECORD_13));
			self::registerItem(new Record(Item::RECORD_CAT, LevelSoundEventPacket::SOUND_RECORD_CAT));
			self::registerItem(new Record(Item::RECORD_BLOCKS, LevelSoundEventPacket::SOUND_RECORD_BLOCKS));
			self::registerItem(new Record(Item::RECORD_CHIRP, LevelSoundEventPacket::SOUND_RECORD_CHIRP));
			self::registerItem(new Record(Item::RECORD_FAR, LevelSoundEventPacket::SOUND_RECORD_FAR));
			self::registerItem(new Record(Item::RECORD_MALL, LevelSoundEventPacket::SOUND_RECORD_MALL));
			self::registerItem(new Record(Item::RECORD_MELLOHI, LevelSoundEventPacket::SOUND_RECORD_MELLOHI));
			self::registerItem(new Record(Item::RECORD_STAL, LevelSoundEventPacket::SOUND_RECORD_STAL));
			self::registerItem(new Record(Item::RECORD_STRAD, LevelSoundEventPacket::SOUND_RECORD_STRAD));
			self::registerItem(new Record(Item::RECORD_WARD, LevelSoundEventPacket::SOUND_RECORD_WARD));
			self::registerItem(new Record(Item::RECORD_11, LevelSoundEventPacket::SOUND_RECORD_11));
			self::registerItem(new Record(Item::RECORD_WAIT, LevelSoundEventPacket::SOUND_RECORD_WAIT));
			self::registerItem(new Redstone());
			self::registerItem(new RottenFlesh());
			self::registerItem(new Saddle());
			self::registerItem(new Shears());
			self::registerItem(new Shield());
			self::registerItem(new ShulkerBox(), true);
			self::registerItem(new Sign(Item::SIGN, 0, "Oak Sign", new SignPost(Block::SIGN_POST, 0, "Oak Sign Post", Item::SIGN, Block::WALL_SIGN)));
			self::registerItem(new SpruceSign(Item::SPRUCE_SIGN, 0, "Spruce Sign", new SignPost(Block::SPRUCE_STANDING_SIGN, 0, "Spruce Sign Post", Item::SPRUCE_SIGN, Block::SPRUCE_WALL_SIGN)));
			self::registerItem(new BirchSign(Item::BIRCH_SIGN, 0, "Birch Sign", new SignPost(Block::BIRCH_STANDING_SIGN, 0, "Birch Sign Post", Item::BIRCH_SIGN, Block::BIRCH_WALL_SIGN)));
			self::registerItem(new JungleSign(Item::JUNGLE_SIGN, 0, "Jungle Sign", new SignPost(Block::JUNGLE_STANDING_SIGN, 0, "Jungle Sign Post", Item::JUNGLE_SIGN, Block::JUNGLE_WALL_SIGN)));
			self::registerItem(new AcaciaSign(Item::ACACIA_SIGN, 0, "Acacia Sign", new SignPost(Block::ACACIA_STANDING_SIGN, 0, "Acacia Sign Post", Item::ACACIA_SIGN, Block::ACACIA_WALL_SIGN)));
			self::registerItem(new DarkoakSign(Item::DARKOAK_SIGN, 0, "Dark Oak Sign", new SignPost(Block::DARKOAK_STANDING_SIGN, 0, "Dark Oak Sign Post", Item::DARKOAK_SIGN, Block::DARKOAK_WALL_SIGN)));
			self::registerItem(new WarpedSign(Item::WARPED_SIGN, 0, "Warped Sign", new SignPost(Block::WARPED_STANDING_SIGN, 0, "Warped Sign Post", Item::WARPED_SIGN, Block::WARPED_WALL_SIGN)));
			self::registerItem(new CrimsonSign(Item::CRIMSON_SIGN, 0, "Crimson Sign", new SignPost(Block::CRIMSON_STANDING_SIGN, 0, "Crimson Sign Post", Item::CRIMSON_SIGN, Block::CRIMSON_WALL_SIGN)));
			self::registerItem(new MangroveSign(Item::MANGROVE_SIGN, 0, "Mangrove Sign", new SignPost(Block::MANGROVE_STANDING_SIGN, 0, "Mangrove Sign Post", Item::MANGROVE_SIGN, Block::MANGROVE_WALL_SIGN)));
			self::registerItem(new PaleOakSign(Item::PALE_OAK_SIGN, 0, "Pale Oak Sign", new SignPost(Block::PALE_OAK_STANDING_SIGN, 0, "Pale Oak Sign Post", Item::PALE_OAK_SIGN, Block::PALE_OAK_WALL_SIGN)));
			self::registerItem(new BambooSign(Item::BAMBOO_SIGN, 0, "Bamboo Sign", new SignPost(Block::BAMBOO_STANDING_SIGN, 0, "Bamboo Sign Post", Item::BAMBOO_SIGN, Block::BAMBOO_WALL_SIGN)));

			self::registerItem(new Snowball());
			self::registerItem(new SpiderEye());
			self::registerItem(new Spyglass());
			self::registerItem(new Steak());
			self::registerItem(new Stick());
			self::registerItem(new StringItem());
			self::registerItem(new Totem());
			self::registerItem(new WheatSeeds());
			self::registerItem(new WoodenTrapdoor(Item::WOODEN_TRAPDOOR, 0, BlockFactory::get(Block::WOODEN_TRAPDOOR)), true);
			self::registerItem(new WoodenTrapdoor(Item::SPRUCE_TRAPDOOR, 0, BlockFactory::get(Block::SPRUCE_TRAPDOOR)), true);
			self::registerItem(new WoodenTrapdoor(Item::BIRCH_TRAPDOOR, 0, BlockFactory::get(Block::BIRCH_TRAPDOOR)), true);
			self::registerItem(new WoodenTrapdoor(Item::JUNGLE_TRAPDOOR, 0, BlockFactory::get(Block::JUNGLE_TRAPDOOR)), true);
			self::registerItem(new WoodenTrapdoor(Item::ACACIA_TRAPDOOR, 0, BlockFactory::get(Block::ACACIA_TRAPDOOR)), true);
			self::registerItem(new WoodenTrapdoor(Item::DARK_OAK_TRAPDOOR, 0, BlockFactory::get(Block::DARK_OAK_TRAPDOOR)), true);
			self::registerItem(new WoodenTrapdoor(Item::MANGROVE_TRAPDOOR, 0, BlockFactory::get(Block::MANGROVE_TRAPDOOR)), true);
			self::registerItem(new WoodenTrapdoor(Item::BAMBOO_TRAPDOOR, 0, BlockFactory::get(Block::BAMBOO_TRAPDOOR)), true);
			self::registerItem(new WoodenTrapdoor(Item::PALE_OAK_TRAPDOOR, 0, BlockFactory::get(Block::PALE_OAK_TRAPDOOR)), true);
			self::registerItem(new WoodenPressurePlate(Item::WOODEN_PRESSURE_PLATE, 0, BlockFactory::get(Block::WOODEN_PRESSURE_PLATE)), true);
			self::registerItem(new WoodenPressurePlate(Item::SPRUCE_PRESSURE_PLATE, 0, BlockFactory::get(Block::SPRUCE_PRESSURE_PLATE)), true);
			self::registerItem(new WoodenPressurePlate(Item::BIRCH_PRESSURE_PLATE, 0, BlockFactory::get(Block::BIRCH_PRESSURE_PLATE)), true);
			self::registerItem(new WoodenPressurePlate(Item::JUNGLE_PRESSURE_PLATE, 0, BlockFactory::get(Block::JUNGLE_PRESSURE_PLATE)), true);
			self::registerItem(new WoodenPressurePlate(Item::ACACIA_PRESSURE_PLATE, 0, BlockFactory::get(Block::ACACIA_PRESSURE_PLATE)), true);
			self::registerItem(new WoodenPressurePlate(Item::DARK_OAK_PRESSURE_PLATE, 0, BlockFactory::get(Block::DARK_OAK_PRESSURE_PLATE)), true);
			self::registerItem(new WoodenPressurePlate(Item::MANGROVE_PRESSURE_PLATE, 0, BlockFactory::get(Block::MANGROVE_PRESSURE_PLATE)), true);
			self::registerItem(new WoodenPressurePlate(Item::BAMBOO_PRESSURE_PLATE, 0, BlockFactory::get(Block::BAMBOO_PRESSURE_PLATE)), true);
			self::registerItem(new WoodenPressurePlate(Item::PALE_OAK_PRESSURE_PLATE, 0, BlockFactory::get(Block::PALE_OAK_PRESSURE_PLATE)), true);
			self::registerItem(new WritableBook());
			self::registerItem(new WrittenBook());

			self::registerItem(new Skull(Item::SKULL, TileSkull::TYPE_SKELETON, Block::get(Block::SKULL_BLOCK, BlockSkull::TYPE_SKELETON)));
			self::registerItem(new Skull(Item::SKULL, TileSkull::TYPE_WITHER_SKELETON, Block::get(Block::SKULL_BLOCK, BlockSkull::TYPE_WITHER_SKELETON)));
			self::registerItem(new Skull(Item::SKULL, TileSkull::TYPE_ZOMBIE, Block::get(Block::SKULL_BLOCK, BlockSkull::TYPE_ZOMBIE)));
			self::registerItem(new Skull(Item::SKULL, TileSkull::TYPE_PLAYER, Block::get(Block::SKULL_BLOCK, BlockSkull::TYPE_PLAYER)));
			self::registerItem(new Skull(Item::SKULL, TileSkull::TYPE_CREEPER, Block::get(Block::SKULL_BLOCK, BlockSkull::TYPE_CREEPER)));
			self::registerItem(new Skull(Item::SKULL, TileSkull::TYPE_DRAGON, Block::get(Block::SKULL_BLOCK, BlockSkull::TYPE_DRAGON)));
			self::registerItem(new Skull(Item::SKULL, TileSkull::TYPE_PIGLIN, Block::get(Block::SKULL_BLOCK, BlockSkull::TYPE_PIGLIN)));

			$colorsDyeNew = [
				0 => 16, //BLACK
				3 => 17, //BROWN
				4 => 18, //BLUE
				15 => 19 //WHITE
			];
			for ($color = 0; $color <= 15; $color++) {
				self::registerItem(new Dye($colorsDyeNew[$color] ?? $color));
				self::registerItem(new Bed($color));
				self::registerItem(new Banner($color));
				self::registerItem(new Fireworks($color));
				self::registerItem(new FireworksCharge($color));
			}

			for ($typePotion = 0; $typePotion <= 36; $typePotion++) {
				self::registerItem(new Potion($typePotion));
				self::registerItem(new SplashPotion($typePotion));
			}

			self::registerItem(new Boat(ItemIds::BOAT, 0, "Oak Boat"));
			self::registerItem(new Boat(ItemIds::BOAT, 1, "Spruce Boat"));
			self::registerItem(new Boat(ItemIds::BOAT, 2, "Birch Boat"));
			self::registerItem(new Boat(ItemIds::BOAT, 3, "Jungle Boat"));
			self::registerItem(new Boat(ItemIds::BOAT, 4, "Acacia Boat"));
			self::registerItem(new Boat(ItemIds::BOAT, 5, "Dark Oak Boat"));

			self::registerItem(new UndyedShulkerBox(), true);

			self::registerItem(new Wall(ItemIds::MOSSY_COBBLESTONE_WALL, 0, BlockFactory::get(BlockIds::MOSSY_COBBLESTONE_WALL), 1), true);
			self::registerItem(new Wall(ItemIds::GRANITE_WALL, 0, BlockFactory::get(BlockIds::GRANITE_WALL), 2), true);
			self::registerItem(new Wall(ItemIds::DIORITE_WALL, 0, BlockFactory::get(BlockIds::DIORITE_WALL), 3), true);
			self::registerItem(new Wall(ItemIds::ANDESITE_WALL, 0, BlockFactory::get(BlockIds::ANDESITE_WALL), 4), true);
			self::registerItem(new Wall(ItemIds::SANDSTONE_WALL, 0, BlockFactory::get(BlockIds::SANDSTONE_WALL), 5), true);
			self::registerItem(new Wall(ItemIds::BRICK_WALL, 0, BlockFactory::get(BlockIds::BRICK_WALL), 6), true);
			self::registerItem(new Wall(ItemIds::STONE_BRICK_WALL, 0, BlockFactory::get(BlockIds::STONE_BRICK_WALL), 7), true);
			self::registerItem(new Wall(ItemIds::MOSSY_STONE_BRICK_WALL, 0, BlockFactory::get(BlockIds::MOSSY_STONE_BRICK_WALL), 8), true);
			self::registerItem(new Wall(ItemIds::NETHER_BRICK_WALL, 0, BlockFactory::get(BlockIds::NETHER_BRICK_WALL), 9), true);
			self::registerItem(new Wall(ItemIds::END_STONE_BRICK_WALL, 0, BlockFactory::get(BlockIds::END_STONE_BRICK_WALL), 10), true);
			self::registerItem(new Wall(ItemIds::PRISMARINE_WALL, 0, BlockFactory::get(BlockIds::PRISMARINE_WALL), 11), true);
			self::registerItem(new Wall(ItemIds::RED_SANDSTONE_WALL, 0, BlockFactory::get(BlockIds::RED_SANDSTONE_WALL), 12), true);
			self::registerItem(new Wall(ItemIds::RED_NETHER_BRICK_WALL, 0, BlockFactory::get(BlockIds::RED_NETHER_BRICK_WALL), 13), true);
		}
	}

	/**
	 * Registers an item type into the index. Plugins may use this method to register new item types or override existing
	 * ones.
	 *
	 * NOTE: If you are registering a new item type, you will need to add it to the creative inventory yourself - it
	 * will not automatically appear there.
	 *
	 * @return void
	 * @throws RuntimeException if something attempted to override an already-registered item without specifying the
	 * $override parameter.
	 */
	public static function registerItem(Item $item, bool $override = false)
	{
		$id = $item->getId();
		$meta = $item->getDamage();

		if (!$override && self::isRegistered($id, $meta)) {
			throw new RuntimeException("Trying to overwrite an already registered item");
		}

		self::$list[self::getListOffset($id, $meta)] = clone $item;
	}

	public static function itemToBlockId(int $id) : int
	{
		return $id < 0 ? 255 - $id : $id;
	}

	/**
	 * Returns an instance of the Item with the specified id, meta, count and NBT.
	 *
	 * @param CompoundTag|string|null $tags
	 *
	 * @throws TypeError
	 */
	public static function get(int $id, int $meta = 0, int $count = 1, $tags = null) : Item
	{
		if (!is_string($tags) && !($tags instanceof CompoundTag) && $tags !== null) {
			throw new TypeError("`tags` argument must be a string or CompoundTag instance, " . (is_object($tags) ? "instance of " . get_class($tags) : gettype($tags)) . " given");
		}

		$item = null;
		if ($meta !== -1) {
			if (isset(self::$list[$offset = self::getListOffset($id, $meta)])) {
				$item = clone self::$list[$offset];
			} elseif (isset(self::$list[$zero = self::getListOffset($id, 0)]) && self::$list[$zero] instanceof Durable) {
				$item = clone self::$list[$zero];
				if ($meta >= $item->getMaxDurability()) {
					$meta = 0;
				}
				$item->setDamage($meta);
			} elseif ($id < 256) { //intentionally includes negatives, for extended block IDs
				//TODO: do not assume that item IDs and block IDs are the same or related
				$item = new ItemBlock($id, $meta & 0xf, BlockFactory::get(self::itemToBlockId($id), $meta & 0xf));
			}
		}

		if ($item === null) {
			//negative damage values will fallthru to here, to avoid crazy shit with crafting wildcard hacks
			$item = new Item($id, $meta === -1 ? -1 : 0);
		}

		$item->setCount($count);
		if ($tags !== null) {
			$item->setCompoundTag($tags);
		}

		return $item;
	}

	/**
	 * Tries to parse the specified string into Item ID/meta identifiers, and returns Item instances it created.
	 *
	 * Example accepted formats:
	 * - `diamond_pickaxe:5`
	 * - `minecraft:string`
	 * - `351:4 (lapis lazuli ID:meta)`
	 *
	 * If multiple item instances are to be created, their identifiers must be comma-separated, for example:
	 * `diamond_pickaxe,wooden_shovel:18,iron_ingot`
	 *
	 * @return Item[]|Item
	 *
	 * @throws InvalidArgumentException if the given string cannot be parsed as an item identifier
	 */
	public static function fromString(string $str, bool $multiple = false)
	{
		if ($multiple) {
			$blocks = [];
			foreach (explode(",", $str) as $b) {
				$blocks[] = self::fromStringSingle($b);
			}

			return $blocks;
		} else {
			return self::fromStringSingle($str);
		}
	}

	public static function fromStringSingle(string $str) : Item
	{
		$b = explode(":", str_replace([" ", "minecraft:"], ["_", ""], trim($str)));
		if (!isset($b[1])) {
			$meta = 0;
		} elseif (is_numeric($b[1])) {
			$meta = (int) $b[1];
		} else {
			throw new InvalidArgumentException("Unable to parse \"" . $b[1] . "\" from \"" . $str . "\" as a valid meta value");
		}

		if (is_numeric($b[0])) {
			$item = self::get((int) $b[0], $meta);
		} elseif (defined(ItemIds::class . "::" . mb_strtoupper($b[0]))) {
			$item = self::get(constant(ItemIds::class . "::" . mb_strtoupper($b[0])), $meta);
		} else {
			throw new InvalidArgumentException("Unable to resolve \"" . $str . "\" to a valid item");
		}

		return $item;
	}

	/**
	 * @deprecated
	 */
	public static function air() : Item
	{
		return ItemFactory::get(Item::AIR, 0, 0);
	}

	/**
	 * Returns whether the specified item ID is already registered in the item factory.
	 */
	public static function isRegistered(int $id, int $variant = 0) : bool
	{
		if ($id < 256) {
			return BlockFactory::isRegistered(self::itemToBlockId($id));
		}

		return isset(self::$list[self::getListOffset($id, $variant)]);
	}

	public static function getListOffset(int $id, int $variant) : int
	{
		if ($id < -0x8000 || $id > 0x7fff) {
			throw new InvalidArgumentException("ID must be in range " . -0x8000 . " - " . 0x7fff);
		}
		return (($id & 0xffff) << 16) | ($variant & 0xffff);
	}
}
