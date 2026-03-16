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

namespace pocketmine\tile;

use InvalidArgumentException;
use pocketmine\block\Block;
use pocketmine\inventory\InventoryHolder;
use pocketmine\inventory\ShulkerBoxInventory;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

class ShulkerBox extends Spawnable implements InventoryHolder, Container, Nameable
{
	use NameableTrait {
		addAdditionalSpawnData as addNameSpawnData;
	}
	use ContainerTrait;

	public const TAG_FACING = "facing";
	public const TAG_UNDYED = "isUndyed";

	/** @var int */
	protected $facing = Facing::UP;
	/** @var bool */
	protected $isUndyed = true;

	/** @var ShulkerBoxInventory */
	protected $inventory;

	public function setFacing(int $facing) : void
	{
		if ($facing < 0 || $facing > 5) {
			throw new InvalidArgumentException("Invalid shulkerbox facing: $facing");
		}

		$this->facing = $facing;
		$this->onChanged();
	}

	public function getFacing() : int
	{
		return $this->facing;
	}

	/**
	 * @return int
	 */
	public function getSize()
	{
		return 27;
	}

	public function getDefaultName() : string
	{
		return "Shulker Box";
	}

	/**
	 * @return ShulkerBoxInventory
	 */
	public function getInventory()
	{
		return $this->inventory;
	}

	/**
	 * @return ShulkerBoxInventory
	 */
	public function getRealInventory()
	{
		return $this->inventory;
	}

	protected function readSaveData(CompoundTag $nbt) : void
	{
		$this->facing = $nbt->getByte(self::TAG_FACING, Facing::DOWN);
		$this->isUndyed = $nbt->getByte(self::TAG_UNDYED, 1) == 1;

		$this->inventory = new ShulkerBoxInventory($this);

		$this->loadName($nbt);
		$this->loadItems($nbt);
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$nbt->setTag(new ByteTag(self::TAG_FACING, $this->facing));
		$nbt->setTag(new ByteTag(self::TAG_UNDYED, $this->isUndyed ? 1 : 0));

		$this->saveName($nbt);
		$this->saveItems($nbt);
	}

	public function writeBlockData(CompoundTag $nbt)
	{
		$this->saveName($nbt);
		$this->saveItems($nbt);
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void
	{
		$nbt->setTag(new ByteTag(self::TAG_FACING, $this->facing));
		$nbt->setTag(new ByteTag(self::TAG_UNDYED, $this->isUndyed ? 1 : 0));

		$this->addNameSpawnData($nbt);
	}

	protected static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : void
	{
		parent::createAdditionalNBT($nbt, $pos, $face, $item, $player);

		$nbt->setByte(self::TAG_FACING, $face ?? Facing::DOWN);
		if ($item !== null) {
			$nbt->setByte(self::TAG_UNDYED, $item->getId() == Block::UNDYED_SHULKER_BOX ? 1 : 0);
		}
	}
}
