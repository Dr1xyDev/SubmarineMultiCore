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

namespace pocketmine\block;

use InvalidArgumentException;
use pocketmine\item\Item;
use pocketmine\level\Position;
use RuntimeException;
use SplFixedArray;

use function array_fill;
use function array_filter;
use function min;

/**
 * Manages block registration and instance creation
 */
class BlockFactory
{
	/**
	 * @var SplFixedArray|Block[]
	 * @phpstan-var SplFixedArray<Block>
	 */
	public static $fullList = null;

	/**
	 * @var SplFixedArray|int[]
	 * @phpstan-var SplFixedArray<int>
	 */
	private static $mappedStateIds;

	/**
	 * @var SplFixedArray|int[]
	 * @phpstan-var SplFixedArray<int>
	 */
	public static $light;
	/**
	 * @var SplFixedArray|int[]
	 * @phpstan-var SplFixedArray<int>
	 */
	public static $lightFilter;
	/**
	 * @var SplFixedArray|bool[]
	 * @phpstan-var SplFixedArray<bool>
	 */
	public static $diffusesSkyLight;
	/**
	 * @var SplFixedArray|float[]
	 * @phpstan-var SplFixedArray<float>
	 */
	public static $blastResistance;

	/** @var SplFixedArray */
	public static $hasEntityCollision = null;

	/**
	 * Initializes the block factory. By default this is called only once on server start, however you may wish to use
	 * this if you need to reset the block factory back to its original defaults for whatever reason.
	 */
	public static function init() : void
	{
		if (self::$fullList === null) {
			self::$fullList = new SplFixedArray(1280 << Block::INTERNAL_METADATA_BITS);
			self::$mappedStateIds = new SplFixedArray(1280 << Block::INTERNAL_METADATA_BITS);

			self::$light = SplFixedArray::fromArray(array_fill(0, 1280 << Block::INTERNAL_METADATA_BITS, 0));
			self::$lightFilter = SplFixedArray::fromArray(array_fill(0, 1280 << Block::INTERNAL_METADATA_BITS, 1));
			self::$diffusesSkyLight = SplFixedArray::fromArray(array_fill(0, 1280 << Block::INTERNAL_METADATA_BITS, false));
			self::$blastResistance = SplFixedArray::fromArray(array_fill(0, 1280 << Block::INTERNAL_METADATA_BITS, 0.0));
			self::$hasEntityCollision = SplFixedArray::fromArray(array_fill(0, 1280 << Block::INTERNAL_METADATA_BITS, false));

			self::registerBlock(new Air());
			self::registerBlock(new Stone());
			self::registerBlock(new Grass());
			self::registerBlock(new Dirt());
			self::registerBlock(new Cobblestone());
			self::registerBlock(new Planks());
			self::registerBlock(new Sapling());
			self::registerBlock(new Bedrock());
			self::registerBlock(new Water());
			self::registerBlock(new StillWater());
			self::registerBlock(new Lava());
			self::registerBlock(new StillLava());
			self::registerBlock(new Sand());
			self::registerBlock(new Gravel());
			self::registerBlock(new GoldOre());
			self::registerBlock(new IronOre());
			self::registerBlock(new CoalOre());
			self::registerBlock(new Log());
			self::registerBlock(new Leaves());
			self::registerBlock(new Sponge());
			self::registerBlock(new Glass());
			self::registerBlock(new LapisOre());
			self::registerBlock(new Lapis());
			self::registerBlock(new Sandstone());
			self::registerBlock(new NoteBlock());
			self::registerBlock(new Bed());
			self::registerBlock(new PoweredRail());
			self::registerBlock(new DetectorRail());
			//TODO: STICKY_PISTON
			self::registerBlock(new Cobweb());
			self::registerBlock(new TallGrass());
			self::registerBlock(new DeadBush());
			//TODO: PISTON
			//TODO: PISTONARMCOLLISION
			self::registerBlock(new Wool());
			self::registerBlock(new Element(Block::ELEMENT_0, 0, "???"));
			self::registerBlock(new Dandelion());
			self::registerBlock(new Flower());
			self::registerBlock(new BrownMushroom());
			self::registerBlock(new RedMushroom());
			self::registerBlock(new Gold());
			self::registerBlock(new Iron());
			self::registerBlock(new DoubleStoneSlab());
			self::registerBlock(new StoneSlab());
			self::registerBlock(new Bricks());
			self::registerBlock(new TNT());
			self::registerBlock(new Bookshelf());
			self::registerBlock(new MossyCobblestone());
			self::registerBlock(new Obsidian());
			self::registerBlock(new Torch());
			self::registerBlock(new Fire());
			self::registerBlock(new MonsterSpawner());
			self::registerBlock(new WoodenStairs(Block::OAK_STAIRS, 0, "Oak Stairs"));
			self::registerBlock(new Chest());
			//TODO: REDSTONE_WIRE
			self::registerBlock(new DiamondOre());
			self::registerBlock(new Diamond());
			self::registerBlock(new CraftingTable());
			self::registerBlock(new Wheat());
			self::registerBlock(new Farmland());
			self::registerBlock(new Furnace());
			self::registerBlock(new BurningFurnace());
			self::registerBlock(new SignPost(Block::SIGN_POST, 0, "Oak Sign Post", Item::SIGN, Block::WALL_SIGN));
			self::registerBlock(new WoodenDoor(Block::OAK_DOOR_BLOCK, 0, "Oak Door", Item::OAK_DOOR));
			self::registerBlock(new Ladder());
			self::registerBlock(new Rail());
			self::registerBlock(new CobblestoneStairs());
			self::registerBlock(new WallSign(Block::WALL_SIGN, 0, "Oak Sign Post", Item::SIGN, Block::WALL_SIGN));
			self::registerBlock(new Lever());
			self::registerBlock(new StonePressurePlate(Block::STONE_PRESSURE_PLATE, 0, "Stone Pressure Plate"));
			self::registerBlock(new IronDoor());
			self::registerBlock(new WoodenPressurePlate(Block::WOODEN_PRESSURE_PLATE, 0, "Wooden Pressure Plate"));
			self::registerBlock(new RedstoneOre());
			self::registerBlock(new GlowingRedstoneOre());
			self::registerBlock(new RedstoneTorchUnlit());
			self::registerBlock(new RedstoneTorch());
			self::registerBlock(new StoneButton(Block::STONE_BUTTON, 0, "Stone Button"));
			self::registerBlock(new SnowLayer());
			self::registerBlock(new Ice());
			self::registerBlock(new Snow());
			self::registerBlock(new Cactus());
			self::registerBlock(new Clay());
			self::registerBlock(new Sugarcane());
			self::registerBlock(new Jukebox());
			self::registerBlock(new WoodenFence());
			self::registerBlock(new Pumpkin());
			self::registerBlock(new Netherrack());
			self::registerBlock(new SoulSand());
			self::registerBlock(new Glowstone());
			self::registerBlock(new NetherPortal());
			self::registerBlock(new LitPumpkin());
			self::registerBlock(new Cake());
			//TODO: REPEATER_BLOCK
			//TODO: POWERED_REPEATER
			self::registerBlock(new InvisibleBedrock());
			self::registerBlock(new WoodenTrapdoor(Block::WOODEN_TRAPDOOR, 0, "Wooden Trapdoor"));
			self::registerBlock(new InfestedStone());
			self::registerBlock(new StoneBricks());
			self::registerBlock(new BrownMushroomBlock());
			self::registerBlock(new RedMushroomBlock());
			self::registerBlock(new IronBars());
			self::registerBlock(new GlassPane());
			self::registerBlock(new Melon());
			self::registerBlock(new PumpkinStem());
			self::registerBlock(new MelonStem());
			self::registerBlock(new Vine());
			self::registerBlock(new FenceGate(Block::OAK_FENCE_GATE, 0, "Oak Fence Gate"));
			self::registerBlock(new BrickStairs());
			self::registerBlock(new StoneBrickStairs());
			self::registerBlock(new Mycelium());
			self::registerBlock(new WaterLily());
			self::registerBlock(new NetherBrick(Block::NETHER_BRICK_BLOCK, 0, "Nether Bricks"));
			self::registerBlock(new NetherBrickFence());
			self::registerBlock(new NetherBrickStairs());
			self::registerBlock(new NetherWartPlant());
			self::registerBlock(new EnchantingTable());
			self::registerBlock(new BrewingStand());
			self::registerBlock(new Cauldron());
			self::registerBlock(new EndPortal());
			self::registerBlock(new EndPortalFrame());
			self::registerBlock(new EndStone());
			self::registerBlock(new DragonEgg());
			self::registerBlock(new RedstoneLamp());
			self::registerBlock(new LitRedstoneLamp());
			//TODO: DROPPER
			self::registerBlock(new ActivatorRail());
			self::registerBlock(new CocoaBlock());
			self::registerBlock(new SandstoneStairs());
			self::registerBlock(new EmeraldOre());
			self::registerBlock(new EnderChest());
			self::registerBlock(new TripwireHook());
			self::registerBlock(new Tripwire());
			self::registerBlock(new Emerald());
			self::registerBlock(new WoodenStairs(Block::SPRUCE_STAIRS, 0, "Spruce Stairs"));
			self::registerBlock(new WoodenStairs(Block::BIRCH_STAIRS, 0, "Birch Stairs"));
			self::registerBlock(new WoodenStairs(Block::JUNGLE_STAIRS, 0, "Jungle Stairs"));
			//TODO: COMMAND_BLOCK
			self::registerBlock(new Beacon());
			self::registerBlock(new CobblestoneWall());
			self::registerBlock(new FlowerPot());
			self::registerBlock(new Carrot());
			self::registerBlock(new Potato());
			self::registerBlock(new WoodenButton(Block::WOODEN_BUTTON, 0, "Wooden Button"));
			self::registerBlock(new Skull());
			self::registerBlock(new Anvil());
			self::registerBlock(new TrappedChest());
			self::registerBlock(new WeightedPressurePlateLight());
			self::registerBlock(new WeightedPressurePlateHeavy());
			//TODO: COMPARATOR_BLOCK
			//TODO: POWERED_COMPARATOR
			self::registerBlock(new DaylightSensor());
			self::registerBlock(new Redstone());
			self::registerBlock(new NetherQuartzOre());
			self::registerBlock(new Hopper());
			self::registerBlock(new Quartz());
			self::registerBlock(new QuartzStairs());
			self::registerBlock(new DoubleWoodenSlab());
			self::registerBlock(new WoodenSlab());
			self::registerBlock(new StainedHardenedClay());
			self::registerBlock(new StainedGlassPane());
			self::registerBlock(new Leaves2());
			self::registerBlock(new Log2());
			self::registerBlock(new WoodenStairs(Block::ACACIA_STAIRS, 0, "Acacia Stairs"));
			self::registerBlock(new WoodenStairs(Block::DARK_OAK_STAIRS, 0, "Dark Oak Stairs"));
			self::registerBlock(new Slime());
			self::registerBlock(new IronTrapdoor(Block::IRON_TRAPDOOR, 0, "Iron Trapdoor"));
			self::registerBlock(new Prismarine());
			self::registerBlock(new SeaLantern());
			self::registerBlock(new HayBale());
			self::registerBlock(new Carpet());
			self::registerBlock(new HardenedClay());
			self::registerBlock(new Coal());
			self::registerBlock(new PackedIce());
			self::registerBlock(new DoublePlant());
			self::registerBlock(new StandingBanner());
			self::registerBlock(new WallBanner());
			//TODO: DAYLIGHT_DETECTOR_INVERTED
			self::registerBlock(new RedSandstone());
			self::registerBlock(new RedSandstoneStairs());
			self::registerBlock(new DoubleStoneSlab2());
			self::registerBlock(new StoneSlab2());
			self::registerBlock(new FenceGate(Block::SPRUCE_FENCE_GATE, 0, "Spruce Fence Gate"));
			self::registerBlock(new FenceGate(Block::BIRCH_FENCE_GATE, 0, "Birch Fence Gate"));
			self::registerBlock(new FenceGate(Block::JUNGLE_FENCE_GATE, 0, "Jungle Fence Gate"));
			self::registerBlock(new FenceGate(Block::DARK_OAK_FENCE_GATE, 0, "Dark Oak Fence Gate"));
			self::registerBlock(new FenceGate(Block::ACACIA_FENCE_GATE, 0, "Acacia Fence Gate"));
			//TODO: REPEATING_COMMAND_BLOCK
			//TODO: CHAIN_COMMAND_BLOCK
			//TODO: HARD_GRASS_PANE
			//TODO: HARD_STAINED_GLASS_PANE
			//TODO: CHEMICAL_HEAT
			self::registerBlock(new WoodenDoor(Block::SPRUCE_DOOR_BLOCK, 0, "Spruce Door", Item::SPRUCE_DOOR));
			self::registerBlock(new WoodenDoor(Block::BIRCH_DOOR_BLOCK, 0, "Birch Door", Item::BIRCH_DOOR));
			self::registerBlock(new WoodenDoor(Block::JUNGLE_DOOR_BLOCK, 0, "Jungle Door", Item::JUNGLE_DOOR));
			self::registerBlock(new WoodenDoor(Block::ACACIA_DOOR_BLOCK, 0, "Acacia Door", Item::ACACIA_DOOR));
			self::registerBlock(new WoodenDoor(Block::DARK_OAK_DOOR_BLOCK, 0, "Dark Oak Door", Item::DARK_OAK_DOOR));
			self::registerBlock(new GrassPath());
			self::registerBlock(new ItemFrame());
			//TODO: CHORUS_FLOWER
			self::registerBlock(new Purpur());
			//TODO: COLORED_TORCH_RG
			self::registerBlock(new PurpurStairs());
			self::registerBlock(new UndyedShulkerBox());
			self::registerBlock(new EndBricks());
			self::registerBlock(new FrostedIce());
			self::registerBlock(new EndRod());
			//TODO: END_GATEWAY
			//TODO: ALLOW
			//TODO: DENY
			//TODO: BORDER_BLOCK
			self::registerBlock(new Magma());
			self::registerBlock(new NetherWartBlock());
			self::registerBlock(new NetherBrick(Block::RED_NETHER_BRICK, 0, "Red Nether Bricks"));
			self::registerBlock(new BoneBlock());
			self::registerBlock(new ShulkerBox());
			self::registerBlock(new GlazedTerracotta(Block::PURPLE_GLAZED_TERRACOTTA, 0, "Purple Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(Block::WHITE_GLAZED_TERRACOTTA, 0, "White Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(Block::ORANGE_GLAZED_TERRACOTTA, 0, "Orange Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(Block::MAGENTA_GLAZED_TERRACOTTA, 0, "Magenta Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(Block::LIGHT_BLUE_GLAZED_TERRACOTTA, 0, "Light Blue Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(Block::YELLOW_GLAZED_TERRACOTTA, 0, "Yellow Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(Block::LIME_GLAZED_TERRACOTTA, 0, "Lime Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(Block::PINK_GLAZED_TERRACOTTA, 0, "Pink Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(Block::GRAY_GLAZED_TERRACOTTA, 0, "Grey Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(Block::SILVER_GLAZED_TERRACOTTA, 0, "Light Grey Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(Block::CYAN_GLAZED_TERRACOTTA, 0, "Cyan Glazed Terracotta"));

			self::registerBlock(new GlazedTerracotta(Block::BLUE_GLAZED_TERRACOTTA, 0, "Blue Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(Block::BROWN_GLAZED_TERRACOTTA, 0, "Brown Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(Block::GREEN_GLAZED_TERRACOTTA, 0, "Green Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(Block::RED_GLAZED_TERRACOTTA, 0, "Red Glazed Terracotta"));
			self::registerBlock(new GlazedTerracotta(Block::BLACK_GLAZED_TERRACOTTA, 0, "Black Glazed Terracotta"));
			self::registerBlock(new Concrete());
			self::registerBlock(new ConcretePowder());
			//TODO: CHEMISTRY_TABLE
			//TODO: UNDERWATER_TORCH
			//TODO: CHORUS_PLANT
			self::registerBlock(new StainedGlass());
			//TODO: CAMERA
			self::registerBlock(new Podzol());
			self::registerBlock(new Beetroot());
			self::registerBlock(new Stonecutter());
			self::registerBlock(new GlowingObsidian());
			self::registerBlock(new NetherReactor());
			self::registerBlock(new InfoUpdate(Block::INFO_UPDATE, 0, "update!"));
			self::registerBlock(new InfoUpdate(Block::INFO_UPDATE2, 0, "ate!upd"));
			//TODO: MOVINGBLOCK
			//TODO: OBSERVER
			//TODO: STRUCTURE_BLOCK
			//TODO: HARD_GLASS
			//TODO: HARD_STAINED_GLASS
			self::registerBlock(new Reserved6(Block::RESERVED6, 0, "reserved6"));

			self::registerBlock(new PrismarineStairs());
			self::registerBlock(new DarkPrismarineStairs());
			self::registerBlock(new PrismarineBricksStairs());
			self::registerBlock(new StrippedLog(Block::STRIPPED_SPRUCE_LOG, 0, "Spruce"));
			self::registerBlock(new StrippedLog(Block::STRIPPED_BIRCH_LOG, 0, "Birch"));
			self::registerBlock(new StrippedLog(Block::STRIPPED_JUNGLE_LOG, 0, "Jungle"));
			self::registerBlock(new StrippedLog(Block::STRIPPED_ACACIA_LOG, 0, "Acacia"));
			self::registerBlock(new StrippedLog(Block::STRIPPED_DARK_OAK_LOG, 0, "Dark Oak"));
			self::registerBlock(new StrippedLog(Block::STRIPPED_OAK_LOG, 0, "Oak"));
			self::registerBlock(new BlueIce());
			self::registerBlock(new Element(Block::ELEMENT_1, 0, "Hydrogen"));
			self::registerBlock(new Element(Block::ELEMENT_2, 0, "Helium"));
			self::registerBlock(new Element(Block::ELEMENT_3, 0, "Lithium"));
			self::registerBlock(new Element(Block::ELEMENT_4, 0, "Beryllium"));
			self::registerBlock(new Element(Block::ELEMENT_5, 0, "Boron"));
			self::registerBlock(new Element(Block::ELEMENT_6, 0, "Carbon"));
			self::registerBlock(new Element(Block::ELEMENT_7, 0, "Nitrogen"));
			self::registerBlock(new Element(Block::ELEMENT_8, 0, "Oxygen"));
			self::registerBlock(new Element(Block::ELEMENT_9, 0, "Fluorine"));
			self::registerBlock(new Element(Block::ELEMENT_10, 0, "Neon"));
			self::registerBlock(new Element(Block::ELEMENT_11, 0, "Sodium"));
			self::registerBlock(new Element(Block::ELEMENT_12, 0, "Magnesium"));
			self::registerBlock(new Element(Block::ELEMENT_13, 0, "Aluminum"));
			self::registerBlock(new Element(Block::ELEMENT_14, 0, "Silicon"));
			self::registerBlock(new Element(Block::ELEMENT_15, 0, "Phosphorus"));
			self::registerBlock(new Element(Block::ELEMENT_16, 0, "Sulfur"));
			self::registerBlock(new Element(Block::ELEMENT_17, 0, "Chlorine"));
			self::registerBlock(new Element(Block::ELEMENT_18, 0, "Argon"));
			self::registerBlock(new Element(Block::ELEMENT_19, 0, "Potassium"));
			self::registerBlock(new Element(Block::ELEMENT_20, 0, "Calcium"));
			self::registerBlock(new Element(Block::ELEMENT_21, 0, "Scandium"));
			self::registerBlock(new Element(Block::ELEMENT_22, 0, "Titanium"));
			self::registerBlock(new Element(Block::ELEMENT_23, 0, "Vanadium"));
			self::registerBlock(new Element(Block::ELEMENT_24, 0, "Chromium"));
			self::registerBlock(new Element(Block::ELEMENT_25, 0, "Manganese"));
			self::registerBlock(new Element(Block::ELEMENT_26, 0, "Iron"));
			self::registerBlock(new Element(Block::ELEMENT_27, 0, "Cobalt"));
			self::registerBlock(new Element(Block::ELEMENT_28, 0, "Nickel"));
			self::registerBlock(new Element(Block::ELEMENT_29, 0, "Copper"));
			self::registerBlock(new Element(Block::ELEMENT_30, 0, "Zinc"));
			self::registerBlock(new Element(Block::ELEMENT_31, 0, "Gallium"));
			self::registerBlock(new Element(Block::ELEMENT_32, 0, "Germanium"));
			self::registerBlock(new Element(Block::ELEMENT_33, 0, "Arsenic"));
			self::registerBlock(new Element(Block::ELEMENT_34, 0, "Selenium"));
			self::registerBlock(new Element(Block::ELEMENT_35, 0, "Bromine"));
			self::registerBlock(new Element(Block::ELEMENT_36, 0, "Krypton"));
			self::registerBlock(new Element(Block::ELEMENT_37, 0, "Rubidium"));
			self::registerBlock(new Element(Block::ELEMENT_38, 0, "Strontium"));
			self::registerBlock(new Element(Block::ELEMENT_39, 0, "Yttrium"));
			self::registerBlock(new Element(Block::ELEMENT_40, 0, "Zirconium"));
			self::registerBlock(new Element(Block::ELEMENT_41, 0, "Niobium"));
			self::registerBlock(new Element(Block::ELEMENT_42, 0, "Molybdenum"));
			self::registerBlock(new Element(Block::ELEMENT_43, 0, "Technetium"));
			self::registerBlock(new Element(Block::ELEMENT_44, 0, "Ruthenium"));
			self::registerBlock(new Element(Block::ELEMENT_45, 0, "Rhodium"));
			self::registerBlock(new Element(Block::ELEMENT_46, 0, "Palladium"));
			self::registerBlock(new Element(Block::ELEMENT_47, 0, "Silver"));
			self::registerBlock(new Element(Block::ELEMENT_48, 0, "Cadmium"));
			self::registerBlock(new Element(Block::ELEMENT_49, 0, "Indium"));
			self::registerBlock(new Element(Block::ELEMENT_50, 0, "Tin"));
			self::registerBlock(new Element(Block::ELEMENT_51, 0, "Antimony"));
			self::registerBlock(new Element(Block::ELEMENT_52, 0, "Tellurium"));
			self::registerBlock(new Element(Block::ELEMENT_53, 0, "Iodine"));
			self::registerBlock(new Element(Block::ELEMENT_54, 0, "Xenon"));
			self::registerBlock(new Element(Block::ELEMENT_55, 0, "Cesium"));
			self::registerBlock(new Element(Block::ELEMENT_56, 0, "Barium"));
			self::registerBlock(new Element(Block::ELEMENT_57, 0, "Lanthanum"));
			self::registerBlock(new Element(Block::ELEMENT_58, 0, "Cerium"));
			self::registerBlock(new Element(Block::ELEMENT_59, 0, "Praseodymium"));
			self::registerBlock(new Element(Block::ELEMENT_60, 0, "Neodymium"));
			self::registerBlock(new Element(Block::ELEMENT_61, 0, "Promethium"));
			self::registerBlock(new Element(Block::ELEMENT_62, 0, "Samarium"));
			self::registerBlock(new Element(Block::ELEMENT_63, 0, "Europium"));
			self::registerBlock(new Element(Block::ELEMENT_64, 0, "Gadolinium"));
			self::registerBlock(new Element(Block::ELEMENT_65, 0, "Terbium"));
			self::registerBlock(new Element(Block::ELEMENT_66, 0, "Dysprosium"));
			self::registerBlock(new Element(Block::ELEMENT_67, 0, "Holmium"));
			self::registerBlock(new Element(Block::ELEMENT_68, 0, "Erbium"));
			self::registerBlock(new Element(Block::ELEMENT_69, 0, "Thulium"));
			self::registerBlock(new Element(Block::ELEMENT_70, 0, "Ytterbium"));
			self::registerBlock(new Element(Block::ELEMENT_71, 0, "Lutetium"));
			self::registerBlock(new Element(Block::ELEMENT_72, 0, "Hafnium"));
			self::registerBlock(new Element(Block::ELEMENT_73, 0, "Tantalum"));
			self::registerBlock(new Element(Block::ELEMENT_74, 0, "Tungsten"));
			self::registerBlock(new Element(Block::ELEMENT_75, 0, "Rhenium"));
			self::registerBlock(new Element(Block::ELEMENT_76, 0, "Osmium"));
			self::registerBlock(new Element(Block::ELEMENT_77, 0, "Iridium"));
			self::registerBlock(new Element(Block::ELEMENT_78, 0, "Platinum"));
			self::registerBlock(new Element(Block::ELEMENT_79, 0, "Gold"));
			self::registerBlock(new Element(Block::ELEMENT_80, 0, "Mercury"));
			self::registerBlock(new Element(Block::ELEMENT_81, 0, "Thallium"));
			self::registerBlock(new Element(Block::ELEMENT_82, 0, "Lead"));
			self::registerBlock(new Element(Block::ELEMENT_83, 0, "Bismuth"));
			self::registerBlock(new Element(Block::ELEMENT_84, 0, "Polonium"));
			self::registerBlock(new Element(Block::ELEMENT_85, 0, "Astatine"));
			self::registerBlock(new Element(Block::ELEMENT_86, 0, "Radon"));
			self::registerBlock(new Element(Block::ELEMENT_87, 0, "Francium"));
			self::registerBlock(new Element(Block::ELEMENT_88, 0, "Radium"));
			self::registerBlock(new Element(Block::ELEMENT_89, 0, "Actinium"));
			self::registerBlock(new Element(Block::ELEMENT_90, 0, "Thorium"));
			self::registerBlock(new Element(Block::ELEMENT_91, 0, "Protactinium"));
			self::registerBlock(new Element(Block::ELEMENT_92, 0, "Uranium"));
			self::registerBlock(new Element(Block::ELEMENT_93, 0, "Neptunium"));
			self::registerBlock(new Element(Block::ELEMENT_94, 0, "Plutonium"));
			self::registerBlock(new Element(Block::ELEMENT_95, 0, "Americium"));
			self::registerBlock(new Element(Block::ELEMENT_96, 0, "Curium"));
			self::registerBlock(new Element(Block::ELEMENT_97, 0, "Berkelium"));
			self::registerBlock(new Element(Block::ELEMENT_98, 0, "Californium"));
			self::registerBlock(new Element(Block::ELEMENT_99, 0, "Einsteinium"));
			self::registerBlock(new Element(Block::ELEMENT_100, 0, "Fermium"));
			self::registerBlock(new Element(Block::ELEMENT_101, 0, "Mendelevium"));
			self::registerBlock(new Element(Block::ELEMENT_102, 0, "Nobelium"));
			self::registerBlock(new Element(Block::ELEMENT_103, 0, "Lawrencium"));
			self::registerBlock(new Element(Block::ELEMENT_104, 0, "Rutherfordium"));
			self::registerBlock(new Element(Block::ELEMENT_105, 0, "Dubnium"));
			self::registerBlock(new Element(Block::ELEMENT_106, 0, "Seaborgium"));
			self::registerBlock(new Element(Block::ELEMENT_107, 0, "Bohrium"));
			self::registerBlock(new Element(Block::ELEMENT_108, 0, "Hassium"));
			self::registerBlock(new Element(Block::ELEMENT_109, 0, "Meitnerium"));
			self::registerBlock(new Element(Block::ELEMENT_110, 0, "Darmstadtium"));
			self::registerBlock(new Element(Block::ELEMENT_111, 0, "Roentgenium"));
			self::registerBlock(new Element(Block::ELEMENT_112, 0, "Copernicium"));
			self::registerBlock(new Element(Block::ELEMENT_113, 0, "Nihonium"));
			self::registerBlock(new Element(Block::ELEMENT_114, 0, "Flerovium"));
			self::registerBlock(new Element(Block::ELEMENT_115, 0, "Moscovium"));
			self::registerBlock(new Element(Block::ELEMENT_116, 0, "Livermorium"));
			self::registerBlock(new Element(Block::ELEMENT_117, 0, "Tennessine"));
			self::registerBlock(new Element(Block::ELEMENT_118, 0, "Oganesson"));
			//TODO: SEAGRASS
			//TODO: CORAL
			//TODO: CORAL_BLOCK
			//TODO: CORAL_FAN
			//TODO: CORAL_FAN_DEAD
			//TODO: CORAL_FAN_HANG
			//TODO: CORAL_FAN_HANG2
			//TODO: CORAL_FAN_HANG3
			//TODO: KELP
			//TODO: DRIED_KELP_BLOCK
			self::registerBlock(new WoodenButton(Block::ACACIA_BUTTON, 0, "Acacia Button"));
			self::registerBlock(new WoodenButton(Block::BIRCH_BUTTON, 0, "Birch Button"));
			self::registerBlock(new WoodenButton(Block::DARK_OAK_BUTTON, 0, "Dark Oak Button"));
			self::registerBlock(new WoodenButton(Block::JUNGLE_BUTTON, 0, "Jungle Button"));
			self::registerBlock(new WoodenButton(Block::SPRUCE_BUTTON, 0, "Spruce Button"));
			self::registerBlock(new WoodenTrapdoor(Block::ACACIA_TRAPDOOR, 0, "Acacia Trapdoor"));
			self::registerBlock(new WoodenTrapdoor(Block::BIRCH_TRAPDOOR, 0, "Birch Trapdoor"));
			self::registerBlock(new WoodenTrapdoor(Block::DARK_OAK_TRAPDOOR, 0, "Dark Oak Trapdoor"));
			self::registerBlock(new WoodenTrapdoor(Block::JUNGLE_TRAPDOOR, 0, "Jungle Trapdoor"));
			self::registerBlock(new WoodenTrapdoor(Block::SPRUCE_TRAPDOOR, 0, "Spruce Trapdoor"));
			self::registerBlock(new WoodenPressurePlate(Block::ACACIA_PRESSURE_PLATE, 0, "Acacia Pressure Plate"));
			self::registerBlock(new WoodenPressurePlate(Block::BIRCH_PRESSURE_PLATE, 0, "Birch Pressure Plate"));
			self::registerBlock(new WoodenPressurePlate(Block::DARK_OAK_PRESSURE_PLATE, 0, "Dark Oak Pressure Plate"));
			self::registerBlock(new WoodenPressurePlate(Block::JUNGLE_PRESSURE_PLATE, 0, "Jungle Pressure Plate"));
			self::registerBlock(new WoodenPressurePlate(Block::SPRUCE_PRESSURE_PLATE, 0, "Spruce Pressure Plate"));
			self::registerBlock(new CarvedPumpkin());
			//TODO: SEA_PICKLE
			//TODO: CONDUIT
			//TODO: TURTLE_EGG
			//TODO: BUBBLE_COLUMN
			self::registerBlock(new Barrier());
			self::registerBlock(new StoneSlab3());
			//TODO: BAMBOO
			//TODO: BAMBOO_SAPLING
			//TODO: SCAFFOLDING
			self::registerBlock(new StoneSlab4());
			self::registerBlock(new DoubleStoneSlab3());
			self::registerBlock(new DoubleStoneSlab4());
			self::registerBlock(new GraniteStairs());
			self::registerBlock(new DioriteStairs());
			self::registerBlock(new AndesiteStairs());
			self::registerBlock(new PolishedGraniteStairs());
			self::registerBlock(new PolishedDioriteStairs());
			self::registerBlock(new PolishedAndesiteStairs());
			self::registerBlock(new MossyStoneBrickStairs());
			self::registerBlock(new SmoothRedSandstoneStairs());
			self::registerBlock(new SmoothSandstoneStairs());
			self::registerBlock(new EndBrickStairs());
			self::registerBlock(new MossyCobblestoneStairs());
			self::registerBlock(new NormalStoneStairs());
			self::registerBlock(new SignPost(Block::SPRUCE_STANDING_SIGN, 0, "Spruce Sign Post", Item::SPRUCE_SIGN, Block::SPRUCE_WALL_SIGN));
			self::registerBlock(new WallSign(Block::SPRUCE_WALL_SIGN, 0, "Spruce Sign Post", Item::SPRUCE_SIGN, Block::SPRUCE_WALL_SIGN));
			self::registerBlock(new SmoothStone());
			self::registerBlock(new RedNetherBrickStairs());
			self::registerBlock(new SmoothQuartzStairs());
			self::registerBlock(new SignPost(Block::BIRCH_STANDING_SIGN, 0, "Birch Sign Post", Item::BIRCH_SIGN, Block::BIRCH_WALL_SIGN));
			self::registerBlock(new WallSign(Block::BIRCH_WALL_SIGN, 0, "Birch Sign Post", Item::BIRCH_SIGN, Block::BIRCH_WALL_SIGN));
			self::registerBlock(new SignPost(Block::JUNGLE_STANDING_SIGN, 0, "Jungle Sign Post", Item::JUNGLE_SIGN, Block::JUNGLE_WALL_SIGN));
			self::registerBlock(new WallSign(Block::JUNGLE_WALL_SIGN, 0, "Jungle Sign Post", Item::JUNGLE_SIGN, Block::JUNGLE_WALL_SIGN));
			self::registerBlock(new SignPost(Block::ACACIA_STANDING_SIGN, 0, "Acacia Sign Post", Item::ACACIA_SIGN, Block::ACACIA_WALL_SIGN));
			self::registerBlock(new WallSign(Block::ACACIA_WALL_SIGN, 0, "Acacia Sign Post", Item::ACACIA_SIGN, Block::ACACIA_WALL_SIGN));
			self::registerBlock(new SignPost(Block::DARKOAK_STANDING_SIGN, 0, "Darkoak Sign Post", Item::DARKOAK_SIGN, Block::DARKOAK_WALL_SIGN));
			self::registerBlock(new WallSign(Block::DARKOAK_WALL_SIGN, 0, "Darkoak Sign Post", Item::DARKOAK_SIGN, Block::DARKOAK_WALL_SIGN));
			//TODO: LECTERN
			//TODO: GRINDSTONE
			//TODO: BLAST_FURNACE
			//TODO: STONECUTTER_BLOCK
			//TODO: SMOKER
			//TODO: LIT_SMOKER
			//TODO: CARTOGRAPHY_TABLE
			//TODO: FLETCHING_TABLE
			//TODO: SMITHING_TABLE
			//TODO: BARREL
			//TODO: LOOM
			//TODO: BELL
			//TODO: SWEET_BERRY_BUSH
			self::registerBlock(new Lantern());
			//TODO: CAMPFIRE
			//TODO: LAVA_CAULDRON
			//TODO: JIGSAW
			self::registerBlock(new Wood());
			//TODO: COMPOSTER
			//TODO: LIT_BLAST_FURNACE
			//TODO: LIGHT_BLOCK
			//TODO: WITHER_ROSE
			//TODO: STICKY_PISTON_ARM_COLLISION
			//TODO: BEE_NEST
			//TODO: BEEHIVE
			//TODO: HONEY_BLOCK
			//TODO: HONEYCOMB_BLOCK
			//TODO: LODESTONE
			//TODO: CRIMSON_ROOTS
			//TODO: WARPED_ROOTS
			self::registerBlock(new CrimsonStem());
			self::registerBlock(new WarpedStem());
			//TODO: WARPED_WART_BLOCK
			//TODO: CRIMSON_FUNGUS
			//TODO: WARPED_FUNGUS
			//TODO: SHROOMLIGHT
			//TODO: WEEPING_VINES
			//TODO: CRIMSON_NYLIUM
			//TODO: WARPED_NYLIUM
			//TODO: BASALT
			//TODO: POLISHED_BASALT
			//TODO: SOUL_SOIL
			self::registerBlock(new SoulFire());
			//TODO: NETHER_SPROUTS
			//TODO: TARGET
			self::registerBlock(new StrippedCrimsonStem(BlockIds::STRIPPED_CRIMSON_STEM, 0, "Crimson"));
			self::registerBlock(new StrippedWarpedStem(BlockIds::STRIPPED_WARPED_STEM, 0, "Warped"));
			self::registerBlock(new CrimsonPlanks());
			self::registerBlock(new WarpedPlanks());
			self::registerBlock(new CrimsonDoor());
			self::registerBlock(new WarpedDoor());
			self::registerBlock(new CrimsonTrapdoor());
			self::registerBlock(new WarpedTrapdoor());
			self::registerBlock(new CrimsonSignPost());
			self::registerBlock(new WarpedSignPost());
			self::registerBlock(new CrimsonWallSign());
			self::registerBlock(new WarpedWallSign());
			self::registerBlock(new CrimsonStairs());
			self::registerBlock(new WarpedStairs());
			self::registerBlock(new CrimsonFence());
			self::registerBlock(new WarpedFence());
			self::registerBlock(new CrimsonFenceGate());
			self::registerBlock(new WarpedFenceGate());
			self::registerBlock(new CrimsonButton());
			self::registerBlock(new WarpedButton());
			self::registerBlock(new CrimsonPressurePlate());
			self::registerBlock(new WarpedPressurePlate());
			self::registerBlock(new CrimsonSlab());
			self::registerBlock(new WarpedSlab());
			self::registerBlock(new CrimsonDoubleSlab());
			self::registerBlock(new WarpedDoubleSlab());
			self::registerBlock(new SoulTorch());
			self::registerBlock(new SoulLantern());
			self::registerBlock(new NetheriteBlock());
			self::registerBlock(new AncientDebris());
			self::registerBlock(new RespawnAnchor());
			self::registerBlock(new Blackstone(BlockIds::BLACKSTONE, 0, "Blackstone"));
			self::registerBlock(new Blackstone(BlockIds::POLISHED_BLACKSTONE_BRICKS, 0, "Polished Blackstone Bricks"));
			self::registerBlock(new BlackstoneStairs(BlockIds::POLISHED_BLACKSTONE_BRICK_STAIRS, 0, "Polished Blackstone Brick Stairs"));
			self::registerBlock(new BlackstoneStairs(BlockIds::BLACKSTONE_STAIRS, 0, "Blackstone Stairs"));
			//TODO: BLACKSTONE_WALL
			//TODO: POLISHED_BLACKSTONE_BRICK_WALL
			self::registerBlock(new Blackstone(BlockIds::CHISELED_POLISHED_BLACKSTONE, 0, "Chiseled Polished Blackstone"));
			self::registerBlock(new Blackstone(BlockIds::CRACKED_POLISHED_BLACKSTONE_BRICKS, 0, "Cracked Chiseled Polished Blackstone"));
			self::registerBlock(new GildedBlackstone());
			self::registerBlock(new BlackstoneSlab(BlockIds::BLACKSTONE_SLAB, 0, "Blackstone Slab", BlockIds::BLACKSTONE_DOUBLE_SLAB));
			self::registerBlock(new BlackstoneDoubleSlab(BlockIds::BLACKSTONE_DOUBLE_SLAB, 0, BlockIds::BLACKSTONE_SLAB));
			self::registerBlock(new BlackstoneSlab(BlockIds::POLISHED_BLACKSTONE_BRICK_SLAB, 0, "Polished Blackstone Brick Slab", BlockIds::POLISHED_BLACKSTONE_BRICK_DOUBLE_SLAB));
			self::registerBlock(new BlackstoneDoubleSlab(BlockIds::POLISHED_BLACKSTONE_BRICK_DOUBLE_SLAB, 0, BlockIds::POLISHED_BLACKSTONE_BRICK_SLAB));
			//TODO: CHAIN
			//TODO: TWISTING_VINES
			self::registerBlock(new NetherGoldOre());
			self::registerBlock(new CryingObsidian());
			//TODO: SOUL_CAMPFIRE
			self::registerBlock(new Blackstone(BlockIds::POLISHED_BLACKSTONE, 0, "Polished Blackstone"));
			self::registerBlock(new BlackstoneStairs(BlockIds::POLISHED_BLACKSTONE_STAIRS, 0, "Polished Blackstone Stairs"));
			self::registerBlock(new BlackstoneSlab(BlockIds::POLISHED_BLACKSTONE_SLAB, 0, "Polished Blackstone Slab", BlockIds::POLISHED_BLACKSTONE_DOUBLE_SLAB));
			self::registerBlock(new BlackstoneDoubleSlab(BlockIds::POLISHED_BLACKSTONE_DOUBLE_SLAB, 0, BlockIds::POLISHED_BLACKSTONE_SLAB));
			self::registerBlock(new PolishedBlackstonePressurePlate());
			self::registerBlock(new PolishedBlackstoneButton());
			//TODO: POLISHED_BLACKSTONE_WALL
			self::registerBlock(new WarpedHyphae());
			self::registerBlock(new CrimsonHyphae());
			self::registerBlock(new StrippedWarpedHyphae());
			self::registerBlock(new StrippedCrimsonHyphae());
			self::registerBlock(new ChiseledNetherBricks());
			self::registerBlock(new CrackedNetherBricks());
			self::registerBlock(new QuartzBricks());
			//TODO: UNKNOWN
			self::registerBlock(new PowderSnow());
			self::registerBlock(new SculkSensor());
			//TODO: POINTED_DRIPSTONE
			self::registerBlock(new CopperOre());
			self::registerBlock(new LightningRod());
			self::registerBlock(new Dripstone());
			self::registerBlock(new DirtWithRoots());
			//TODO: HANGING_ROOTS
			self::registerBlock(new Moss());
			self::registerBlock(new SporeBlossom());
			//TODO: CAVE_VINES
			//TODO: BIG_DRIPLEAF
			self::registerBlock(new AzaleaLeaves());
			self::registerBlock(new AzaleaLeavesFlowered());
			self::registerBlock(new Calcite());
			self::registerBlock(new Amethyst());
			self::registerBlock(new BuddingAmethyst());
			self::registerBlock(new AmethystCluster());
			self::registerBlock(new AmethystBudLarge());
			self::registerBlock(new AmethystBudMedium());
			self::registerBlock(new AmethystBudSmall());
			self::registerBlock(new Tuff());
			self::registerBlock(new TintedGlass());
			self::registerBlock(new MossCarpet());
			//TODO: SMALL_DRIPLEAF_BLOCK
			self::registerBlock(new Azalea());
			self::registerBlock(new FloweringAzalea());
			self::registerBlock(new GlowFrame());
			self::registerBlock(new CopperBlock(BlockIds::COPPER_BLOCK, 0, "Copper"));
			self::registerBlock(new CopperBlock(BlockIds::EXPOSED_COPPER, 0, "Exposed Copper"));
			self::registerBlock(new CopperBlock(BlockIds::WEATHERED_COPPER, 0, "Weathered Copper"));
			self::registerBlock(new CopperBlock(BlockIds::OXIDIZED_COPPER, 0, "Oxidized Copper"));
			self::registerBlock(new CopperBlock(BlockIds::WAXED_COPPER, 0, "Waxed Copper"));
			self::registerBlock(new CopperBlock(BlockIds::WAXED_EXPOSED_COPPER, 0, "Waxed Exposed Copper"));
			self::registerBlock(new CopperBlock(BlockIds::WAXED_WEATHERED_COPPER, 0, "Waxed Weathered Copper"));
			self::registerBlock(new CutCopper(BlockIds::CUT_COPPER, 0, "Cut Copper"));
			self::registerBlock(new CutCopper(BlockIds::EXPOSED_CUT_COPPER, 0, "Exposed Cut Copper"));
			self::registerBlock(new CutCopper(BlockIds::WEATHERED_CUT_COPPER, 0, "Weathered Cut Copper"));
			self::registerBlock(new CutCopper(BlockIds::OXIDIZED_CUT_COPPER, 0, "Oxidized Cut Copper"));
			self::registerBlock(new CutCopper(BlockIds::WAXED_CUT_COPPER, 0, "Waxed Copper"));
			self::registerBlock(new CutCopper(BlockIds::WAXED_EXPOSED_CUT_COPPER, 0, "Waxed Exposed Cut Copper"));
			self::registerBlock(new CutCopper(BlockIds::WAXED_WEATHERED_CUT_COPPER, 0, "Waxed Weathered Cut Copper"));
			self::registerBlock(new CutCopperStairs(BlockIds::CUT_COPPER_STAIRS, 0, "Cut Copper Stairs"));
			self::registerBlock(new CutCopperStairs(BlockIds::EXPOSED_CUT_COPPER_STAIRS, 0, "Exposed Cut Copper Stairs"));
			self::registerBlock(new CutCopperStairs(BlockIds::WEATHERED_CUT_COPPER_STAIRS, 0, "Weathered Cut Copper Stairs"));
			self::registerBlock(new CutCopperStairs(BlockIds::OXIDIZED_CUT_COPPER_STAIRS, 0, "Oxidized Cut Copper Stairs"));
			self::registerBlock(new CutCopperStairs(BlockIds::WAXED_CUT_COPPER_STAIRS, 0, "Waxed Copper Stairs"));
			self::registerBlock(new CutCopperStairs(BlockIds::WAXED_EXPOSED_CUT_COPPER_STAIRS, 0, "Waxed Exposed Cut Copper Stairs"));
			self::registerBlock(new CutCopperStairs(BlockIds::WAXED_WEATHERED_CUT_COPPER_STAIRS, 0, "Waxed Weathered Cut Copper Stairs"));
			//TODO: OXIDIZED_DOUBLE_CUT_COPPER_SLAB
			self::registerBlock(new CutCopperSlab(BlockIds::CUT_COPPER_SLAB, 0, "Cut Copper Slab", BlockIds::DOUBLE_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperSlab(BlockIds::EXPOSED_CUT_COPPER_SLAB, 0, "Exposed Cut Copper Slab", BlockIds::EXPOSED_DOUBLE_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperSlab(BlockIds::WEATHERED_CUT_COPPER_SLAB, 0, "Weathered Cut Copper Slab", BlockIds::WEATHERED_DOUBLE_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperSlab(BlockIds::OXIDIZED_CUT_COPPER_SLAB, 0, "Oxidized Cut Copper Slab", BlockIds::OXIDIZED_DOUBLE_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperSlab(BlockIds::WAXED_CUT_COPPER_SLAB, 0, "Waxed Cut Copper Slab", BlockIds::WAXED_DOUBLE_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperSlab(BlockIds::WAXED_EXPOSED_CUT_COPPER_SLAB, 0, "Waxed Exposed Cut Copper Slab", BlockIds::WAXED_EXPOSED_DOUBLE_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperSlab(BlockIds::WAXED_WEATHERED_CUT_COPPER_SLAB, 0, "Waxed Weathered Cut Copper Slab", BlockIds::WAXED_WEATHERED_DOUBLE_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperDoubleSlab(BlockIds::DOUBLE_CUT_COPPER_SLAB, 0, BlockIds::CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperDoubleSlab(BlockIds::EXPOSED_DOUBLE_CUT_COPPER_SLAB, 0, BlockIds::EXPOSED_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperDoubleSlab(BlockIds::WEATHERED_DOUBLE_CUT_COPPER_SLAB, 0, BlockIds::WEATHERED_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperDoubleSlab(BlockIds::OXIDIZED_DOUBLE_CUT_COPPER_SLAB, 0, BlockIds::OXIDIZED_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperDoubleSlab(BlockIds::WAXED_DOUBLE_CUT_COPPER_SLAB, 0, BlockIds::WAXED_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperDoubleSlab(BlockIds::WAXED_EXPOSED_DOUBLE_CUT_COPPER_SLAB, 0, BlockIds::WAXED_EXPOSED_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperDoubleSlab(BlockIds::WAXED_WEATHERED_DOUBLE_CUT_COPPER_SLAB, 0, BlockIds::WAXED_WEATHERED_CUT_COPPER_SLAB));
			//TODO: CAVE_VINES_BODY_WITH_BERRIES
			//TODO: CAVE_VINES_HEAD_WITH_BERRIES
			//TODO: SMOOTH_BASALT
			//TODO: DEEPSLATE
			//TODO: COBBLED_DEEPSLATE
			//TODO: COBBLED_DEEPSLATE_SLAB
			//TODO: COBBLED_DEEPSLATE_STAIRS
			//TODO: COBBLED_DEEPSLATE_WALL
			//TODO: POLISHED_DEEPSLATE
			//TODO: POLISHED_DEEPSLATE_SLAB
			//TODO: POLISHED_DEEPSLATE_STAIRS
			//TODO: POLISHED_DEEPSLATE_WALL
			//TODO: DEEPSLATE_TILES
			//TODO: DEEPSLATE_TILE_SLAB
			//TODO: DEEPSLATE_TILE_STAIRS
			//TODO: DEEPSLATE_TILE_WALL
			//TODO: DEEPSLATE_BRICKS
			//TODO: DEEPSLATE_BRICK_SLAB
			//TODO: DEEPSLATE_BRICK_STAIRS
			//TODO: DEEPSLATE_BRICK_WALL
			//TODO: CHISELED_DEEPSLATE
			//TODO: COBBLED_DEEPSLATE_DOUBLE_SLAB
			//TODO: POLISHED_DEEPSLATE_DOUBLE_SLAB
			//TODO: DEEPSLATE_TILE_DOUBLE_SLAB
			//TODO: DEEPSLATE_BRICK_DOUBLE_SLAB
			//TODO: DEEPSLATE_LAPIS_ORE
			//TODO: DEEPSLATE_IRON_ORE
			//TODO: DEEPSLATE_GOLD_ORE
			//TODO: DEEPSLATE_REDSTONE_ORE
			//TODO: LIT_DEEPSLATE_REDSTONE_ORE
			//TODO: DEEPSLATE_DIAMOND_ORE
			//TODO: DEEPSLATE_COAL_ORE
			//TODO: DEEPSLATE_EMERALD_ORE
			//TODO: DEEPSLATE_COPPER_ORE
			//TODO: CRACKED_DEEPSLATE_TILES
			//TODO: CRACKED_DEEPSLATE_BRICKS
			//TODO: GLOW_LICHEN
			self::registerBlock(new Candle(BlockIds::CANDLE, 0, "Candle", null, BlockIds::CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::WHITE_CANDLE, 0, "White Candle", null, BlockIds::WHITE_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::ORANGE_CANDLE, 0, "Orange Candle", null, BlockIds::ORANGE_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::MAGENTA_CANDLE, 0, "Magenta Candle", null, BlockIds::MAGENTA_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::LIGHT_BLUE_CANDLE, 0, "Light Blue Candle", null, BlockIds::LIGHT_BLUE_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::YELLOW_CANDLE, 0, "Yellow Candle", null, BlockIds::YELLOW_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::LIME_CANDLE, 0, "Lime Candle", null, BlockIds::LIME_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::PINK_CANDLE, 0, "Pink Candle", null, BlockIds::PINK_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::GRAY_CANDLE, 0, "Gray Candle", null, BlockIds::GRAY_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::LIGHT_GRAY_CANDLE, 0, "Light Gray Candle", null, BlockIds::LIGHT_GRAY_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::CYAN_CANDLE, 0, "Cyan Candle", null, BlockIds::CYAN_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::PURPLE_CANDLE, 0, "Purple Candle", null, BlockIds::PURPLE_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::BLUE_CANDLE, 0, "Blue Candle", null, BlockIds::BLUE_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::BROWN_CANDLE, 0, "Brown Candle", null, BlockIds::BROWN_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::GREEN_CANDLE, 0, "Green Candle", null, BlockIds::GREEN_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::RED_CANDLE, 0, "Red Candle", null, BlockIds::RED_CANDLE_CAKE));
			self::registerBlock(new Candle(BlockIds::BLACK_CANDLE, 0, "Black Candle", null, BlockIds::BLACK_CANDLE_CAKE));
			self::registerBlock(new CandleCake(BlockIds::CANDLE_CAKE, 0, "Candle Cake", null, BlockIds::CANDLE));
			self::registerBlock(new CandleCake(BlockIds::WHITE_CANDLE_CAKE, 0, "White Candle Cake", null, BlockIds::WHITE_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::ORANGE_CANDLE_CAKE, 0, "Orange Candle Cake", null, BlockIds::ORANGE_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::MAGENTA_CANDLE_CAKE, 0, "Magenta Candle Cake", null, BlockIds::MAGENTA_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::LIGHT_BLUE_CANDLE_CAKE, 0, "Light Blue Candle Cake", null, BlockIds::LIGHT_BLUE_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::YELLOW_CANDLE_CAKE, 0, "Yellow Candle Cake", null, BlockIds::YELLOW_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::LIME_CANDLE_CAKE, 0, "Lime Candle Cake", null, BlockIds::LIME_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::PINK_CANDLE_CAKE, 0, "Pink Candle Cake", null, BlockIds::PINK_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::GRAY_CANDLE_CAKE, 0, "Gray Candle Cake", null, BlockIds::GRAY_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::LIGHT_GRAY_CANDLE_CAKE, 0, "Light Gray Candle Cake", null, BlockIds::LIGHT_GRAY_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::CYAN_CANDLE_CAKE, 0, "Cyan Candle Cake", null, BlockIds::CYAN_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::PURPLE_CANDLE_CAKE, 0, "Purple Candle Cake", null, BlockIds::PURPLE_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::BLUE_CANDLE_CAKE, 0, "Blue Candle Cake", null, BlockIds::BLUE_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::BROWN_CANDLE_CAKE, 0, "Brown Candle Cake", null, BlockIds::BROWN_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::GREEN_CANDLE_CAKE, 0, "Green Candle Cake", null, BlockIds::GREEN_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::RED_CANDLE_CAKE, 0, "Red Candle Cake", null, BlockIds::RED_CANDLE));
			self::registerBlock(new CandleCake(BlockIds::BLACK_CANDLE_CAKE, 0, "Black Candle Cake", null, BlockIds::BLACK_CANDLE));
			self::registerBlock(new CopperBlock(BlockIds::WAXED_OXIDIZED_COPPER, 0, "Waxed Oxidized Copper Door"));
			self::registerBlock(new CutCopper(BlockIds::WAXED_OXIDIZED_CUT_COPPER, 0, "Waxed Oxidized Cut Copper"));
			self::registerBlock(new CutCopperStairs(BlockIds::WAXED_OXIDIZED_CUT_COPPER_STAIRS, 0, "Waxed Oxidized Cut Copper Stairs"));
			self::registerBlock(new CutCopperSlab(BlockIds::WAXED_OXIDIZED_CUT_COPPER_SLAB, 0, "Waxed Oxidized Cut Copper Slab", BlockIds::WAXED_OXIDIZED_DOUBLE_CUT_COPPER_SLAB));
			self::registerBlock(new CutCopperDoubleSlab(BlockIds::WAXED_OXIDIZED_DOUBLE_CUT_COPPER_SLAB, 0, BlockIds::WAXED_OXIDIZED_CUT_COPPER_SLAB));
			//TODO: RAW_IRON_BLOCK
			//TODO: RAW_COPPER_BLOCK
			//TODO: RAW_GOLD_BLOCK
			//TODO: INFESTED_DEEPSLATE
			self::registerBlock(new BambooDoor());
			//TODO: SCULK
			//TODO: SCULK_VEIN
			//TODO: SCULK_CATALYST
			//TODO: SCULK_SHRIEKER

			//TODO: CLIENT_REQUEST_PLACEHOLDER_BLOCK

			//TODO: FROG_SPAWN
			//TODO: PEARLESCENT_FROGLIGHT
			//TODO: VERDANT_FROGLIGHT
			//TODO: OCHRE_FROGLIGHT
			self::registerBlock(new MangroveLeaves());
			//TODO: MANGROVE_PROPAGULE

			//TODO: MUD
			//TODO: MUD_BRICK_DOUBLE_SLAB
			//TODO: MUD_BRICK_SLAB
			//TODO: MUD_BRICK_STAIRS
			//TODO: MUD_BRICK_WALL
			//TODO: MUD_BRICKS
			//TODO: PACKED_MUD
			//TODO: REINFORCED_DEEPSLATE
			self::registerBlock(new MangroveDoor());
			self::registerBlock(new MangroveButton());
			self::registerBlock(new MangroveDoubleSlab());
			self::registerBlock(new MangroveFence());
			self::registerBlock(new MangroveFenceGate());
			self::registerBlock(new MangroveLog());
			self::registerBlock(new MangrovePlanks());
			self::registerBlock(new MangrovePressurePlate());
			//TODO: MANGROVE_ROOTS
			self::registerBlock(new MangroveSlab());
			self::registerBlock(new MangroveStairs());
			self::registerBlock(new MangroveSignPost());
			//TODO: MANGROVE_TRAPDOOR
			self::registerBlock(new MangroveWallSign());
			self::registerBlock(new MangroveWood());
			//TODO: MUDDY_MANGROVE_ROOTS
			self::registerBlock(new StrippedMangroveLog(BlockIds::STRIPPED_MANGROVE_LOG, 0, "Mangrove"));
			self::registerBlock(new StrippedMangroveWood());
			self::registerBlock(new BambooButton());
			self::registerBlock(new BambooDoubleSlab());
			self::registerBlock(new BambooFence());
			self::registerBlock(new BambooFenceGate());
			//TODO: BAMBOO_HANGING_SIGN
			self::registerBlock(new BambooMosaic());
			self::registerBlock(new BambooMosaicDoubleSlab());
			self::registerBlock(new BambooMosaicSlab());
			self::registerBlock(new BambooMosaicStairs());
			self::registerBlock(new BambooPlanks());
			self::registerBlock(new BambooPressurePlate());
			self::registerBlock(new BambooSlab());
			self::registerBlock(new BambooStairs());
			self::registerBlock(new BambooSignPost());
			self::registerBlock(new BambooWallSign());
			self::registerBlock(new BambooTrapdoor());
			//TODO: BIRCH_HANGING_SIGN
			//TODO: CHISELED_BOOKSHELF
			//TODO: CRIMSON_HANGING_SIGN
			//TODO: DARK_OAK_HANGING_SIGN
			//TODO: JUNGLE_HANGING_SIGN
			//TODO: MANGROVE_HANGING_SIGN
			//TODO: OAK_HANGING_SIGN

			//TODO: WARPED_HANGING_SIGN
			//TODO: SPRUCE_HANGING_SIGN
			self::registerBlock(new BambooBlock());
			self::registerBlock(new StrippedBambooBlock(BlockIds::STRIPPED_BAMBOO_BLOCK, 0, "Bamboo"));
			//TODO: DECORATED_POT
			//TODO: SUSPICIOUS_SAND
			//TODO: TORCHFLOWER
			//TODO: TORCHFLOWER_CROP
			//TODO: CALIBRATED_SCULK_SENSOR
			self::registerBlock(new CherryButton());
			self::registerBlock(new CherryDoubleSlab());
			self::registerBlock(new CherryFence());
			self::registerBlock(new CherryFenceGate());
			//TODO: CHERRY_HANGING_SIGN
			self::registerBlock(new CherryLeaves()); //сделать наследником листвы
			self::registerBlock(new CherryLog());
			self::registerBlock(new CherryPlanks());
			self::registerBlock(new CherryPressurePlate());
			self::registerBlock(new CherrySapling());
			self::registerBlock(new CherrySlab());
			self::registerBlock(new CherryStairs());
			self::registerBlock(new CherrySignPost());
			self::registerBlock(new CherryTrapdoor());
			self::registerBlock(new CherryWallSign());
			self::registerBlock(new CherryWood());
			//TODO: PINK_PETALS
			self::registerBlock(new StrippedCherryWood());
			self::registerBlock(new StrippedCherryLog(BlockIds::STRIPPED_CHERRY_LOG, 0, "Cherry"));
			//TODO: SUSPICIOUS_GRAVEL
			//TODO: PITCHER_CROP
			//TODO: PITCHER_PLANT
			//TODO: SNIFFER_EGG
			self::registerBlock(new ChiseledCopper(BlockIds::CHISELED_COPPER, 0, "Chiseled Copper"));
			//TODO: CHISELED_TUFF
			//TODO: CHISELED_TUFF_BRICKS
			//TODO: COPPER_BULB
			self::registerBlock(new CopperGrate(BlockIds::COPPER_GRATE, 0, "Copper Grate"));
			self::registerBlock(new CopperTrapdoor(BlockIds::COPPER_TRAPDOOR, 0, "Copper Trapdoor"));
			//TODO: CRAFTER
			self::registerBlock(new ChiseledCopper(BlockIds::EXPOSED_CHISELED_COPPER, 0, "Exposed Chiseled Copper"));
			//TODO: EXPOSED_COPPER_BULB
			self::registerBlock(new CopperGrate(BlockIds::EXPOSED_COPPER_GRATE, 0, "Exposed Copper Grate"));
			self::registerBlock(new CopperTrapdoor(BlockIds::EXPOSED_COPPER_TRAPDOOR, 0, "Exposed Copper Trapdoor"));
			self::registerBlock(new ChiseledCopper(BlockIds::OXIDIZED_CHISELED_COPPER, 0, "Oxidized Chiseled Copper"));
			//TODO: OXIDIZED_COPPER_BULB
			self::registerBlock(new CopperGrate(BlockIds::OXIDIZED_COPPER_GRATE, 0, "Oxidized Copper Grate"));
			self::registerBlock(new CopperTrapdoor(BlockIds::OXIDIZED_COPPER_TRAPDOOR, 0, "Oxidized Copper Trapdoor"));
			//TODO: POLISHED_TUFF
			//TODO: POLISHED_TUFF_DOUBLE_SLAB
			//TODO: POLISHED_TUFF_SLAB
			//TODO: POLISHED_TUFF_STAIRS
			//TODO: POLISHED_TUFF_WALL
			//TODO: TUFF_BRICK_DOUBLE_SLAB
			//TODO: TUFF_BRICK_SLAB
			//TODO: TUFF_BRICK_STAIRS
			//TODO: TUFF_BRICK_WALL
			//TODO: TUFF_BRICKS
			//TODO: TUFF_SLAB
			//TODO: TUFF_STAIRS
			//TODO: TUFF_WALL
			self::registerBlock(new ChiseledCopper(BlockIds::WAXED_CHISELED_COPPER, 0, "Waxed Chiseled Copper"));
			//TODO: WAXED_COPPER_BULB
			self::registerBlock(new CopperGrate(BlockIds::WAXED_COPPER_GRATE, 0, "Waxed Copper Grate"));
			self::registerBlock(new CopperTrapdoor(BlockIds::WAXED_COPPER_TRAPDOOR, 0, "Waxed Copper Trapdoor"));
			self::registerBlock(new ChiseledCopper(BlockIds::WAXED_EXPOSED_CHISELED_COPPER, 0, "Waxed Exposed Chiseled Copper"));
			//TODO: WAXED_EXPOSED_COPPER_BULB
			self::registerBlock(new CopperGrate(BlockIds::WAXED_EXPOSED_COPPER_GRATE, 0, "Waxed Exposed Copper Grate"));
			self::registerBlock(new CopperTrapdoor(BlockIds::WAXED_EXPOSED_COPPER_TRAPDOOR, 0, "Waxed Exposed Copper Trapdoor"));
			self::registerBlock(new ChiseledCopper(BlockIds::WAXED_OXIDIZED_CHISELED_COPPER, 0, "Waxed Oxidized Chiseled Copper"));
			//TODO: WAXED_OXIDIZED_COPPER_BULB
			self::registerBlock(new CopperGrate(BlockIds::WAXED_OXIDIZED_COPPER_GRATE, 0, "Waxed Oxidized Copper Grate"));
			self::registerBlock(new CopperTrapdoor(BlockIds::WAXED_OXIDIZED_COPPER_TRAPDOOR, 0, "Waxed Oxidized Copper Trapdoor"));
			self::registerBlock(new ChiseledCopper(BlockIds::WAXED_WEATHERED_CHISELED_COPPER, 0, "Waxed Weathered Chiseled Copper"));
			//TODO: WAXED_WEATHERED_COPPER_BULB
			self::registerBlock(new CopperGrate(BlockIds::WAXED_WEATHERED_COPPER_GRATE, 0, "Waxed Weathered Copper Grate"));
			self::registerBlock(new CopperTrapdoor(BlockIds::WAXED_WEATHERED_COPPER_TRAPDOOR, 0, "Waxed Weathered Copper Trapdoor"));
			self::registerBlock(new ChiseledCopper(BlockIds::WEATHERED_CHISELED_COPPER, 0, "Weathered Chiseled Copper"));
			//TODO: WEATHERED_COPPER_BULB
			self::registerBlock(new CopperGrate(BlockIds::WEATHERED_COPPER_GRATE, 0, "Weathered Copper Grate"));
			self::registerBlock(new CopperTrapdoor(BlockIds::WEATHERED_COPPER_TRAPDOOR, 0, "Weathered Copper Trapdoor"));
			self::registerBlock(new CopperDoor(BlockIds::WEATHERED_COPPER_DOOR, 0, "Weathered Copper Door"));
			self::registerBlock(new CopperDoor(BlockIds::WAXED_WEATHERED_COPPER_DOOR, 0, "Waxed Weathered Copper Door"));
			self::registerBlock(new CopperDoor(BlockIds::WAXED_OXIDIZED_COPPER_DOOR, 0, "Waxed Oxidized Copper Door"));
			self::registerBlock(new CopperDoor(BlockIds::WAXED_EXPOSED_COPPER_DOOR, 0, "Waxed Exposed Copper Door"));
			self::registerBlock(new CopperDoor(BlockIds::WAXED_COPPER_DOOR, 0, "Waxed Copper Door"));
			self::registerBlock(new CopperDoor(BlockIds::OXIDIZED_COPPER_DOOR, 0, "Oxidized Copper Door"));
			self::registerBlock(new CopperDoor(BlockIds::EXPOSED_COPPER_DOOR, 0, "Exposed Copper Door"));
			self::registerBlock(new CopperDoor(BlockIds::COPPER_DOOR, 0, "Copper Door"));
			self::registerBlock(new CherryDoor());
			//TODO: RESIN_CLUMP
			//TODO: ACACIA_HANGING_SIGN
			self::registerBlock(new MossyCobblestoneWall());
			self::registerBlock(new GraniteWall());
			self::registerBlock(new DioriteWall());
			self::registerBlock(new AndesiteWall());
			self::registerBlock(new SandstoneWall());
			self::registerBlock(new BrickWall());
			self::registerBlock(new StoneBrickWall());
			self::registerBlock(new MossyStoneBrickWall());
			self::registerBlock(new NetherBrickWall());
			self::registerBlock(new EndStoneBrickWall());
			self::registerBlock(new PrismarineWall());
			self::registerBlock(new RedSandstoneWall());
			self::registerBlock(new RedNetherBrickWall());

			//TODO: TRIAL_SPAWNER
			//TODO: VAULT
			self::registerBlock(new HeavyCore());
			//TODO: DEPRECATED_ANVIL
			//TODO: MUSHROOM_STEM

			self::registerBlock(new DriedGhast());
			self::registerBlock(new ChiseledResinBricks());
			self::registerBlock(new ClosedEyeblossom());
			self::registerBlock(new CreakingHeart());
			self::registerBlock(new OpenEyeblossom());
			self::registerBlock(new PaleHangingMoss());
			self::registerBlock(new PaleMoss());
			self::registerBlock(new PaleMossCarpet());
			self::registerBlock(new PaleOakButton());
			self::registerBlock(new PaleOakDoor());
			self::registerBlock(new PaleOakDoubleSlab());
			self::registerBlock(new PaleOakFence());
			self::registerBlock(new PaleOakFenceGate());
			//TODO: PALE_OAK_HANGING_SIGN
			self::registerBlock(new PaleOakLeaves());
			self::registerBlock(new PaleOakLog());
			self::registerBlock(new PaleOakPlanks());
			self::registerBlock(new PaleOakPressurePlate());
			self::registerBlock(new PaleOakSapling());
			self::registerBlock(new PaleOakSlab());
			self::registerBlock(new PaleOakStairs());
			self::registerBlock(new PaleOakSignPost());
			self::registerBlock(new PaleOakTrapdoor());
			self::registerBlock(new PaleOakWallSign());
			self::registerBlock(new PaleOakWood());
			self::registerBlock(new Resin());
			self::registerBlock(new ResinBrickDoubleSlab());
			self::registerBlock(new ResinBrickSlab());
			self::registerBlock(new ResinBrickStairs());
			self::registerBlock(new ResinBricks());
			self::registerBlock(new StrippedPaleOakLog(BlockIds::STRIPPED_PALE_OAK_LOG, 0, "Pale Oak"));
			self::registerBlock(new StrippedPaleOakWood());
			//TODO: RESIN_BRICK_WALL
			self::registerBlock(new Bush());
			self::registerBlock(new CactusFlower());
			self::registerBlock(new FireflyBush());
			self::registerBlock(new LeafLitter());
			self::registerBlock(new ShortDryGrass());
			self::registerBlock(new TallDryGrass());
			self::registerBlock(new Wildflowers());
			//TODO: COPPER_CHEST
			//TODO: EXPOSED_COPPER_CHEST
			//TODO: OXIDIZED_COPPER_CHEST
			//TODO: WAXED_COPPER_CHEST
			//TODO: WAXED_EXPOSED_COPPER_CHEST
			//TODO: WAXED_OXIDDIZED_COPPER_CHEST
			//TODO: WAXED_WEATHERED_COPPER_CHEST
			//TODO: WEATHERED_COPPER_CHEST
		}
	}

	public static function isInit() : bool
	{
		return self::$fullList !== null;
	}

	/**
	 * Registers a block type into the index. Plugins may use this method to register new block types or override
	 * existing ones.
	 *
	 * NOTE: If you are registering a new block type, you will need to add it to the creative inventory yourself - it
	 * will not automatically appear there.
	 *
	 * @param bool $override Whether to override existing registrations
	 *
	 * @throws RuntimeException if something attempted to override an already-registered block without specifying the
	 * $override parameter.
	 */
	public static function registerBlock(Block $block, bool $override = false) : void
	{
		$id = $block->getId();
		$meta = $block->getDamage();

		if (!$override && self::isRegistered($id, $meta)) {
			throw new RuntimeException("Trying to overwrite an already registered block");
		}

		for ($meta = 0; $meta < (1 << Block::INTERNAL_METADATA_BITS); ++$meta) {
			$variant = clone $block;
			$variant->setDamage($meta);

			self::fillStaticArrays($variant->getFullId(), $variant);
		}
	}

	private static function fillStaticArrays(int $index, Block $block) : void
	{
		self::$fullList[$index] = $block;
		self::$mappedStateIds[$index] = $block->getFullId();
		self::$light[$index] = $block->getLightLevel();
		self::$lightFilter[$index] = min(15, $block->getLightFilter() + 1); //opacity plus 1 standard light filter
		self::$diffusesSkyLight[$index] = $block->diffusesSkyLight();
		self::$blastResistance[$index] = $block->getBlastResistance();
		self::$hasEntityCollision[$index] = $block->hasEntityCollision();
	}

	/**
	 * Returns a new Block instance with the specified ID, meta and position.
	 */
	public static function get(int $id, int $meta = 0, Position $pos = null) : Block
	{
		if($meta < 0 || $meta >= (1 << Block::INTERNAL_METADATA_BITS)){
			throw new InvalidArgumentException("Block meta value $meta is out of bounds");
		}

		$index = ($id << Block::INTERNAL_METADATA_BITS) | $meta;
		if($index < 0 || $index >= self::$fullList->getSize()){
			throw new InvalidArgumentException("Block ID $id is out of bounds");
		}

		if(self::$fullList[$index] !== null){
			$block = clone self::$fullList[$index];
		}else{
			$block = new UnknownBlock($id, $meta);
		}

		if ($pos !== null) {
			$block->x = $pos->getFloorX();
			$block->y = $pos->getFloorY();
			$block->z = $pos->getFloorZ();
			$block->level = $pos->level;
		}

		return $block;
	}

	public static function fromFullBlock(int $fullState, Position $pos = null) : Block
	{
		return self::get($fullState >> Block::INTERNAL_METADATA_BITS, $fullState & Block::INTERNAL_METADATA_MASK, $pos);
	}

	/**
	 * Returns whether a specified block state is already registered in the block factory.
	 */
	public static function isRegistered(int $id, int $meta = 0) : bool
	{
		$b = self::$fullList[self::getListOffset($id, $meta)];
		return $b !== null && !($b instanceof UnknownBlock);
	}

	/**
	 * @return Block[]
	 */
	public static function getAllKnownStates() : array
	{
		return array_filter(self::$fullList->toArray(), function (?Block $v) : bool { return $v !== null; });
	}

	/**
	 * Returns the ID of the state mapped to the given state ID.
	 * Used to correct invalid blockstates found in loaded chunks.
	 */
	public static function getMappedStateId(int $fullState) : int
	{
		return self::$mappedStateIds[$fullState] ?? $fullState;
	}

	public static function getListOffset(int $id, int $meta) : int
	{
		return $id << Block::INTERNAL_METADATA_BITS | $meta;
	}
}
