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

use pocketmine\block\Block;
use pocketmine\level\format\io\leveldb\states\BlockStateNames as StateNames;
use pocketmine\math\Facing;

final class BlockStateDeserializerHelper
{
	/** @throws BlockStateDeserializeException */
	public static function decodeButton(Block $button, BlockStateReader $in) : Block
	{
		$facing = $in->readFacingDirection();
		$isPressed = $in->readBool(StateNames::BUTTON_PRESSED_BIT); //TODO: ??
		return $button->setDamage($facing);
	}

	/**
	 * @throws BlockStateDeserializeException
	 */
	public static function decodeCrops(Block $crops, BlockStateReader $in) : Block
	{
		$growth = $in->readBoundedInt(StateNames::GROWTH, 0, 7);
		return $crops->setDamage($growth);
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeDoor(Block $door, BlockStateReader $in) : Block
	{
		//TODO: check if these need any special treatment to get the appropriate data to both halves of the door
		$isTop = $in->readBool(StateNames::UPPER_BLOCK_BIT);
		$facing = Facing::rotateY($in->readCardinalHorizontalFacing(), false);
		$hingeRight = $in->readBool(StateNames::DOOR_HINGE_BIT);
		$isOpen = $in->readBool(StateNames::OPEN_BIT);

		if ($isTop) {
			return $door->setDamage(0x08 |
				($hingeRight ? 0x01 : 0));
		}

		return $door->setDamage($facing | ($isOpen ? 0x04 : 0));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeDoublePlant(Block $doublePlant, BlockStateReader $in) : Block
	{
		$isTop = $in->readBool(StateNames::UPPER_BLOCK_BIT);
		return $doublePlant->setDamage($doublePlant->getDamage() | ($isTop ? 0x08 : 0));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeFenceGate(Block $fenceGate, BlockStateReader $in) : Block
	{
		$facing = $in->readCardinalHorizontalFacing();
		$inWall = $in->readBool(StateNames::IN_WALL_BIT);
		$isOpen = $in->readBool(StateNames::OPEN_BIT);

		return $fenceGate->setDamage($facing |
			($isOpen ? 0x04 : 0) |
			($inWall ? 0x08 : 0));
	}

	public static function decodeItemFrame(Block $itemFrame, BlockStateReader $in) : Block
	{
		$in->todo(StateNames::ITEM_FRAME_PHOTO_BIT); //TODO: not sure what the point of this is

		$facing = match($in->readFacingDirection()) {
			Facing::NORTH => 3,
			Facing::SOUTH => 2,
			Facing::WEST => 1,
			default => 0,
		};

		$hasMap = $in->readBool(StateNames::ITEM_FRAME_MAP_BIT); //TODO: ???

		return $itemFrame->setDamage($facing);
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeLeaves(Block $leaves, BlockStateReader $in) : Block
	{
		$noDecay = $in->readBool(StateNames::PERSISTENT_BIT);
		$checkDecay = $in->readBool(StateNames::UPDATE_BIT);

		return $leaves->setDamage($leaves->getDamage() | ($noDecay ? 0x04 : 0) | ($checkDecay ? 0x08 : 0));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeLiquid(Block $liquid, BlockStateReader $in) : Block
	{
		$fluidHeightState = $in->readBoundedInt(StateNames::LIQUID_DEPTH, 0, 15);
		$decay = $fluidHeightState & 0x7;
		$falling = $fluidHeightState & 0x8 !== 0;

		return $liquid->setDamage($decay | ($falling ? 0x08 : 0));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeMushroomBlock(Block $mushroomStew, BlockStateReader $in) : Block
	{
		switch ($type = $in->readBoundedInt(StateNames::HUGE_MUSHROOM_BITS, 0, 15)) {
			case 15:
			case 10: throw new BlockStateDeserializeException("This state does not exist");
			default:
				return $mushroomStew->setDamage($type);
		}
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeSapling(Block $sapling, BlockStateReader $in) : Block
	{
		$isReady = $in->readBool(StateNames::AGE_BIT);

		return $sapling->setDamage($isReady ? 0x01 : 0);
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeSingleSlab(Block $block, BlockStateReader $in) : Block
	{
		$isTop = $in->readSlabTop();

		return $block->setDamage($block->getDamage() | $isTop ? 0x08 : 0);
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeDoubleSlab(Block $doubleSlab, BlockStateReader $in) : Block
	{
		$in->ignored(StateNames::MC_VERTICAL_HALF);
		return $doubleSlab;
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeLog(Block $block, bool $stripped, BlockStateReader $in) : Block
	{
		return $block->setDamage($block->getDamage() | $in->readPillarAxis());
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeStairs(Block $stair, BlockStateReader $in) : Block
	{
		$facing = $in->readWeirdoHorizontalFacing();
		$isUpsideDown = $in->readBool(StateNames::UPSIDE_DOWN_BIT);

		return $stair->setDamage($facing | ($isUpsideDown ? 0x04 : 0));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeStem(Block $block, BlockStateReader $in) : Block
	{
		//In PM, we use Facing::UP to indicate that the stem is not attached to a pumpkin/melon, since this makes the
		//most intuitive sense (the stem is pointing at the sky). However, Bedrock uses the DOWN state for this, which
		//is absurd, and I refuse to make our API similarly absurd.
		$facing = $in->readFacingWithoutUp();
		$decodeCrops = self::decodeCrops($block, $in);
		return $block->setDamage($decodeCrops->getDamage() | ($facing === Facing::DOWN ? Facing::UP : $facing));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeTrapdoor(Block $trapdoor, BlockStateReader $in) : Block
	{
		$facing = $in->read5MinusHorizontalFacing();
		$isTop = $in->readBool(StateNames::UPSIDE_DOWN_BIT);
		$isOpen = $in->readBool(StateNames::OPEN_BIT);

		return $trapdoor->setDamage($facing | ($isTop ? 0x04 : 0) | ($isOpen ? 0x08 : 0));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeWallSign(Block $sign, BlockStateReader $in) : Block
	{
		$facing = $in->readHorizontalFacing();
		return $sign->setDamage($facing);
	}
}
