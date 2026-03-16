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

use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\Player;

abstract class Spawnable extends Tile
{
	private ?string $spawnCompoundCache = null;
	/** @var NetworkLittleEndianNBTStream|null */
	private static $nbtWriter = null;

	public function __construct(Level $level, CompoundTag $nbt)
	{
		parent::__construct($level, $nbt);
		$this->spawnToAll();
	}

	public function createSpawnPacket() : BlockActorDataPacket{
		return BlockActorDataPacket::create($this->x, $this->y, $this->z, $this->getSerializedSpawnCompound());
	}

	public function spawnTo(Player $player) : bool
	{
		if ($this->closed) {
			return false;
		}

		$player->dataPacket($this->createSpawnPacket());

		return true;
	}

	public function spawnToAll() : void
	{
		if ($this->closed) {
			return;
		}

		$this->level->broadcastPacketToViewers($this, $this->createSpawnPacket());
	}

	/**
	 * Performs actions needed when the tile is modified, such as clearing caches and respawning the tile to players.
	 * WARNING: This MUST be called to clear spawn-compound and chunk caches when the tile's spawn compound has changed!
	 */
	protected function onChanged() : void
	{
		$this->spawnCompoundCache = null;
		$this->spawnToAll();

		$this->level->clearChunkCache($this->getFloorX() >> Chunk::COORD_BIT_SIZE, $this->getFloorZ() >> Chunk::COORD_BIT_SIZE);
	}

	/**
	 * Returns encoded NBT (varint, little-endian) used to spawn this tile to clients. Uses cache where possible,
	 * populates cache if it is null.
	 *
	 * @phpstan-return string
	 */
	final public function getSerializedSpawnCompound() : string
	{
		if ($this->spawnCompoundCache === null) {
			if (self::$nbtWriter === null) {
				self::$nbtWriter = new NetworkLittleEndianNBTStream();
			}

			$this->spawnCompoundCache = self::$nbtWriter->write($this->getSpawnCompound());
		}

		return $this->spawnCompoundCache;
	}

	final public function getSpawnCompound() : CompoundTag
	{
		$nbt = new CompoundTag("", [
			new StringTag(self::TAG_ID, static::getSaveId()),
			new IntTag(self::TAG_X, $this->x),
			new IntTag(self::TAG_Y, $this->y),
			new IntTag(self::TAG_Z, $this->z)
		]);
		$this->addAdditionalSpawnData($nbt);
		return $nbt;
	}

	public function getProtocolSerializedSpawnCompound(int $playerProtocol) : string
	{
		return $this->getSerializedSpawnCompound();
	}

	/**
	 * An extension to getSpawnCompound() for
	 * further modifying the generic tile NBT.
	 */
	abstract protected function addAdditionalSpawnData(CompoundTag $nbt) : void;

	/**
	 * Called when a player updates a block entity's NBT data
	 * for example when writing on a sign.
	 *
	 * @return bool indication of success, will respawn the tile to the player if false.
	 */
	public function updateCompoundTag(CompoundTag $nbt, Player $player) : bool
	{
		return false;
	}
}
