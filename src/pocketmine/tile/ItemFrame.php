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

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\format\Chunk;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;

class ItemFrame extends Spawnable
{
	public const TAG_ITEM_ROTATION = "ItemRotation";
	public const TAG_ITEM_DROP_CHANCE = "ItemDropChance";
	public const TAG_ITEM = "Item";

	private Item $item;
	private int $itemRotation;
	private float $itemDropChance;

	private static ?NetworkLittleEndianNBTStream $nbtWriter = null;

	/** @var int[] */
	private array $protocolSpawnCompoundCache = [];

	protected function readSaveData(CompoundTag $nbt) : void
	{
		if (($itemTag = $nbt->getCompoundTag(self::TAG_ITEM)) !== null) {
			$this->item = Item::nbtDeserialize($itemTag);
		} else {
			$this->item = ItemFactory::get(Item::AIR, 0, 0);
		}
		$this->item->setOnItemFrame(true);

		$this->itemRotation = $nbt->getByte(self::TAG_ITEM_ROTATION, 0, true);
		$this->itemDropChance = $nbt->getFloat(self::TAG_ITEM_DROP_CHANCE, 1.0, true);
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$nbt->setFloat(self::TAG_ITEM_DROP_CHANCE, $this->itemDropChance);
		$nbt->setByte(self::TAG_ITEM_ROTATION, $this->itemRotation);
		$nbt->setTag($this->item->nbtSerialize(-1, self::TAG_ITEM));
	}

	public function hasItem() : bool
	{
		return !$this->item->isNull();
	}

	public function getItem() : Item
	{
		return clone $this->item;
	}

	public function setItem(Item $item = null) : void
	{
		if ($item !== null && !$item->isNull()) {
			$this->item = clone $item;
		} else {
			$this->item = ItemFactory::get(Item::AIR, 0, 0);
		}
		$this->item->setOnItemFrame(true);

		$this->onChanged();
	}

	public function getItemRotation() : int
	{
		return $this->itemRotation;
	}

	public function setItemRotation(int $rotation) : void
	{
		$this->itemRotation = $rotation;
		$this->onChanged();
	}

	public function getItemDropChance() : float
	{
		return $this->itemDropChance;
	}

	public function setItemDropChance(float $chance) : void
	{
		$this->itemDropChance = $chance;
		$this->onChanged();
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void
	{
		$nbt->setFloat(self::TAG_ITEM_DROP_CHANCE, $this->itemDropChance);
		$nbt->setByte(self::TAG_ITEM_ROTATION, $this->itemRotation);
	}

	public function spawnTo(Player $player) : bool
	{
		if ($this->closed) {
			return false;
		}

		$player->sendDataPacket(BlockActorDataPacket::create($this->x, $this->y, $this->z, $this->getProtocolSerializedSpawnCompound($player->getProtocolVersion())));
		return true;
	}

	public function spawnToAll() : void
	{
		if ($this->closed) {
			return;
		}

		$viewers = $this->level->getViewersForPosition($this);
		foreach ($viewers as $viewer) {
			$this->spawnTo($viewer);
		}
	}

	/**
	 * Performs actions needed when the tile is modified, such as clearing caches and respawning the tile to players.
	 * WARNING: This MUST be called to clear spawn-compound and chunk caches when the tile's spawn compound has changed!
	 */
	protected function onChanged() : void
	{
		$this->protocolSpawnCompoundCache = [];
		$this->spawnToAll();

		$this->level->clearChunkCache($this->getFloorX() >> Chunk::COORD_BIT_SIZE, $this->getFloorZ() >> Chunk::COORD_BIT_SIZE);
	}

	public function getProtocolSerializedSpawnCompound(int $playerProtocol) : string
	{
		$compound = $this->getSpawnCompound();
		if (!($this->item->getNamedTagEntry("map_uuid") instanceof LongTag) || $playerProtocol >= ProtocolInfo::PROTOCOL_137) {
			$compound->setTag($this->item->nbtSerialize(-1, self::TAG_ITEM, $playerProtocol));
		} else {
			$item = clone $this->item;
			$mapId = $item->getNamedTagEntry("map_uuid")->getValue();
			$item->removeNamedTagEntry("map_uuid");
			$item->setNamedTagEntry(new StringTag("map_uuid", (string) $mapId));

			$compound->setTag($item->nbtSerialize(-1, self::TAG_ITEM));
		}

		if (!isset($this->protocolSpawnCompoundCache[$playerProtocol])) {
			if (self::$nbtWriter === null) {
				self::$nbtWriter = new NetworkLittleEndianNBTStream();
			}

			$this->protocolSpawnCompoundCache[$playerProtocol] = self::$nbtWriter->write($compound);
		}

		return $this->protocolSpawnCompoundCache[$playerProtocol];
	}
}
