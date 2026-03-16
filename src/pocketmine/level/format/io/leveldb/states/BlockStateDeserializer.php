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

namespace pocketmine\level\format\io\leveldb\states;

use pocketmine\block\Anvil;
use pocketmine\block\Bed;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\block\Dirt;
use pocketmine\block\DoublePlant;
use pocketmine\block\Flower;
use pocketmine\block\Leaves;
use pocketmine\block\Log;
use pocketmine\block\Log2;
use pocketmine\block\Planks;
use pocketmine\block\Prismarine;
use pocketmine\block\Quartz;
use pocketmine\block\Sand;
use pocketmine\block\Sandstone;
use pocketmine\block\Sapling;
use pocketmine\block\Stone;
use pocketmine\block\StoneBricks;
use pocketmine\block\StoneSlab;
use pocketmine\block\StoneSlab2;
use pocketmine\block\StoneSlab3;
use pocketmine\block\StoneSlab4;
use pocketmine\block\TallGrass;
use pocketmine\block\utils\BlockTypeNames as Ids;
use pocketmine\block\utils\ColorBlockMetaHelper;
use pocketmine\block\WoodenFence;
use pocketmine\block\WoodenSlab;
use pocketmine\level\format\io\leveldb\states\BlockStateDeserializerHelper as Helper;
use pocketmine\level\format\io\leveldb\states\BlockStateNames as StateNames;
use pocketmine\level\format\io\leveldb\states\BlockStateReader as Reader;
use pocketmine\level\format\io\leveldb\states\BlockStateStringValues as StringValues;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\tile\Skull;
use pocketmine\utils\SingletonTrait;

use function array_key_exists;
use function count;
use function min;

/**
 * They are purely for LevelDB support!
 */
class BlockStateDeserializer
{
	use SingletonTrait;

	/**
	 * @var \Closure[]
	 * @phpstan-var array<string, \Closure(Reader $in) : Block>
	 */
	private array $deserializeFuncs = [];

	/**
	 * @var int[]
	 * @phpstan-var array<string, int>
	 */
	private array $simpleCache = [];

	public function __construct()
	{
		$this->registerFlatColorBlockDeserializers();
		$this->registerCauldronDeserializers();
		$this->registerFlatWoodBlockDeserializers();
		$this->registerLeavesDeserializers();
		$this->registerSaplingDeserializers();
		$this->registerMobHeadDeserializers();
		$this->registerSimpleDeserializers();
		$this->registerDeserializers();
	}

	public function deserialize(BlockStateData $stateData) : int
	{
		if (count($stateData->getStates()) === 0) {
			//if a block has zero properties, we can keep a map of string ID -> internal blockstate ID
			return $this->simpleCache[$stateData->getName()] ??= $this->deserializeBlock($stateData)->getFullId();
		}

		//we can't cache blocks that have properties - go ahead and deserialize the slow way
		return $this->deserializeBlock($stateData)->getFullId();
	}

	/** @phpstan-param \Closure(Reader) : Block $c */
	public function map(string $id, \Closure $c) : void
	{
		if (array_key_exists($id, $this->deserializeFuncs)) {
			throw new \InvalidArgumentException("Deserializer is already assigned for \"$id\"");
		}
		$this->deserializeFuncs[$id] = $c;
	}

	/** @phpstan-param \Closure() : Block $getBlock */
	public function mapSimple(string $id, \Closure $getBlock) : void
	{
		$this->map($id, $getBlock);
	}

	/**
	 * @phpstan-param \Closure(Reader) : Block $getBlock
	 */
	public function mapSingleSlab(string $singleId, \Closure $getBlock) : void
	{
		$this->map($singleId, fn (Reader $in) => Helper::decodeSingleSlab($getBlock($in), $in));
	}

	/**
	 * @phpstan-param \Closure(Reader) : Block $getBlock
	 */
	public function mapDoubleSlab(string $doubleId, \Closure $getBlock) : void
	{
		$this->map($doubleId, fn (Reader $in) => Helper::decodeDoubleSlab($getBlock($in), $in));
	}

	/** @phpstan-param \Closure() : Block $getBlock */
	public function mapLog(string $unstrippedId, string $strippedId, \Closure $getBlock) : void
	{
		$this->map($unstrippedId, fn (Reader $in) => Helper::decodeLog($getBlock(), false, $in));
		$this->map($strippedId, fn (Reader $in) => Helper::decodeLog($getBlock(), true, $in));
	}

	/**
	 * @phpstan-param \Closure() : Block $getBlock
	 */
	public function mapStairs(string $id, \Closure $getBlock) : void
	{
		$this->map($id, fn (Reader $in) : Block => Helper::decodeStairs($getBlock(), $in));
	}

	private function registerFlatColorBlockDeserializers() : void
	{
		$this->map(Ids::BLACK_GLAZED_TERRACOTTA, fn (Reader $in) => BlockFactory::get(BlockIds::BLACK_GLAZED_TERRACOTTA, $in->readHorizontalFacing()));
		$this->map(Ids::BLUE_GLAZED_TERRACOTTA, fn (Reader $in) => BlockFactory::get(BlockIds::BLUE_GLAZED_TERRACOTTA, $in->readHorizontalFacing()));
		$this->map(Ids::BROWN_GLAZED_TERRACOTTA, fn (Reader $in) => BlockFactory::get(BlockIds::BROWN_GLAZED_TERRACOTTA, $in->readHorizontalFacing()));
		$this->map(Ids::CYAN_GLAZED_TERRACOTTA, fn (Reader $in) => BlockFactory::get(BlockIds::CYAN_GLAZED_TERRACOTTA, $in->readHorizontalFacing()));
		$this->map(Ids::GRAY_GLAZED_TERRACOTTA, fn (Reader $in) => BlockFactory::get(BlockIds::GRAY_GLAZED_TERRACOTTA, $in->readHorizontalFacing()));
		$this->map(Ids::GREEN_GLAZED_TERRACOTTA, fn (Reader $in) => BlockFactory::get(BlockIds::GREEN_GLAZED_TERRACOTTA, $in->readHorizontalFacing()));
		$this->map(Ids::LIGHT_BLUE_GLAZED_TERRACOTTA, fn (Reader $in) => BlockFactory::get(BlockIds::LIGHT_BLUE_GLAZED_TERRACOTTA, $in->readHorizontalFacing()));
		$this->map(Ids::SILVER_GLAZED_TERRACOTTA, fn (Reader $in) => BlockFactory::get(BlockIds::SILVER_GLAZED_TERRACOTTA, $in->readHorizontalFacing()));
		$this->map(Ids::LIME_GLAZED_TERRACOTTA, fn (Reader $in) => BlockFactory::get(BlockIds::LIME_GLAZED_TERRACOTTA, $in->readHorizontalFacing()));
		$this->map(Ids::MAGENTA_GLAZED_TERRACOTTA, fn (Reader $in) => BlockFactory::get(BlockIds::MAGENTA_GLAZED_TERRACOTTA, $in->readHorizontalFacing()));
		$this->map(Ids::ORANGE_GLAZED_TERRACOTTA, fn (Reader $in) => BlockFactory::get(BlockIds::ORANGE_GLAZED_TERRACOTTA, $in->readHorizontalFacing()));
		$this->map(Ids::PINK_GLAZED_TERRACOTTA, fn (Reader $in) => BlockFactory::get(BlockIds::PINK_GLAZED_TERRACOTTA, $in->readHorizontalFacing()));
		$this->map(Ids::PURPLE_GLAZED_TERRACOTTA, fn (Reader $in) => BlockFactory::get(BlockIds::PURPLE_GLAZED_TERRACOTTA, $in->readHorizontalFacing()));
		$this->map(Ids::RED_GLAZED_TERRACOTTA, fn (Reader $in) => BlockFactory::get(BlockIds::RED_GLAZED_TERRACOTTA, $in->readHorizontalFacing()));
		$this->map(Ids::WHITE_GLAZED_TERRACOTTA, fn (Reader $in) => BlockFactory::get(BlockIds::WHITE_GLAZED_TERRACOTTA, $in->readHorizontalFacing()));
		$this->map(Ids::YELLOW_GLAZED_TERRACOTTA, fn (Reader $in) => BlockFactory::get(BlockIds::YELLOW_GLAZED_TERRACOTTA, $in->readHorizontalFacing()));

		foreach ([
			Ids::BLACK_WOOL => ColorBlockMetaHelper::BLACK,
			Ids::BLUE_WOOL => ColorBlockMetaHelper::BLUE,
			Ids::BROWN_WOOL => ColorBlockMetaHelper::BROWN,
			Ids::CYAN_WOOL => ColorBlockMetaHelper::CYAN,
			Ids::GRAY_WOOL => ColorBlockMetaHelper::GRAY,
			Ids::GREEN_WOOL => ColorBlockMetaHelper::GREEN,
			Ids::LIGHT_BLUE_WOOL => ColorBlockMetaHelper::LIGHT_BLUE,
			Ids::LIGHT_GRAY_WOOL => ColorBlockMetaHelper::LIGHT_GRAY,
			Ids::LIME_WOOL => ColorBlockMetaHelper::LIME,
			Ids::MAGENTA_WOOL => ColorBlockMetaHelper::MAGENTA,
			Ids::ORANGE_WOOL => ColorBlockMetaHelper::ORANGE,
			Ids::PINK_WOOL => ColorBlockMetaHelper::PINK,
			Ids::PURPLE_WOOL => ColorBlockMetaHelper::PURPLE,
			Ids::RED_WOOL => ColorBlockMetaHelper::RED,
			Ids::WHITE_WOOL => ColorBlockMetaHelper::WHITE,
			Ids::YELLOW_WOOL => ColorBlockMetaHelper::YELLOW,
		] as $id => $color) {
			$this->mapSimple($id, fn () => BlockFactory::get(BlockIds::WOOL, $color));
		}

		foreach ([
			Ids::BLACK_CARPET => ColorBlockMetaHelper::BLACK,
			Ids::BLUE_CARPET => ColorBlockMetaHelper::BLUE,
			Ids::BROWN_CARPET => ColorBlockMetaHelper::BROWN,
			Ids::CYAN_CARPET => ColorBlockMetaHelper::CYAN,
			Ids::GRAY_CARPET => ColorBlockMetaHelper::GRAY,
			Ids::GREEN_CARPET => ColorBlockMetaHelper::GREEN,
			Ids::LIGHT_BLUE_CARPET => ColorBlockMetaHelper::LIGHT_BLUE,
			Ids::LIGHT_GRAY_CARPET => ColorBlockMetaHelper::LIGHT_GRAY,
			Ids::LIME_CARPET => ColorBlockMetaHelper::LIME,
			Ids::MAGENTA_CARPET => ColorBlockMetaHelper::MAGENTA,
			Ids::ORANGE_CARPET => ColorBlockMetaHelper::ORANGE,
			Ids::PINK_CARPET => ColorBlockMetaHelper::PINK,
			Ids::PURPLE_CARPET => ColorBlockMetaHelper::PURPLE,
			Ids::RED_CARPET => ColorBlockMetaHelper::RED,
			Ids::WHITE_CARPET => ColorBlockMetaHelper::WHITE,
			Ids::YELLOW_CARPET => ColorBlockMetaHelper::YELLOW,
		] as $id => $color) {
			$this->mapSimple($id, fn () => BlockFactory::get(BlockIds::CARPET, $color));
		}

		foreach ([
			Ids::BLACK_SHULKER_BOX => ColorBlockMetaHelper::BLACK,
			Ids::BLUE_SHULKER_BOX => ColorBlockMetaHelper::BLUE,
			Ids::BROWN_SHULKER_BOX => ColorBlockMetaHelper::BROWN,
			Ids::CYAN_SHULKER_BOX => ColorBlockMetaHelper::CYAN,
			Ids::GRAY_SHULKER_BOX => ColorBlockMetaHelper::GRAY,
			Ids::GREEN_SHULKER_BOX => ColorBlockMetaHelper::GREEN,
			Ids::LIGHT_BLUE_SHULKER_BOX => ColorBlockMetaHelper::LIGHT_BLUE,
			Ids::LIGHT_GRAY_SHULKER_BOX => ColorBlockMetaHelper::LIGHT_GRAY,
			Ids::LIME_SHULKER_BOX => ColorBlockMetaHelper::LIME,
			Ids::MAGENTA_SHULKER_BOX => ColorBlockMetaHelper::MAGENTA,
			Ids::ORANGE_SHULKER_BOX => ColorBlockMetaHelper::ORANGE,
			Ids::PINK_SHULKER_BOX => ColorBlockMetaHelper::PINK,
			Ids::PURPLE_SHULKER_BOX => ColorBlockMetaHelper::PURPLE,
			Ids::RED_SHULKER_BOX => ColorBlockMetaHelper::RED,
			Ids::WHITE_SHULKER_BOX => ColorBlockMetaHelper::WHITE,
			Ids::YELLOW_SHULKER_BOX => ColorBlockMetaHelper::YELLOW,
		] as $id => $color) {
			$this->mapSimple($id, fn () => BlockFactory::get(BlockIds::SHULKER_BOX, $color));
		}

		foreach ([
			Ids::BLACK_CONCRETE => ColorBlockMetaHelper::BLACK,
			Ids::BLUE_CONCRETE => ColorBlockMetaHelper::BLUE,
			Ids::BROWN_CONCRETE => ColorBlockMetaHelper::BROWN,
			Ids::CYAN_CONCRETE => ColorBlockMetaHelper::CYAN,
			Ids::GRAY_CONCRETE => ColorBlockMetaHelper::GRAY,
			Ids::GREEN_CONCRETE => ColorBlockMetaHelper::GREEN,
			Ids::LIGHT_BLUE_CONCRETE => ColorBlockMetaHelper::LIGHT_BLUE,
			Ids::LIGHT_GRAY_CONCRETE => ColorBlockMetaHelper::LIGHT_GRAY,
			Ids::LIME_CONCRETE => ColorBlockMetaHelper::LIME,
			Ids::MAGENTA_CONCRETE => ColorBlockMetaHelper::MAGENTA,
			Ids::ORANGE_CONCRETE => ColorBlockMetaHelper::ORANGE,
			Ids::PINK_CONCRETE => ColorBlockMetaHelper::PINK,
			Ids::PURPLE_CONCRETE => ColorBlockMetaHelper::PURPLE,
			Ids::RED_CONCRETE => ColorBlockMetaHelper::RED,
			Ids::WHITE_CONCRETE => ColorBlockMetaHelper::WHITE,
			Ids::YELLOW_CONCRETE => ColorBlockMetaHelper::YELLOW,
		] as $id => $color) {
			$this->mapSimple($id, fn () => BlockFactory::get(BlockIds::CONCRETE, $color));
		}

		foreach ([
			Ids::BLACK_CONCRETE_POWDER => ColorBlockMetaHelper::BLACK,
			Ids::BLUE_CONCRETE_POWDER => ColorBlockMetaHelper::BLUE,
			Ids::BROWN_CONCRETE_POWDER => ColorBlockMetaHelper::BROWN,
			Ids::CYAN_CONCRETE_POWDER => ColorBlockMetaHelper::CYAN,
			Ids::GRAY_CONCRETE_POWDER => ColorBlockMetaHelper::GRAY,
			Ids::GREEN_CONCRETE_POWDER => ColorBlockMetaHelper::GREEN,
			Ids::LIGHT_BLUE_CONCRETE_POWDER => ColorBlockMetaHelper::LIGHT_BLUE,
			Ids::LIGHT_GRAY_CONCRETE_POWDER => ColorBlockMetaHelper::LIGHT_GRAY,
			Ids::LIME_CONCRETE_POWDER => ColorBlockMetaHelper::LIME,
			Ids::MAGENTA_CONCRETE_POWDER => ColorBlockMetaHelper::MAGENTA,
			Ids::ORANGE_CONCRETE_POWDER => ColorBlockMetaHelper::ORANGE,
			Ids::PINK_CONCRETE_POWDER => ColorBlockMetaHelper::PINK,
			Ids::PURPLE_CONCRETE_POWDER => ColorBlockMetaHelper::PURPLE,
			Ids::RED_CONCRETE_POWDER => ColorBlockMetaHelper::RED,
			Ids::WHITE_CONCRETE_POWDER => ColorBlockMetaHelper::WHITE,
			Ids::YELLOW_CONCRETE_POWDER => ColorBlockMetaHelper::YELLOW,
		] as $id => $color) {
			$this->mapSimple($id, fn () => BlockFactory::get(BlockIds::CONCRETE_POWDER, $color));
		}

		foreach ([
			Ids::BLACK_TERRACOTTA => ColorBlockMetaHelper::BLACK,
			Ids::BLUE_TERRACOTTA => ColorBlockMetaHelper::BLUE,
			Ids::BROWN_TERRACOTTA => ColorBlockMetaHelper::BROWN,
			Ids::CYAN_TERRACOTTA => ColorBlockMetaHelper::CYAN,
			Ids::GRAY_TERRACOTTA => ColorBlockMetaHelper::GRAY,
			Ids::GREEN_TERRACOTTA => ColorBlockMetaHelper::GREEN,
			Ids::LIGHT_BLUE_TERRACOTTA => ColorBlockMetaHelper::LIGHT_BLUE,
			Ids::LIGHT_GRAY_TERRACOTTA => ColorBlockMetaHelper::LIGHT_GRAY,
			Ids::LIME_TERRACOTTA => ColorBlockMetaHelper::LIME,
			Ids::MAGENTA_TERRACOTTA => ColorBlockMetaHelper::MAGENTA,
			Ids::ORANGE_TERRACOTTA => ColorBlockMetaHelper::ORANGE,
			Ids::PINK_TERRACOTTA => ColorBlockMetaHelper::PINK,
			Ids::PURPLE_TERRACOTTA => ColorBlockMetaHelper::PURPLE,
			Ids::RED_TERRACOTTA => ColorBlockMetaHelper::RED,
			Ids::WHITE_TERRACOTTA => ColorBlockMetaHelper::WHITE,
			Ids::YELLOW_TERRACOTTA => ColorBlockMetaHelper::YELLOW,
		] as $id => $color) {
			$this->mapSimple($id, fn () => BlockFactory::get(BlockIds::STAINED_CLAY, $color));
		}

		foreach ([
			Ids::BLACK_STAINED_GLASS => ColorBlockMetaHelper::BLACK,
			Ids::BLUE_STAINED_GLASS => ColorBlockMetaHelper::BLUE,
			Ids::BROWN_STAINED_GLASS => ColorBlockMetaHelper::BROWN,
			Ids::CYAN_STAINED_GLASS => ColorBlockMetaHelper::CYAN,
			Ids::GRAY_STAINED_GLASS => ColorBlockMetaHelper::GRAY,
			Ids::GREEN_STAINED_GLASS => ColorBlockMetaHelper::GREEN,
			Ids::LIGHT_BLUE_STAINED_GLASS => ColorBlockMetaHelper::LIGHT_BLUE,
			Ids::LIGHT_GRAY_STAINED_GLASS => ColorBlockMetaHelper::LIGHT_GRAY,
			Ids::LIME_STAINED_GLASS => ColorBlockMetaHelper::LIME,
			Ids::MAGENTA_STAINED_GLASS => ColorBlockMetaHelper::MAGENTA,
			Ids::ORANGE_STAINED_GLASS => ColorBlockMetaHelper::ORANGE,
			Ids::PINK_STAINED_GLASS => ColorBlockMetaHelper::PINK,
			Ids::PURPLE_STAINED_GLASS => ColorBlockMetaHelper::PURPLE,
			Ids::RED_STAINED_GLASS => ColorBlockMetaHelper::RED,
			Ids::WHITE_STAINED_GLASS => ColorBlockMetaHelper::WHITE,
			Ids::YELLOW_STAINED_GLASS => ColorBlockMetaHelper::YELLOW,
		] as $id => $color) {
			$this->mapSimple($id, fn () => BlockFactory::get(BlockIds::STAINED_GLASS, $color));
		}

		foreach ([
			Ids::BLACK_STAINED_GLASS_PANE => ColorBlockMetaHelper::BLACK,
			Ids::BLUE_STAINED_GLASS_PANE => ColorBlockMetaHelper::BLUE,
			Ids::BROWN_STAINED_GLASS_PANE => ColorBlockMetaHelper::BROWN,
			Ids::CYAN_STAINED_GLASS_PANE => ColorBlockMetaHelper::CYAN,
			Ids::GRAY_STAINED_GLASS_PANE => ColorBlockMetaHelper::GRAY,
			Ids::GREEN_STAINED_GLASS_PANE => ColorBlockMetaHelper::GREEN,
			Ids::LIGHT_BLUE_STAINED_GLASS_PANE => ColorBlockMetaHelper::LIGHT_BLUE,
			Ids::LIGHT_GRAY_STAINED_GLASS_PANE => ColorBlockMetaHelper::LIGHT_GRAY,
			Ids::LIME_STAINED_GLASS_PANE => ColorBlockMetaHelper::LIME,
			Ids::MAGENTA_STAINED_GLASS_PANE => ColorBlockMetaHelper::MAGENTA,
			Ids::ORANGE_STAINED_GLASS_PANE => ColorBlockMetaHelper::ORANGE,
			Ids::PINK_STAINED_GLASS_PANE => ColorBlockMetaHelper::PINK,
			Ids::PURPLE_STAINED_GLASS_PANE => ColorBlockMetaHelper::PURPLE,
			Ids::RED_STAINED_GLASS_PANE => ColorBlockMetaHelper::RED,
			Ids::WHITE_STAINED_GLASS_PANE => ColorBlockMetaHelper::WHITE,
			Ids::YELLOW_STAINED_GLASS_PANE => ColorBlockMetaHelper::YELLOW,
		] as $id => $color) {
			$this->mapSimple($id, fn () => BlockFactory::get(BlockIds::STAINED_GLASS_PANE, $color));
		}
	}

	private function registerCauldronDeserializers() : void
	{
		$this->mapSimple(Ids::CAULDRON, fn () => BlockFactory::get(BlockIds::CAULDRON_BLOCK));
	}

	private function registerFlatWoodBlockDeserializers() : void
	{
		$this->map(Ids::ACACIA_BUTTON, fn (Reader $in) => Helper::decodeButton(BlockFactory::get(BlockIds::ACACIA_BUTTON), $in));
		$this->map(Ids::ACACIA_DOOR, fn (Reader $in) => Helper::decodeDoor(BlockFactory::get(BlockIds::ACACIA_DOOR_BLOCK), $in));
		$this->map(Ids::ACACIA_FENCE_GATE, fn (Reader $in) => Helper::decodeFenceGate(BlockFactory::get(BlockIds::ACACIA_FENCE_GATE), $in));
		$this->mapSimple(Ids::ACACIA_PRESSURE_PLATE, fn () => BlockFactory::get(BlockIds::ACACIA_PRESSURE_PLATE));
		$this->map(Ids::ACACIA_TRAPDOOR, fn (Reader $in) => Helper::decodeTrapdoor(BlockFactory::get(BlockIds::ACACIA_TRAPDOOR), $in));
		$this->map(Ids::ACACIA_WALL_SIGN, fn (Reader $in) => Helper::decodeWallSign(BlockFactory::get(BlockIds::ACACIA_WALL_SIGN), $in));
		$this->mapLog(Ids::ACACIA_LOG, Ids::STRIPPED_ACACIA_LOG, fn () => BlockFactory::get(BlockIds::LOG2, Log2::ACACIA));
		$this->mapLog(Ids::ACACIA_WOOD, Ids::STRIPPED_ACACIA_WOOD, fn () => BlockFactory::get(BlockIds::LOG2, Log2::ACACIA));
		$this->mapSimple(Ids::ACACIA_FENCE, fn () => BlockFactory::get(BlockIds::FENCE, WoodenFence::FENCE_ACACIA));
		$this->mapSimple(Ids::ACACIA_PLANKS, fn () => BlockFactory::get(BlockIds::PLANKS, Planks::ACACIA));
		$this->mapSingleSlab(Ids::ACACIA_SLAB, fn () => BlockFactory::get(BlockIds::WOODEN_SLAB, Planks::ACACIA));
		$this->mapDoubleSlab(Ids::ACACIA_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_WOODEN_SLAB, Planks::ACACIA));
		$this->mapStairs(Ids::ACACIA_STAIRS, fn () => BlockFactory::get(BlockIds::ACACIA_STAIRS));

		$this->map(Ids::BIRCH_BUTTON, fn (Reader $in) => Helper::decodeButton(BlockFactory::get(BlockIds::BIRCH_BUTTON), $in));
		$this->map(Ids::BIRCH_DOOR, fn (Reader $in) => Helper::decodeDoor(BlockFactory::get(BlockIds::BIRCH_DOOR_BLOCK), $in));
		$this->map(Ids::BIRCH_FENCE_GATE, fn (Reader $in) => Helper::decodeFenceGate(BlockFactory::get(BlockIds::BIRCH_FENCE_GATE), $in));
		$this->mapSimple(Ids::BIRCH_PRESSURE_PLATE, fn () => BlockFactory::get(BlockIds::BIRCH_PRESSURE_PLATE));
		$this->map(Ids::BIRCH_TRAPDOOR, fn (Reader $in) => Helper::decodeTrapdoor(BlockFactory::get(BlockIds::BIRCH_TRAPDOOR), $in));
		$this->map(Ids::BIRCH_WALL_SIGN, fn (Reader $in) => Helper::decodeWallSign(BlockFactory::get(BlockIds::BIRCH_WALL_SIGN), $in));
		$this->mapLog(Ids::BIRCH_LOG, Ids::STRIPPED_BIRCH_LOG, fn () => BlockFactory::get(BlockIds::LOG, Log::BIRCH));
		$this->mapLog(Ids::BIRCH_WOOD, Ids::STRIPPED_BIRCH_WOOD, fn () => BlockFactory::get(BlockIds::LOG, Log::BIRCH));
		$this->mapSimple(Ids::BIRCH_FENCE, fn () => BlockFactory::get(BlockIds::FENCE, WoodenFence::FENCE_BIRCH));
		$this->mapSimple(Ids::BIRCH_PLANKS, fn () => BlockFactory::get(BlockIds::PLANKS, Planks::BIRCH));
		$this->mapSingleSlab(Ids::BIRCH_SLAB, fn () => BlockFactory::get(BlockIds::WOODEN_SLAB, Planks::BIRCH));
		$this->mapDoubleSlab(Ids::BIRCH_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_WOODEN_SLAB, Planks::BIRCH));
		$this->mapStairs(Ids::BIRCH_STAIRS, fn () => BlockFactory::get(BlockIds::BIRCH_STAIRS));

		$this->map(Ids::DARK_OAK_BUTTON, fn (Reader $in) => Helper::decodeButton(BlockFactory::get(BlockIds::DARK_OAK_BUTTON), $in));
		$this->map(Ids::DARK_OAK_DOOR, fn (Reader $in) => Helper::decodeDoor(BlockFactory::get(BlockIds::DARK_OAK_DOOR_BLOCK), $in));
		$this->map(Ids::DARK_OAK_FENCE_GATE, fn (Reader $in) => Helper::decodeFenceGate(BlockFactory::get(BlockIds::DARK_OAK_FENCE_GATE), $in));
		$this->mapSimple(Ids::DARK_OAK_PRESSURE_PLATE, fn () => BlockFactory::get(BlockIds::DARK_OAK_PRESSURE_PLATE));
		$this->map(Ids::DARK_OAK_TRAPDOOR, fn (Reader $in) => Helper::decodeTrapdoor(BlockFactory::get(BlockIds::DARK_OAK_TRAPDOOR), $in));
		$this->map(Ids::DARKOAK_WALL_SIGN, fn (Reader $in) => Helper::decodeWallSign(BlockFactory::get(BlockIds::DARKOAK_WALL_SIGN), $in));
		$this->mapLog(Ids::DARK_OAK_LOG, Ids::STRIPPED_DARK_OAK_LOG, fn () => BlockFactory::get(BlockIds::LOG2, Log2::DARK_OAK));
		$this->mapLog(Ids::DARK_OAK_WOOD, Ids::STRIPPED_DARK_OAK_WOOD, fn () => BlockFactory::get(BlockIds::LOG2, Log2::DARK_OAK));
		$this->mapSimple(Ids::DARK_OAK_FENCE, fn () => BlockFactory::get(BlockIds::FENCE, WoodenFence::FENCE_DARKOAK));
		$this->mapSimple(Ids::DARK_OAK_PLANKS, fn () => BlockFactory::get(BlockIds::PLANKS, Planks::DARK_OAK));
		$this->mapSingleSlab(Ids::DARK_OAK_SLAB, fn () => BlockFactory::get(BlockIds::WOODEN_SLAB, Planks::DARK_OAK));
		$this->mapDoubleSlab(Ids::DARK_OAK_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_WOODEN_SLAB, Planks::DARK_OAK));
		$this->mapStairs(Ids::DARK_OAK_STAIRS, fn () => BlockFactory::get(BlockIds::DARK_OAK_STAIRS));

		$this->map(Ids::JUNGLE_BUTTON, fn (Reader $in) => Helper::decodeButton(BlockFactory::get(BlockIds::JUNGLE_BUTTON), $in));
		$this->map(Ids::JUNGLE_DOOR, fn (Reader $in) => Helper::decodeDoor(BlockFactory::get(BlockIds::JUNGLE_DOOR_BLOCK), $in));
		$this->map(Ids::JUNGLE_FENCE_GATE, fn (Reader $in) => Helper::decodeFenceGate(BlockFactory::get(BlockIds::JUNGLE_FENCE_GATE), $in));
		$this->mapSimple(Ids::JUNGLE_PRESSURE_PLATE, fn () => BlockFactory::get(BlockIds::JUNGLE_PRESSURE_PLATE));
		$this->map(Ids::JUNGLE_TRAPDOOR, fn (Reader $in) => Helper::decodeTrapdoor(BlockFactory::get(BlockIds::JUNGLE_TRAPDOOR), $in));
		$this->map(Ids::JUNGLE_WALL_SIGN, fn (Reader $in) => Helper::decodeWallSign(BlockFactory::get(BlockIds::JUNGLE_WALL_SIGN), $in));
		$this->mapLog(Ids::JUNGLE_LOG, Ids::STRIPPED_JUNGLE_LOG, fn () => BlockFactory::get(BlockIds::LOG, Log::JUNGLE));
		$this->mapLog(Ids::JUNGLE_WOOD, Ids::STRIPPED_JUNGLE_WOOD, fn () => BlockFactory::get(BlockIds::LOG, Log::JUNGLE));
		$this->mapSimple(Ids::JUNGLE_FENCE, fn () => BlockFactory::get(BlockIds::FENCE, WoodenFence::FENCE_JUNGLE));
		$this->mapSimple(Ids::JUNGLE_PLANKS, fn () => BlockFactory::get(BlockIds::PLANKS, Planks::JUNGLE));
		$this->mapSingleSlab(Ids::JUNGLE_SLAB, fn () => BlockFactory::get(BlockIds::WOODEN_SLAB, Planks::JUNGLE));
		$this->mapDoubleSlab(Ids::JUNGLE_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_WOODEN_SLAB, Planks::JUNGLE));
		$this->mapStairs(Ids::JUNGLE_STAIRS, fn () => BlockFactory::get(BlockIds::JUNGLE_STAIRS));

		//oak - due to age, many of these don't specify "oak", making for confusing reading
		$this->map(Ids::WOODEN_BUTTON, fn (Reader $in) => Helper::decodeButton(BlockFactory::get(BlockIds::WOODEN_BUTTON), $in));
		$this->map(Ids::WOODEN_DOOR, fn (Reader $in) => Helper::decodeDoor(BlockFactory::get(BlockIds::WOODEN_DOOR_BLOCK), $in));
		$this->map(Ids::FENCE_GATE, fn (Reader $in) => Helper::decodeFenceGate(BlockFactory::get(BlockIds::OAK_FENCE_GATE), $in));
		$this->mapSimple(Ids::WOODEN_PRESSURE_PLATE, fn () => BlockFactory::get(BlockIds::WOODEN_PRESSURE_PLATE));
		$this->map(Ids::TRAPDOOR, fn (Reader $in) => Helper::decodeTrapdoor(BlockFactory::get(BlockIds::WOODEN_TRAPDOOR), $in));
		$this->map(Ids::WALL_SIGN, fn (Reader $in) => Helper::decodeWallSign(BlockFactory::get(BlockIds::WALL_SIGN), $in));
		$this->mapLog(Ids::OAK_LOG, Ids::STRIPPED_OAK_LOG, fn () => BlockFactory::get(BlockIds::LOG, Log::OAK));
		$this->mapLog(Ids::OAK_WOOD, Ids::STRIPPED_OAK_WOOD, fn () => BlockFactory::get(BlockIds::LOG, Log::OAK));
		$this->mapSimple(Ids::OAK_FENCE, fn () => BlockFactory::get(BlockIds::FENCE, WoodenFence::FENCE_OAK));
		$this->mapSimple(Ids::OAK_PLANKS, fn () => BlockFactory::get(BlockIds::PLANKS, Planks::OAK));
		$this->mapSingleSlab(Ids::OAK_SLAB, fn () => BlockFactory::get(BlockIds::WOODEN_SLAB, Planks::OAK));
		$this->mapDoubleSlab(Ids::OAK_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_WOODEN_SLAB, Planks::OAK));
		$this->mapStairs(Ids::OAK_STAIRS, fn () => BlockFactory::get(BlockIds::WOODEN_STAIRS));

		$this->map(Ids::SPRUCE_BUTTON, fn (Reader $in) => Helper::decodeButton(BlockFactory::get(BlockIds::SPRUCE_BUTTON), $in));
		$this->map(Ids::SPRUCE_DOOR, fn (Reader $in) => Helper::decodeDoor(BlockFactory::get(BlockIds::SPRUCE_DOOR_BLOCK), $in));
		$this->map(Ids::SPRUCE_FENCE_GATE, fn (Reader $in) => Helper::decodeFenceGate(BlockFactory::get(BlockIds::SPRUCE_FENCE_GATE), $in));
		$this->mapSimple(Ids::SPRUCE_PRESSURE_PLATE, fn () => BlockFactory::get(BlockIds::SPRUCE_PRESSURE_PLATE));
		$this->map(Ids::SPRUCE_TRAPDOOR, fn (Reader $in) => Helper::decodeTrapdoor(BlockFactory::get(BlockIds::SPRUCE_TRAPDOOR), $in));
		$this->map(Ids::SPRUCE_WALL_SIGN, fn (Reader $in) => Helper::decodeWallSign(BlockFactory::get(BlockIds::SPRUCE_WALL_SIGN), $in));
		$this->mapLog(Ids::SPRUCE_LOG, Ids::STRIPPED_SPRUCE_LOG, fn () => BlockFactory::get(BlockIds::LOG, Log::SPRUCE));
		$this->mapLog(Ids::SPRUCE_WOOD, Ids::STRIPPED_SPRUCE_WOOD, fn () => BlockFactory::get(BlockIds::LOG, Log::SPRUCE));
		$this->mapSimple(Ids::SPRUCE_FENCE, fn () => BlockFactory::get(BlockIds::FENCE, WoodenFence::FENCE_SPRUCE));
		$this->mapSimple(Ids::SPRUCE_PLANKS, fn () => BlockFactory::get(BlockIds::PLANKS, Planks::SPRUCE));
		$this->mapSingleSlab(Ids::SPRUCE_SLAB, fn () => BlockFactory::get(BlockIds::WOODEN_SLAB, Planks::SPRUCE));
		$this->mapDoubleSlab(Ids::SPRUCE_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_WOODEN_SLAB, Planks::SPRUCE));
		$this->mapStairs(Ids::SPRUCE_STAIRS, fn () => BlockFactory::get(BlockIds::SPRUCE_STAIRS));
	}

	private function registerLeavesDeserializers() : void
	{
		$this->map(Ids::ACACIA_LEAVES, fn (Reader $in) => Helper::decodeLeaves(BlockFactory::get(BlockIds::LEAVES2, Leaves::ACACIA), $in));
		$this->map(Ids::BIRCH_LEAVES, fn (Reader $in) => Helper::decodeLeaves(BlockFactory::get(BlockIds::LEAVES, Leaves::BIRCH), $in));
		$this->map(Ids::DARK_OAK_LEAVES, fn (Reader $in) => Helper::decodeLeaves(BlockFactory::get(BlockIds::LEAVES2, Leaves::DARK_OAK), $in));
		$this->map(Ids::JUNGLE_LEAVES, fn (Reader $in) => Helper::decodeLeaves(BlockFactory::get(BlockIds::LEAVES, Leaves::JUNGLE), $in));
		$this->map(Ids::OAK_LEAVES, fn (Reader $in) => Helper::decodeLeaves(BlockFactory::get(BlockIds::LEAVES, Leaves::OAK), $in));
		$this->map(Ids::SPRUCE_LEAVES, fn (Reader $in) => Helper::decodeLeaves(BlockFactory::get(BlockIds::LEAVES, Leaves::SPRUCE), $in));
	}

	private function registerSaplingDeserializers() : void
	{
		foreach ([
			Ids::ACACIA_SAPLING => fn () => BlockFactory::get(BlockIds::SAPLING, Sapling::ACACIA),
			Ids::BIRCH_SAPLING => fn () => BlockFactory::get(BlockIds::SAPLING, Sapling::BIRCH),
			Ids::DARK_OAK_SAPLING => fn () => BlockFactory::get(BlockIds::SAPLING, Sapling::DARK_OAK),
			Ids::JUNGLE_SAPLING => fn () => BlockFactory::get(BlockIds::SAPLING, Sapling::JUNGLE),
			Ids::OAK_SAPLING => fn () => BlockFactory::get(BlockIds::SAPLING, Sapling::OAK),
			Ids::SPRUCE_SAPLING => fn () => BlockFactory::get(BlockIds::SAPLING, Sapling::SPRUCE),
		] as $id => $getBlock) {
			$this->map($id, fn (Reader $in) => Helper::decodeSapling($getBlock(), $in));
		}
	}

	private function registerMobHeadDeserializers() : void
	{
		foreach ([
			Ids::CREEPER_HEAD => Skull::TYPE_CREEPER,
			Ids::DRAGON_HEAD => Skull::TYPE_DRAGON,
			Ids::PLAYER_HEAD => Skull::TYPE_PLAYER,
			Ids::PIGLIN_HEAD => Skull::TYPE_PIGLIN,
			Ids::SKELETON_SKULL => Skull::TYPE_SKELETON,
			Ids::WITHER_SKELETON_SKULL => Skull::TYPE_WITHER_SKELETON,
			Ids::ZOMBIE_HEAD => Skull::TYPE_ZOMBIE
		] as $id => $mobHeadTypeId) {
			$this->map($id, fn (Reader $in) => BlockFactory::get(BlockIds::SKULL_BLOCK, ($mobHeadTypeId * 0x06) | $in->readFacingWithoutDown()));
		}
	}

	private function registerSimpleDeserializers() : void
	{
		$this->mapSimple(Ids::AIR, fn () => BlockFactory::get(BlockIds::AIR));
		$this->mapSimple(Ids::ANCIENT_DEBRIS, fn () => BlockFactory::get(BlockIds::ANCIENT_DEBRIS));
		$this->mapSimple(Ids::ANDESITE, fn () => BlockFactory::get(BlockIds::STONE, Stone::ANDESITE));
		$this->mapSimple(Ids::BARRIER, fn () => BlockFactory::get(BlockIds::BARRIER));
		$this->mapSimple(Ids::BEACON, fn () => BlockFactory::get(BlockIds::BEACON));
		$this->mapSimple(Ids::BLACKSTONE, fn () => BlockFactory::get(BlockIds::BLACKSTONE));
		$this->mapSimple(Ids::BLUE_ICE, fn () => BlockFactory::get(BlockIds::BLUE_ICE));
		$this->mapSimple(Ids::BOOKSHELF, fn () => BlockFactory::get(BlockIds::BOOKSHELF));
		$this->mapSimple(Ids::BRICK_BLOCK, fn () => BlockFactory::get(BlockIds::STONE_BRICKS));
		$this->mapSimple(Ids::BROWN_MUSHROOM, fn () => BlockFactory::get(BlockIds::BROWN_MUSHROOM));
		$this->mapSimple(Ids::CHISELED_NETHER_BRICKS, fn () => BlockFactory::get(BlockIds::CHISELED_NETHER_BRICKS));
		$this->mapSimple(Ids::CHISELED_POLISHED_BLACKSTONE, fn () => BlockFactory::get(BlockIds::CHISELED_POLISHED_BLACKSTONE));
		$this->mapSimple(Ids::CHISELED_RED_SANDSTONE, fn () => BlockFactory::get(BlockIds::RED_SANDSTONE, Sandstone::CHISELED));
		$this->mapSimple(Ids::CHISELED_SANDSTONE, fn () => BlockFactory::get(BlockIds::SANDSTONE, Sandstone::CHISELED));
		$this->mapSimple(Ids::CHISELED_STONE_BRICKS, fn () => BlockFactory::get(BlockIds::STONE_BRICKS, StoneBricks::CHISELED));
		$this->mapSimple(Ids::CHORUS_PLANT, fn () => BlockFactory::get(BlockIds::CHORUS_PLANT));
		$this->mapSimple(Ids::CLAY, fn () => BlockFactory::get(BlockIds::CLAY_BLOCK));
		$this->mapSimple(Ids::COAL_BLOCK, fn () => BlockFactory::get(BlockIds::COAL_BLOCK));
		$this->mapSimple(Ids::COAL_ORE, fn () => BlockFactory::get(BlockIds::COAL_ORE));
		$this->mapSimple(Ids::COBBLESTONE, fn () => BlockFactory::get(BlockIds::COBBLESTONE));
		$this->mapSimple(Ids::COBBLESTONE_WALL, fn () => BlockFactory::get(BlockIds::COBBLESTONE_WALL));
		$this->mapSimple(Ids::CRACKED_STONE_BRICKS, fn () => BlockFactory::get(BlockIds::STONE_BRICKS, StoneBricks::CRACKED));
		$this->mapSimple(Ids::CRAFTING_TABLE, fn () => BlockFactory::get(BlockIds::CRAFTING_TABLE));
		$this->mapSimple(Ids::CRIMSON_ROOTS, fn () => BlockFactory::get(BlockIds::CRIMSON_ROOTS));
		$this->mapSimple(Ids::CRYING_OBSIDIAN, fn () => BlockFactory::get(BlockIds::CRYING_OBSIDIAN));
		$this->mapSimple(Ids::CUT_RED_SANDSTONE, fn () => BlockFactory::get(BlockIds::RED_SANDSTONE, Sandstone::SMOOTH));
		$this->mapSimple(Ids::CUT_SANDSTONE, fn () => BlockFactory::get(BlockIds::SANDSTONE, Sandstone::SMOOTH));
		$this->mapSimple(Ids::DARK_PRISMARINE, fn () => BlockFactory::get(BlockIds::PRISMARINE, Prismarine::DARK));
		$this->mapSimple(Ids::DEADBUSH, fn () => BlockFactory::get(BlockIds::DEADBUSH));
		$this->mapSimple(Ids::DIAMOND_BLOCK, fn () => BlockFactory::get(BlockIds::DIAMOND_BLOCK));
		$this->mapSimple(Ids::DIAMOND_ORE, fn () => BlockFactory::get(BlockIds::DIAMOND_ORE));
		$this->mapSimple(Ids::DIORITE, fn () => BlockFactory::get(BlockIds::STONE, Stone::DIORITE));
		$this->mapSimple(Ids::DRAGON_EGG, fn () => BlockFactory::get(BlockIds::DRAGON_EGG));
		$this->mapSimple(Ids::DRIED_KELP_BLOCK, fn () => BlockFactory::get(BlockIds::DRIED_KELP_BLOCK));
		$this->mapSimple(Ids::DROPPER, fn () => BlockFactory::get(BlockIds::DROPPER));
		$this->mapSimple(Ids::EMERALD_BLOCK, fn () => BlockFactory::get(BlockIds::EMERALD_BLOCK));
		$this->mapSimple(Ids::EMERALD_ORE, fn () => BlockFactory::get(BlockIds::EMERALD_ORE));
		$this->mapSimple(Ids::ENCHANTING_TABLE, fn () => BlockFactory::get(BlockIds::ENCHANTING_TABLE));
		$this->mapSimple(Ids::END_BRICKS, fn () => BlockFactory::get(BlockIds::END_BRICKS));
		$this->mapSimple(Ids::END_STONE, fn () => BlockFactory::get(BlockIds::END_STONE));
		$this->mapSimple(Ids::FERN, fn () => BlockFactory::get(BlockIds::TALL_GRASS, TallGrass::TYPE_FERN));
		$this->mapSimple(Ids::GLASS, fn () => BlockFactory::get(BlockIds::GLASS));
		$this->mapSimple(Ids::GLASS_PANE, fn () => BlockFactory::get(BlockIds::GLASS_PANE));
		$this->mapSimple(Ids::GLOWINGOBSIDIAN, fn () => BlockFactory::get(BlockIds::GLOWING_OBSIDIAN));
		$this->mapSimple(Ids::GLOWSTONE, fn () => BlockFactory::get(BlockIds::GLOWSTONE));
		$this->mapSimple(Ids::GOLD_BLOCK, fn () => BlockFactory::get(BlockIds::GOLD_BLOCK));
		$this->mapSimple(Ids::GOLD_ORE, fn () => BlockFactory::get(BlockIds::GOLD_ORE));
		$this->mapSimple(Ids::GRANITE, fn () => BlockFactory::get(BlockIds::STONE, Stone::GRANITE));
		$this->mapSimple(Ids::GRASS_BLOCK, fn () => BlockFactory::get(BlockIds::GRASS));
		$this->mapSimple(Ids::GRASS_PATH, fn () => BlockFactory::get(BlockIds::GRASS_PATH));
		$this->mapSimple(Ids::GRAVEL, fn () => BlockFactory::get(BlockIds::GRAVEL));
		$this->mapSimple(Ids::HARD_GLASS, fn () => BlockFactory::get(BlockIds::HARD_GLASS));
		$this->mapSimple(Ids::HARDENED_CLAY, fn () => BlockFactory::get(BlockIds::HARDENED_CLAY));
		$this->mapSimple(Ids::HONEYCOMB_BLOCK, fn () => BlockFactory::get(BlockIds::HONEYCOMB_BLOCK));
		$this->mapSimple(Ids::ICE, fn () => BlockFactory::get(BlockIds::ICE));
		$this->mapSimple(Ids::INFESTED_CHISELED_STONE_BRICKS, fn () => BlockFactory::get(BlockIds::MONSTER_EGG, 5));
		$this->mapSimple(Ids::INFESTED_COBBLESTONE, fn () => BlockFactory::get(BlockIds::MONSTER_EGG, 1));
		$this->mapSimple(Ids::INFESTED_CRACKED_STONE_BRICKS, fn () => BlockFactory::get(BlockIds::MONSTER_EGG, 4));
		$this->mapSimple(Ids::INFESTED_MOSSY_STONE_BRICKS, fn () => BlockFactory::get(BlockIds::MONSTER_EGG, 3));
		$this->mapSimple(Ids::INFESTED_STONE, fn () => BlockFactory::get(BlockIds::MONSTER_EGG));
		$this->mapSimple(Ids::INFESTED_STONE_BRICKS, fn () => BlockFactory::get(BlockIds::MONSTER_EGG, 2));
		$this->mapSimple(Ids::INFO_UPDATE, fn () => BlockFactory::get(BlockIds::INFO_UPDATE));
		$this->mapSimple(Ids::INFO_UPDATE2, fn () => BlockFactory::get(BlockIds::INFO_UPDATE2));
		$this->mapSimple(Ids::INVISIBLE_BEDROCK, fn () => BlockFactory::get(BlockIds::INVISIBLE_BEDROCK));
		$this->mapSimple(Ids::IRON_BARS, fn () => BlockFactory::get(BlockIds::IRON_BARS));
		$this->mapSimple(Ids::IRON_BLOCK, fn () => BlockFactory::get(BlockIds::IRON_BLOCK));
		$this->mapSimple(Ids::IRON_ORE, fn () => BlockFactory::get(BlockIds::IRON_ORE));
		$this->mapSimple(Ids::JUKEBOX, fn () => BlockFactory::get(BlockIds::JUKEBOX));
		$this->mapSimple(Ids::LAPIS_BLOCK, fn () => BlockFactory::get(BlockIds::LAPIS_BLOCK));
		$this->mapSimple(Ids::LAPIS_ORE, fn () => BlockFactory::get(BlockIds::LAPIS_ORE));
		$this->mapSimple(Ids::MAGMA, fn () => BlockFactory::get(BlockIds::MAGMA));
		$this->mapSimple(Ids::MELON_BLOCK, fn () => BlockFactory::get(BlockIds::MELON_BLOCK));
		$this->mapSimple(Ids::MOB_SPAWNER, fn () => BlockFactory::get(BlockIds::MONSTER_SPAWNER));
		$this->mapSimple(Ids::MOSSY_COBBLESTONE, fn () => BlockFactory::get(BlockIds::MOSSY_COBBLESTONE));
		$this->mapSimple(Ids::MOSSY_STONE_BRICKS, fn () => BlockFactory::get(BlockIds::STONE_BRICKS, StoneBricks::MOSSY));
		$this->mapSimple(Ids::MYCELIUM, fn () => BlockFactory::get(BlockIds::MYCELIUM));
		$this->mapSimple(Ids::NETHER_BRICK, fn () => BlockFactory::get(BlockIds::NETHER_BRICK_BLOCK));
		$this->mapSimple(Ids::NETHER_BRICK_FENCE, fn () => BlockFactory::get(BlockIds::NETHER_BRICK_FENCE));
		$this->mapSimple(Ids::NETHER_GOLD_ORE, fn () => BlockFactory::get(BlockIds::NETHER_GOLD_ORE));
		$this->mapSimple(Ids::NETHER_WART_BLOCK, fn () => BlockFactory::get(BlockIds::NETHER_WART_BLOCK));
		$this->mapSimple(Ids::NETHERITE_BLOCK, fn () => BlockFactory::get(BlockIds::NETHERITE_BLOCK));
		$this->mapSimple(Ids::NETHERRACK, fn () => BlockFactory::get(BlockIds::NETHERRACK));
		$this->mapSimple(Ids::NETHERREACTOR, fn () => BlockFactory::get(BlockIds::NETHERREACTOR));
		$this->mapSimple(Ids::NOTEBLOCK, fn () => BlockFactory::get(BlockIds::NOTEBLOCK));
		$this->mapSimple(Ids::OBSIDIAN, fn () => BlockFactory::get(BlockIds::OBSIDIAN));
		$this->mapSimple(Ids::PACKED_ICE, fn () => BlockFactory::get(BlockIds::PACKED_ICE));
		$this->mapSimple(Ids::PODZOL, fn () => BlockFactory::get(BlockIds::PODZOL));
		$this->mapSimple(Ids::POLISHED_ANDESITE, fn () => BlockFactory::get(BlockIds::STONE, Stone::POLISHED_ANDESITE));
		$this->mapSimple(Ids::POLISHED_BLACKSTONE, fn () => BlockFactory::get(Stone::POLISHED_BLACKSTONE));
		$this->mapSimple(Ids::POLISHED_BLACKSTONE_BRICKS, fn () => BlockFactory::get(Stone::POLISHED_BLACKSTONE_BRICKS));
		$this->mapSimple(Ids::POLISHED_DIORITE, fn () => BlockFactory::get(BlockIds::STONE, Stone::POLISHED_ANDESITE));
		$this->mapSimple(Ids::POLISHED_GRANITE, fn () => BlockFactory::get(BlockIds::STONE, Stone::POLISHED_GRANITE));
		$this->mapSimple(Ids::PRISMARINE, fn () => BlockFactory::get(BlockIds::PRISMARINE));
		$this->mapSimple(Ids::PRISMARINE_BRICKS, fn () => BlockFactory::get(BlockIds::PRISMARINE, Prismarine::BRICKS));
		$this->mapSimple(Ids::QUARTZ_BRICKS, fn () => BlockFactory::get(BlockIds::QUARTZ_BRICKS));
		$this->mapSimple(Ids::QUARTZ_ORE, fn () => BlockFactory::get(BlockIds::QUARTZ_ORE));
		$this->mapSimple(Ids::RED_MUSHROOM, fn () => BlockFactory::get(BlockIds::RED_MUSHROOM));
		$this->mapSimple(Ids::RED_NETHER_BRICK, fn () => BlockFactory::get(BlockIds::RED_NETHER_BRICK));
		$this->mapSimple(Ids::RED_SAND, fn () => BlockFactory::get(BlockIds::SAND, Sand::TYPE_RED));
		$this->mapSimple(Ids::RED_SANDSTONE, fn () => BlockFactory::get(BlockIds::RED_SANDSTONE));
		$this->mapSimple(Ids::REDSTONE_BLOCK, fn () => BlockFactory::get(BlockIds::REDSTONE_BLOCK));
		$this->mapSimple(Ids::RESERVED6, fn () => BlockFactory::get(BlockIds::RESERVED6));
		$this->mapSimple(Ids::SAND, fn () => BlockFactory::get(BlockIds::SAND));
		$this->mapSimple(Ids::SANDSTONE, fn () => BlockFactory::get(BlockIds::SANDSTONE));
		$this->mapSimple(Ids::SEA_LANTERN, fn () => BlockFactory::get(BlockIds::SEA_LANTERN));
		$this->mapSimple(Ids::SHORT_GRASS, fn () => BlockFactory::get(BlockIds::TALLGRASS)); //no, this is not a typo - tall_grass is now the double block, just to be confusing :(
		$this->mapSimple(Ids::SHROOMLIGHT, fn () => BlockFactory::get(BlockIds::SHROOMLIGHT));
		$this->mapSimple(Ids::SLIME, fn () => BlockFactory::get(BlockIds::SLIME));
		$this->mapSimple(Ids::SMOOTH_RED_SANDSTONE, fn () => BlockFactory::get(BlockIds::RED_SANDSTONE, Sandstone::SMOOTH));
		$this->mapSimple(Ids::SMOOTH_SANDSTONE, fn () => BlockFactory::get(BlockIds::SANDSTONE, Sandstone::SMOOTH));
		$this->mapSimple(Ids::SMOOTH_STONE, fn () => BlockFactory::get(BlockIds::SMOOTH_STONE));
		$this->mapSimple(Ids::SNOW, fn () => BlockFactory::get(BlockIds::SNOW));
		$this->mapSimple(Ids::SOUL_SAND, fn () => BlockFactory::get(BlockIds::SOUL_SAND));
		$this->mapSimple(Ids::SOUL_SOIL, fn () => BlockFactory::get(BlockIds::SOUL_SOIL));
		$this->mapSimple(Ids::SPONGE, fn () => BlockFactory::get(BlockIds::SPONGE));
		$this->mapSimple(Ids::STICKY_PISTON, fn () => BlockFactory::get(BlockIds::STICKY_PISTON));
		$this->mapSimple(Ids::STICKY_PISTON_ARM_COLLISION, fn () => BlockFactory::get(BlockIds::STICKY_PISTON_ARM_COLLISION));
		$this->mapSimple(Ids::STONE, fn () => BlockFactory::get(BlockIds::STONE));
		$this->mapSimple(Ids::STONECUTTER, fn () => BlockFactory::get(BlockIds::STONECUTTER));
		$this->mapSimple(Ids::STONE_BRICKS, fn () => BlockFactory::get(BlockIds::STONE_BRICKS));
		$this->mapSimple(Ids::UNDYED_SHULKER_BOX, fn () => BlockFactory::get(BlockIds::SHULKER_BOX));
		$this->mapSimple(Ids::WARPED_ROOTS, fn () => BlockFactory::get(BlockIds::WARPED_ROOTS));
		$this->mapSimple(Ids::WATERLILY, fn () => BlockFactory::get(BlockIds::WATERLILY));
		$this->mapSimple(Ids::WEB, fn () => BlockFactory::get(BlockIds::WEB));
		$this->mapSimple(Ids::WET_SPONGE, fn () => BlockFactory::get(BlockIds::SPONGE, 1));
		$this->mapSimple(Ids::WITHER_ROSE, fn () => BlockFactory::get(BlockIds::WITHER_ROSE));
		$this->mapSimple(Ids::DANDELION, fn () => BlockFactory::get(BlockIds::DANDELION));

		$this->mapSimple(Ids::ALLIUM, fn () => BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_ALLIUM));
		$this->mapSimple(Ids::CORNFLOWER, fn () => BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_CORNFLOWER));
		$this->mapSimple(Ids::AZURE_BLUET, fn () => BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_AZURE_BLUET));
		$this->mapSimple(Ids::LILY_OF_THE_VALLEY, fn () => BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_LILY_OF_THE_VALLEY));
		$this->mapSimple(Ids::BLUE_ORCHID, fn () => BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_BLUE_ORCHID));
		$this->mapSimple(Ids::OXEYE_DAISY, fn () => BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_OXEYE_DAISY));
		$this->mapSimple(Ids::POPPY, fn () => BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_POPPY));
		$this->mapSimple(Ids::ORANGE_TULIP, fn () => BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_ORANGE_TULIP));
		$this->mapSimple(Ids::PINK_TULIP, fn () => BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_PINK_TULIP));
		$this->mapSimple(Ids::RED_TULIP, fn () => BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_RED_TULIP));
		$this->mapSimple(Ids::WHITE_TULIP, fn () => BlockFactory::get(BlockIds::RED_FLOWER, Flower::TYPE_WHITE_TULIP));
	}

	private function registerDeserializers() : void
	{
		//$this->map(Ids::ACTIVATOR_RAIL, function(Reader $in) : Block{
		//    return BlockFactory::get(BlockIds::ACTIVATOR_RAIL,
		//            $in->readBoundedInt(StateNames::RAIL_DIRECTION, 0, 5) |
		//            ($in->readBool(StateNames::RAIL_DATA_BIT) ? 0x08 : 0)
		//        );
		//});
		$this->mapStairs(Ids::ANDESITE_STAIRS, fn () => BlockFactory::get(BlockIds::ANDESITE_STAIRS));
		$this->map(Ids::ANVIL, function (Reader $in) : Block {
			return BlockFactory::get(
				BlockIds::ANVIL,
				$in->readCardinalHorizontalFacing() |
					(Anvil::TYPE_NORMAL >> 2)
			);
		});
		$this->map(Ids::CHIPPED_ANVIL, function (Reader $in) : Block {
			return BlockFactory::get(
				BlockIds::ANVIL,
				$in->readCardinalHorizontalFacing() |
					(Anvil::TYPE_SLIGHTLY_DAMAGED >> 2)
			);
		});
		$this->map(Ids::DAMAGED_ANVIL, function (Reader $in) : Block {
			return BlockFactory::get(
				BlockIds::ANVIL,
				$in->readCardinalHorizontalFacing() |
					(Anvil::TYPE_VERY_DAMAGED >> 2)
			);
		});
		$this->map(Ids::BED, function (Reader $in) : Block {
			return BlockFactory::get(
				BlockIds::BED_BLOCK,
				$in->readLegacyHorizontalFacing() |
					($in->readBool(StateNames::OCCUPIED_BIT) ? Bed::BITFLAG_OCCUPIED : 0) |
					($in->readBool(StateNames::HEAD_PIECE_BIT) ? Bed::BITFLAG_HEAD : 0)
			);
		});
		$this->map(Ids::BEDROCK, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::BEDROCK, $in->readBool(StateNames::INFINIBURN_BIT) ? 0x01 : 0);
		});
		$this->map(Ids::BEETROOT, fn (Reader $in) => Helper::decodeCrops(BlockFactory::get(BlockIds::BEETROOT_BLOCK), $in));
		$this->mapStairs(Ids::BLACKSTONE_STAIRS, fn () => BlockFactory::get(BlockIds::BLACKSTONE_STAIRS));
		$this->map(Ids::BONE_BLOCK, function (Reader $in) : Block {
			$in->ignored(StateNames::DEPRECATED);
			return BlockFactory::get(BlockIds::BONE_BLOCK, $in->readPillarAxis());
		});
		$this->mapSingleSlab(Ids::BRICK_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB, StoneSlab::BRICK));
		$this->mapDoubleSlab(Ids::BRICK_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_STONE_SLAB, StoneSlab::BRICK));
		$this->mapStairs(Ids::BRICK_STAIRS, fn () => BlockFactory::get(BlockIds::BRICK_STAIRS));
		$this->map(Ids::MUSHROOM_STEM, fn (Reader $in) => match($in->readBoundedInt(StateNames::HUGE_MUSHROOM_BITS, 0, 15)) {
			15 => BlockFactory::get(BlockIds::BROWN_MUSHROOM_BLOCK, 15),
			10 => BlockFactory::get(BlockIds::BROWN_MUSHROOM_BLOCK, 10),
			default => throw new BlockStateDeserializeException("This state does not exist"),
		});
		$this->map(Ids::BROWN_MUSHROOM_BLOCK, fn (Reader $in) => Helper::decodeMushroomBlock(BlockFactory::get(BlockIds::BROWN_MUSHROOM_BLOCK), $in));
		$this->map(Ids::CACTUS, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::CACTUS, $in->readBoundedInt(StateNames::AGE, 0, 15));
		});
		$this->map(Ids::CAKE, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::CAKE_BLOCK, $in->readBoundedInt(StateNames::BITE_COUNTER, 0, 6));
		});
		$this->map(Ids::CARROTS, fn (Reader $in) => Helper::decodeCrops(BlockFactory::get(BlockIds::CARROTS), $in));
		$this->map(Ids::CARVED_PUMPKIN, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::CARVED_PUMPKIN, $in->readCardinalHorizontalFacing());
		});
		$this->map(Ids::CHAIN, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::CHAIN, $in->readPillarAxis());
		});
		$this->map(Ids::CHEST, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::CHEST, $in->readCardinalHorizontalFacing());
		});
		$this->map(Ids::CHISELED_QUARTZ_BLOCK, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::QUARTZ_BLOCK, Quartz::CHISELED | $in->readPillarAxis());
		});
		$this->map(Ids::CHORUS_FLOWER, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::CHORUS_FLOWER, $in->readBoundedInt(StateNames::AGE, 0, 5));
		});
		$this->map(Ids::COARSE_DIRT, fn () => BlockFactory::get(BlockIds::DIRT, Dirt::TYPE_COARSE));
		$this->map(Ids::COCOA, function (Reader $in) : Block {
			return BlockFactory::get(
				BlockIds::COCOA_BLOCK,
				Facing::opposite($in->readLegacyHorizontalFacing()) |
				($in->readBoundedInt(StateNames::AGE, 0, 2) << 2)
			);
		});

		$this->mapSingleSlab(Ids::CUT_RED_SANDSTONE_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB4, StoneSlab4::TYPE_CUT_RED_SANDSTONE));
		$this->mapDoubleSlab(Ids::CUT_RED_SANDSTONE_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_STONE_SLAB4, StoneSlab4::TYPE_CUT_RED_SANDSTONE));
		$this->mapSingleSlab(Ids::CUT_SANDSTONE_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB4, StoneSlab4::TYPE_CUT_SANDSTONE));
		$this->mapDoubleSlab(Ids::CUT_SANDSTONE_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_STONE_SLAB4, StoneSlab4::TYPE_CUT_SANDSTONE));
		$this->mapSingleSlab(Ids::COBBLESTONE_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB, StoneSlab::COBBLESTONE));
		$this->mapDoubleSlab(Ids::COBBLESTONE_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB, StoneSlab::COBBLESTONE));
		$this->mapSingleSlab(Ids::DARK_PRISMARINE_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB2, StoneSlab2::TYPE_DARK_PRISMARINE));
		$this->mapDoubleSlab(Ids::DARK_PRISMARINE_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_STONE_SLAB2, StoneSlab2::TYPE_DARK_PRISMARINE));
		$this->mapStairs(Ids::DARK_PRISMARINE_STAIRS, fn () => BlockFactory::get(BlockIds::DARK_PRISMARINE_STAIRS));
		$this->map(Ids::DAYLIGHT_DETECTOR, fn (Reader $in) => BlockFactory::get(BlockIds::DAYLIGHT_DETECTOR));
		$this->map(Ids::DAYLIGHT_DETECTOR_INVERTED, fn (Reader $in) => BlockFactory::get(BlockIds::DAYLIGHT_DETECTOR_INVERTED));
		$this->map(Ids::DETECTOR_RAIL, function (Reader $in) : Block {
			return BlockFactory::get(
				BlockIds::DETECTOR_RAIL,
				$in->readBoundedInt(StateNames::RAIL_DIRECTION, 0, 5) |
				($in->readBool(StateNames::RAIL_DATA_BIT) ? 0x08 : 0)
			);
		});
		$this->mapSingleSlab(Ids::DIORITE_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB3, StoneSlab3::TYPE_DIORITE));
		$this->mapDoubleSlab(Ids::DIORITE_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_STONE_SLAB3, StoneSlab3::TYPE_DIORITE));
		$this->mapStairs(Ids::DIORITE_STAIRS, fn () => BlockFactory::get(BlockIds::DIORITE_STAIRS));
		$this->map(Ids::DIRT, fn () => BlockFactory::get(BlockIds::DIRT, Dirt::TYPE_NORMAL));
		$this->map(Ids::DIRT_WITH_ROOTS, fn () => BlockFactory::get(BlockIds::DIRT, Dirt::TYPE_COARSE));
		$this->map(Ids::LARGE_FERN, fn (Reader $in) => Helper::decodeDoublePlant(BlockFactory::get(BlockIds::DOUBLE_PLANT, DoublePlant::TYPE_LARGE_FERN), $in));
		$this->map(Ids::TALL_GRASS, fn (Reader $in) => Helper::decodeDoublePlant(BlockFactory::get(BlockIds::DOUBLE_PLANT, DoublePlant::TYPE_DOUBLE_TALLGRASS), $in));
		$this->map(Ids::PEONY, fn (Reader $in) => Helper::decodeDoublePlant(BlockFactory::get(BlockIds::DOUBLE_PLANT, DoublePlant::TYPE_PEONY), $in));
		$this->map(Ids::ROSE_BUSH, fn (Reader $in) => Helper::decodeDoublePlant(BlockFactory::get(BlockIds::DOUBLE_PLANT, DoublePlant::TYPE_ROSE_BUSH), $in));
		$this->map(Ids::SUNFLOWER, fn (Reader $in) => Helper::decodeDoublePlant(BlockFactory::get(BlockIds::DOUBLE_PLANT, DoublePlant::TYPE_SUNFLOWER), $in));
		$this->map(Ids::LILAC, fn (Reader $in) => Helper::decodeDoublePlant(BlockFactory::get(BlockIds::DOUBLE_PLANT, DoublePlant::TYPE_LILAC), $in));
		$this->mapStairs(Ids::END_BRICK_STAIRS, fn () => BlockFactory::get(BlockIds::END_BRICK_STAIRS));
		$this->map(Ids::END_PORTAL_FRAME, function (Reader $in) : Block {
			return BlockFactory::get(
				BlockIds::END_PORTAL_FRAME,
				$in->readCardinalHorizontalFacing() |
				($in->readBool(StateNames::END_PORTAL_EYE_BIT) ? 0x04 : 0)
			);
		});
		$this->map(Ids::END_ROD, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::END_ROD, $in->readEndRodFacingDirection());
		});
		$this->mapSingleSlab(Ids::END_STONE_BRICK_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB3, StoneSlab3::TYPE_END_STONE_BRICK));
		$this->mapDoubleSlab(Ids::END_STONE_BRICK_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_STONE_SLAB3, StoneSlab3::TYPE_END_STONE_BRICK));
		$this->map(Ids::ENDER_CHEST, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::ENDER_CHEST, $in->readCardinalHorizontalFacing());
		});
		$this->map(Ids::FARMLAND, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::FARMLAND, $in->readBoundedInt(StateNames::MOISTURIZED_AMOUNT, 0, 7));
		});
		$this->map(Ids::FIRE, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::FIRE, $in->readBoundedInt(StateNames::AGE, 0, 15));
		});
		$this->map(Ids::FLOWER_POT, function (Reader $in) : Block {
			$in->ignored(StateNames::UPDATE_BIT);
			return BlockFactory::get(BlockIds::FLOWER_POT_BLOCK);
		});
		$this->map(Ids::FLOWING_LAVA, fn (Reader $in) => Helper::decodeLiquid(BlockFactory::get(BlockIds::FLOWING_LAVA), $in));
		$this->map(Ids::FLOWING_WATER, fn (Reader $in) => Helper::decodeLiquid(BlockFactory::get(BlockIds::FLOWING_WATER), $in));
		$this->map(Ids::FRAME, fn (Reader $in) => Helper::decodeItemFrame(BlockFactory::get(BlockIds::ITEM_FRAME_BLOCK), $in));
		$this->map(Ids::FROSTED_ICE, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::FROSTED_ICE, $in->readBoundedInt(StateNames::AGE, 0, 3));
		});
		$this->map(Ids::FURNACE, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::FURNACE, $in->readCardinalHorizontalFacing());
		});
		$this->map(Ids::GOLDEN_RAIL, function (Reader $in) : Block {
			return BlockFactory::get(
				BlockIds::GOLDEN_RAIL,
				$in->readBoundedInt(StateNames::RAIL_DIRECTION, 0, 5) |
				($in->readBool(StateNames::RAIL_DATA_BIT) ? 0x08 : 0)
			);
		});
		$this->mapSingleSlab(Ids::GRANITE_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB3, StoneSlab3::TYPE_GRANITE));
		$this->mapDoubleSlab(Ids::GRANITE_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_STONE_SLAB3, StoneSlab3::TYPE_GRANITE));
		$this->mapStairs(Ids::GRANITE_STAIRS, fn () => BlockFactory::get(BlockIds::GRANITE_STAIRS));
		$this->map(Ids::HAY_BLOCK, function (Reader $in) : Block {
			$in->ignored(StateNames::DEPRECATED);
			return BlockFactory::get(BlockIds::HAY_BLOCK, $in->readPillarAxis());
		});
		$this->map(Ids::HOPPER, function (Reader $in) : Block {
			return BlockFactory::get(
				BlockIds::HOPPER_BLOCK,
				$in->readFacingWithoutUp() |
				($in->readBool(StateNames::TOGGLE_BIT) ? 0x08 : 0)
			);
		});
		$this->map(Ids::IRON_DOOR, fn (Reader $in) => Helper::decodeDoor(BlockFactory::get(BlockIds::IRON_DOOR_BLOCK), $in));
		$this->map(Ids::IRON_TRAPDOOR, fn (Reader $in) => Helper::decodeTrapdoor(BlockFactory::get(BlockIds::IRON_TRAPDOOR), $in));
		$this->map(Ids::LADDER, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::LADDER, $in->readHorizontalFacing());
		});
		$this->map(Ids::LANTERN, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::LANTERN, $in->readBool(StateNames::HANGING) ? 0x01 : 0);
		});
		$this->map(Ids::LAVA, fn (Reader $in) => Helper::decodeLiquid(BlockFactory::get(BlockIds::STILL_LAVA), $in));
		$this->map(Ids::LECTERN, function (Reader $in) : Block {
			return BlockFactory::get(
				BlockIds::LECTERN,
				$in->readCardinalHorizontalFacing() |
				($in->readBool(StateNames::POWERED_BIT) ? 0x04 : 0)
			);
		});
		$this->map(Ids::LEVER, function (Reader $in) : Block {
			return BlockFactory::get(
				BlockIds::LEVER,
				match($value = $in->readString(StateNames::LEVER_DIRECTION)) {
					StringValues::LEVER_DIRECTION_DOWN_NORTH_SOUTH, StringValues::LEVER_DIRECTION_DOWN_EAST_WEST => Facing::DOWN,
					StringValues::LEVER_DIRECTION_UP_NORTH_SOUTH, StringValues::LEVER_DIRECTION_UP_EAST_WEST => Facing::UP,
					StringValues::LEVER_DIRECTION_NORTH => Facing::NORTH,
					StringValues::LEVER_DIRECTION_SOUTH => Facing::SOUTH,
					StringValues::LEVER_DIRECTION_WEST => Facing::WEST,
					StringValues::LEVER_DIRECTION_EAST => Facing::EAST,
					default => throw $in->badValueException(StateNames::LEVER_DIRECTION, $value),
				} | (
					$in->readBool(StateNames::OPEN_BIT) ? 0x08 : 0
				)
			);
		});
		$this->map(Ids::LIT_FURNACE, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::LIT_FURNACE, $in->readCardinalHorizontalFacing());
		});
		$this->map(Ids::LIT_PUMPKIN, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::LIT_PUMPKIN, $in->readCardinalHorizontalFacing());
		});
		$this->map(Ids::LIT_REDSTONE_LAMP, function () : Block {
			return BlockFactory::get(BlockIds::LIT_REDSTONE_LAMP);
		});
		$this->map(Ids::LIT_REDSTONE_ORE, function () : Block {
			return BlockFactory::get(BlockIds::LIT_REDSTONE_ORE);
		});
		$this->mapSingleSlab(Ids::MOSSY_COBBLESTONE_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB4, StoneSlab4::TYPE_MOSSY_STONE_BRICK));
		$this->mapDoubleSlab(Ids::MOSSY_COBBLESTONE_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_STONE_SLAB4, StoneSlab4::TYPE_MOSSY_STONE_BRICK));
		$this->mapStairs(Ids::MOSSY_COBBLESTONE_STAIRS, fn () => BlockFactory::get(BlockIds::MOSSY_COBBLESTONE_STAIRS));
		$this->mapSingleSlab(Ids::MOSSY_STONE_BRICK_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB4, StoneSlab4::TYPE_STONE));
		$this->mapDoubleSlab(Ids::MOSSY_STONE_BRICK_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_STONE_SLAB4, StoneSlab4::TYPE_STONE));
		$this->mapStairs(Ids::MOSSY_STONE_BRICK_STAIRS, fn () => BlockFactory::get(BlockIds::MOSSY_STONE_BRICK_STAIRS));
		$this->mapSingleSlab(Ids::NETHER_BRICK_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB, StoneSlab::NETHER_BRICK));
		$this->mapDoubleSlab(Ids::NETHER_BRICK_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_STONE_SLAB, StoneSlab::NETHER_BRICK));
		$this->mapStairs(Ids::NETHER_BRICK_STAIRS, fn () => BlockFactory::get(BlockIds::NETHER_BRICK_STAIRS));
		$this->map(Ids::MELON_STEM, fn (Reader $in) => Helper::decodeStem(BlockFactory::get(BlockIds::MELON_STEM), $in));
		$this->map(Ids::NETHER_WART, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::NETHER_WART_BLOCK, $in->readBoundedInt(StateNames::AGE, 0, 3));
		});
		$this->mapSingleSlab(Ids::NORMAL_STONE_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB4, StoneSlab4::TYPE_MOSSY_STONE_BRICK));
		$this->mapDoubleSlab(Ids::NORMAL_STONE_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_STONE_SLAB4, StoneSlab4::TYPE_MOSSY_STONE_BRICK));
		$this->mapStairs(Ids::NORMAL_STONE_STAIRS, fn () => BlockFactory::get(BlockIds::NORMAL_STONE_STAIRS));
		$this->mapSingleSlab(Ids::PETRIFIED_OAK_SLAB, fn () => BlockFactory::get(BlockIds::WOODEN_SLAB, WoodenSlab::TYPE_OAK));
		$this->mapDoubleSlab(Ids::PETRIFIED_OAK_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_WOODEN_SLAB, WoodenSlab::TYPE_OAK));
		$this->mapStairs(Ids::POLISHED_ANDESITE_STAIRS, fn () => BlockFactory::get(BlockIds::POLISHED_ANDESITE_STAIRS));
		$this->mapStairs(Ids::POLISHED_BLACKSTONE_STAIRS, fn () => BlockFactory::get(BlockIds::POLISHED_BLACKSTONE_STAIRS));
		$this->mapStairs(Ids::POLISHED_BLACKSTONE_BRICK_STAIRS, fn () => BlockFactory::get(BlockIds::POLISHED_BLACKSTONE_BRICK_STAIRS));
		$this->mapStairs(Ids::POLISHED_DIORITE_STAIRS, fn () => BlockFactory::get(BlockIds::POLISHED_DIORITE_STAIRS));
		$this->mapStairs(Ids::POLISHED_GRANITE_STAIRS, fn () => BlockFactory::get(BlockIds::POLISHED_GRANITE_STAIRS));
		$this->map(Ids::PORTAL, function (Reader $in) : Block {
			$axis = match($value = $in->readString(StateNames::PORTAL_AXIS)) {
				StringValues::PORTAL_AXIS_UNKNOWN, StringValues::PORTAL_AXIS_X => Axis::X,
				StringValues::PORTAL_AXIS_Z => Axis::Z,
				default => throw $in->badValueException(StateNames::PORTAL_AXIS, $value),
			};
			return BlockFactory::get(BlockIds::PORTAL, $axis === Axis::Z ? 2 : 1);
		});
		$this->map(Ids::POTATOES, fn (Reader $in) => Helper::decodeCrops(BlockFactory::get(BlockIds::POTATOES), $in));
		$this->mapSimple(Ids::POWERED_COMPARATOR, fn () => BlockFactory::get(BlockIds::POWERED_COMPARATOR));
		$this->mapSimple(Ids::POWERED_REPEATER, fn () => BlockFactory::get(BlockIds::POWERED_REPEATER));
		$this->mapSingleSlab(Ids::PRISMARINE_BRICK_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB2, StoneSlab2::TYPE_PRISMARINE_BRICKS));
		$this->mapDoubleSlab(Ids::PRISMARINE_BRICK_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_STONE_SLAB2, StoneSlab2::TYPE_PRISMARINE_BRICKS));
		$this->mapStairs(Ids::PRISMARINE_BRICKS_STAIRS, fn () => BlockFactory::get(BlockIds::PRISMARINE_BRICKS_STAIRS));
		$this->mapSingleSlab(Ids::PRISMARINE_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB2, StoneSlab2::TYPE_PRISMARINE));
		$this->mapDoubleSlab(Ids::PRISMARINE_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_STONE_SLAB2, StoneSlab2::TYPE_PRISMARINE));
		$this->mapStairs(Ids::PRISMARINE_STAIRS, fn () => BlockFactory::get(BlockIds::PRISMARINE_STAIRS));
		$this->map(Ids::PUMPKIN, function (Reader $in) : Block {
			$in->ignored(StateNames::MC_CARDINAL_DIRECTION); //obsolete
			return BlockFactory::get(BlockIds::PUMPKIN);
		});
		$this->map(Ids::PUMPKIN_STEM, fn (Reader $in) => Helper::decodeStem(BlockFactory::get(BlockIds::PUMPKIN_STEM), $in));
		$this->map(Ids::PURPUR_BLOCK, function (Reader $in) : Block {
			$in->ignored(StateNames::PILLAR_AXIS); //???
			return BlockFactory::get(BlockIds::PURPUR_BLOCK);
		});
		$this->map(Ids::PURPUR_PILLAR, fn (Reader $in) => BlockFactory::get(BlockIds::PURPUR_BLOCK, Quartz::PILLAR | $in->readPillarAxis()));
		$this->mapSingleSlab(Ids::PURPUR_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB2, StoneSlab2::TYPE_PURPUR));
		$this->mapDoubleSlab(Ids::PURPUR_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_STONE_SLAB2, StoneSlab2::TYPE_PURPUR));
		$this->mapStairs(Ids::PURPUR_STAIRS, fn () => BlockFactory::get(BlockIds::PURPUR_STAIRS));
		$this->map(Ids::QUARTZ_BLOCK, function (Reader $in) : Block {
			$in->ignored(StateNames::PILLAR_AXIS);
			return BlockFactory::get(BlockIds::QUARTZ_BLOCK);
		});
		$this->map(Ids::QUARTZ_PILLAR, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::QUARTZ_BLOCK, Quartz::PILLAR | $in->readPillarAxis());
		});
		$this->mapSingleSlab(Ids::QUARTZ_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB4, StoneSlab4::TYPE_SMOOTH_QUARTZ));
		$this->mapDoubleSlab(Ids::QUARTZ_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_STONE_SLAB4, StoneSlab4::TYPE_SMOOTH_QUARTZ));
		$this->mapStairs(Ids::QUARTZ_STAIRS, fn () => BlockFactory::get(BlockIds::QUARTZ_STAIRS));
		$this->map(Ids::RAIL, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::RAIL, $in->readBoundedInt(StateNames::RAIL_DIRECTION, 0, 5));
		});
		$this->map(Ids::RED_MUSHROOM_BLOCK, fn (Reader $in) => Helper::decodeMushroomBlock(BlockFactory::get(BlockIds::QUARTZ_STAIRS), $in));
		$this->mapSingleSlab(Ids::RED_NETHER_BRICK_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB2, StoneSlab2::TYPE_RED_NETHER_BRICK));
		$this->mapDoubleSlab(Ids::RED_NETHER_BRICK_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_STONE_SLAB2, StoneSlab2::TYPE_RED_NETHER_BRICK));
		$this->mapStairs(Ids::RED_NETHER_BRICK_STAIRS, fn () => BlockFactory::get(BlockIds::RED_NETHER_BRICK_STAIRS));
		$this->mapSingleSlab(Ids::RED_SANDSTONE_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB2, StoneSlab2::TYPE_RED_SANDSTONE));
		$this->mapDoubleSlab(Ids::RED_SANDSTONE_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_STONE_SLAB2, StoneSlab2::TYPE_RED_SANDSTONE));
		$this->mapStairs(Ids::RED_SANDSTONE_STAIRS, fn () => BlockFactory::get(BlockIds::RED_SANDSTONE_STAIRS));
		$this->map(Ids::REDSTONE_LAMP, function () : Block {
			return BlockFactory::get(BlockIds::REDSTONE_LAMP);
		});
		$this->map(Ids::REDSTONE_ORE, function () : Block {
			return BlockFactory::get(BlockIds::REDSTONE_ORE);
		});
		$this->map(Ids::REDSTONE_TORCH, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::LIT_REDSTONE_TORCH, $in->readTorchFacing());
		});
		$this->map(Ids::REDSTONE_WIRE, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::REDSTONE_WIRE, $in->readBoundedInt(StateNames::REDSTONE_SIGNAL, 0, 15));
		});
		$this->map(Ids::REEDS, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::REEDS_BLOCK, $in->readBoundedInt(StateNames::AGE, 0, 15));
		});
		$this->mapSingleSlab(Ids::SANDSTONE_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB, StoneSlab::SANDSTONE));
		$this->mapDoubleSlab(Ids::SANDSTONE_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_STONE_SLAB, StoneSlab::SANDSTONE));
		$this->mapStairs(Ids::SANDSTONE_STAIRS, fn () => BlockFactory::get(BlockIds::SANDSTONE_STAIRS));
		$this->mapSingleSlab(Ids::SMOOTH_QUARTZ_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB4, StoneSlab4::TYPE_SMOOTH_QUARTZ));
		$this->mapDoubleSlab(Ids::SMOOTH_QUARTZ_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_STONE_SLAB4, StoneSlab4::TYPE_SMOOTH_QUARTZ));
		$this->mapStairs(Ids::SMOOTH_QUARTZ_STAIRS, fn () => BlockFactory::get(BlockIds::SMOOTH_QUARTZ_STAIRS));
		$this->mapSingleSlab(Ids::SMOOTH_RED_SANDSTONE_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB3, StoneSlab3::TYPE_SMOOTH_RED_SANDSTONE));
		$this->mapDoubleSlab(Ids::SMOOTH_RED_SANDSTONE_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_STONE_SLAB3, StoneSlab3::TYPE_SMOOTH_RED_SANDSTONE));
		$this->mapStairs(Ids::SMOOTH_RED_SANDSTONE_STAIRS, fn () => BlockFactory::get(BlockIds::SMOOTH_RED_SANDSTONE_STAIRS));
		$this->mapSingleSlab(Ids::SMOOTH_SANDSTONE_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB2, StoneSlab2::TYPE_SMOOTH_SANDSTONE));
		$this->mapDoubleSlab(Ids::SMOOTH_SANDSTONE_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_STONE_SLAB2, StoneSlab2::TYPE_SMOOTH_SANDSTONE));
		$this->mapStairs(Ids::SMOOTH_SANDSTONE_STAIRS, fn () => BlockFactory::get(BlockIds::SMOOTH_SANDSTONE_STAIRS));
		$this->mapSingleSlab(Ids::SMOOTH_STONE_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB));
		$this->mapDoubleSlab(Ids::SMOOTH_STONE_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_STONE_SLAB));
		$this->map(Ids::SNOW_LAYER, function (Reader $in) : Block {
			$in->ignored(StateNames::COVERED_BIT); //seems to be useless
			return BlockFactory::get(BlockIds::SNOW_LAYER, $in->readBoundedInt(StateNames::HEIGHT, 0, 7) + 1);
		});
		$this->map(Ids::SOUL_LANTERN, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::SOUL_LANTERN, $in->readBool(StateNames::HANGING) ? 0x01 : 0);
		});
		$this->map(Ids::STANDING_BANNER, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::STANDING_BANNER, $in->readBoundedInt(StateNames::GROUND_SIGN_DIRECTION, 0, 15));
		});
		$this->mapSingleSlab(Ids::STONE_BRICK_SLAB, fn () => BlockFactory::get(BlockIds::STONE_SLAB, StoneSlab::STONE_BRICK));
		$this->mapDoubleSlab(Ids::STONE_BRICK_DOUBLE_SLAB, fn () => BlockFactory::get(BlockIds::DOUBLE_STONE_SLAB, StoneSlab::STONE_BRICK));
		$this->mapStairs(Ids::STONE_BRICK_STAIRS, fn () => BlockFactory::get(BlockIds::STONE_BRICK_STAIRS));
		$this->map(Ids::STONE_BUTTON, fn (Reader $in) => Helper::decodeButton(BlockFactory::get(BlockIds::STONE_BUTTON), $in));
		$this->map(Ids::STONE_PRESSURE_PLATE, fn (Reader $in) => BlockFactory::get(BlockIds::STONE_PRESSURE_PLATE));
		$this->mapStairs(Ids::STONE_STAIRS, fn () => BlockFactory::get(BlockIds::STONE_STAIRS));
		$this->map(Ids::STONECUTTER_BLOCK, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::STONECUTTER_BLOCK, $in->readCardinalHorizontalFacing());
		});
		$this->map(Ids::SWEET_BERRY_BUSH, function (Reader $in) : Block {
			//berry bush only wants 0-3, but it can be bigger in MCPE due to misuse of GROWTH state which goes up to 7
			$growth = $in->readBoundedInt(StateNames::GROWTH, 0, 7);
			return BlockFactory::get(BlockIds::SWEET_BERRY_BUSH, min($growth, 7));
		});
		$this->map(Ids::TNT, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::TNT, $in->readBool(StateNames::EXPLODE_BIT) ? 0x01 : 0);
		});
		$this->map(Ids::TORCH, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::TORCH, $in->readTorchFacing());
		});
		$this->map(Ids::TRAPPED_CHEST, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::TRAPPED_CHEST, $in->readCardinalHorizontalFacing());
		});
		$this->map(Ids::TRIP_WIRE, function (Reader $in) : Block {
			return BlockFactory::get(
				BlockIds::TRIPWIRE_HOOK,
				($in->readBool(StateNames::POWERED_BIT) ? 0x01 : 0) |
				($in->readBool(StateNames::SUSPENDED_BIT) ? 0x02 : 0) |
				($in->readBool(StateNames::ATTACHED_BIT) ? 0x04 : 0) |
				($in->readBool(StateNames::DISARMED_BIT) ? 0x08 : 0)
			);
		});
		$this->map(Ids::TRIPWIRE_HOOK, function (Reader $in) : Block {
			return BlockFactory::get(
				BlockIds::TRIPWIRE_HOOK,
				$in->readLegacyHorizontalFacing() |
				($in->readBool(StateNames::ATTACHED_BIT) ? 0x04 : 0) |
				($in->readBool(StateNames::POWERED_BIT) ? 0x08 : 0)
			);
		});
		$this->map(Ids::UNLIT_REDSTONE_TORCH, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::UNLIT_REDSTONE_TORCH, $in->readTorchFacing());
		});
		$this->mapSimple(Ids::UNPOWERED_COMPARATOR, fn () => BlockFactory::get(BlockIds::UNPOWERED_COMPARATOR));
		$this->mapSimple(Ids::UNPOWERED_REPEATER, fn () => BlockFactory::get(BlockIds::UNPOWERED_REPEATER));
		$this->map(Ids::VINE, function (Reader $in) : Block {
			$vineDirectionFlags = $in->readBoundedInt(StateNames::VINE_DIRECTION_BITS, 0, 15);
			return BlockFactory::get(
				BlockIds::VINE,
				(($vineDirectionFlags & 0x04) !== 0 ? 0x04 : 0) |
				(($vineDirectionFlags & 0x01) !== 0 ? 0x01 : 0) |
				(($vineDirectionFlags & 0x02) !== 0 ? 0x02 : 0) |
				(($vineDirectionFlags & 0x08) !== 0 ? 0x08 : 0)
			);
		});

		$this->map(Ids::WALL_BANNER, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::WALL_BANNER, $in->readHorizontalFacing());
		});
		$this->map(Ids::WATER, fn (Reader $in) => Helper::decodeLiquid(BlockFactory::get(BlockIds::STILL_WATER), $in));
		$this->map(Ids::WEEPING_VINES, function (Reader $in) : Block {
			return BlockFactory::get(BlockIds::WEEPING_VINES, $in->readBoundedInt(StateNames::WEEPING_VINES_AGE, 0, 25));
		});
		$this->map(Ids::WHEAT, fn (Reader $in) => Helper::decodeCrops(BlockFactory::get(BlockIds::WHEAT_BLOCK), $in));
	}

	/** @throws BlockStateDeserializeException */
	public function deserializeBlock(BlockStateData $blockStateData) : Block
	{
		$id = $blockStateData->getName();
		if (!array_key_exists($id, $this->deserializeFuncs)) {
			return BlockFactory::get(BlockIds::AIR);
		}

		$reader = new Reader($blockStateData);
		$block = $this->deserializeFuncs[$id]($reader);
		//$reader->checkUnreadProperties();
		return $block;
	}
}
