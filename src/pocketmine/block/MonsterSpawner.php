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

use pocketmine\item\Item;
use pocketmine\item\SpawnEgg;
use pocketmine\item\TieredTool;
use pocketmine\Player;
use pocketmine\tile\MobSpawner;
use pocketmine\tile\Tile;

use function mt_rand;

class MonsterSpawner extends Transparent
{
	protected $id = self::MONSTER_SPAWNER;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getHardness() : float
	{
		return 5;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int
	{
		return TieredTool::TIER_WOODEN;
	}

	public function getName() : string
	{
		return "Monster Spawner";
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [];
	}

	public function isAffectedBySilkTouch() : bool
	{
		return false;
	}

	protected function getXpDropAmount() : int
	{
		return mt_rand(15, 43);
	}

	public function onActivate(Item $item, Player $player = null) : bool
	{
		if ($item instanceof SpawnEgg) {
			/** @var MobSpawner $tile */
			$tile = Tile::createTile(Tile::MOB_SPAWNER, $this->level, MobSpawner::createNBT($this));
			$tile->setEntityId($item->getDamage());

			if ($player instanceof Player) {
				$item->pop();
				$player->getInventory()->setItemInHand($item);
			}
		}
		return true;
	}

	public function onScheduledUpdate() : void
	{
		$level = $this->getLevel();
		$furnace = $level->getTile($this);
		if ($furnace instanceof MobSpawner && $furnace->onUpdate()) {
			$level->scheduleDelayedBlockUpdate($this, 1); //TODO: check this
		}
	}
}
