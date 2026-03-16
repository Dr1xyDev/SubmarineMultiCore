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

use pocketmine\event\block\BlockPreExplodeEvent;
use pocketmine\event\player\PlayerRespawnAnchorUseEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\item\TieredTool;
use pocketmine\lang\TranslationContainer;
use pocketmine\level\Explosion;
use pocketmine\level\GameRules;
use pocketmine\level\Position;
use pocketmine\level\sound\RespawnAnchorChargeSound;
use pocketmine\level\sound\RespawnAnchorSetSpawnSound;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class RespawnAnchor extends Solid
{
	private const int MAX_CHARGES = 4;

	protected $id = self::RESPAWN_ANCHOR;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getBlastResistance() : float
	{
		return 1200;
	}

	public function getHardness() : float
	{
		return 50;
	}

	public function getName() : string
	{
		return "Respawn Anchor";
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int
	{
		return TieredTool::TIER_DIAMOND;
	}

	public function getLightLevel() : int
	{
		return match ($this->meta) {
			0 => 0,
			1 => 3,
			2 => 7,
			default => 15
		};
	}

	public function onActivate(Item $item, Player $player = null) : bool
	{
		if ($item->getId() === ItemIds::GLOWSTONE && $this->meta < self::MAX_CHARGES) {
			$this->meta++;
			$this->level->setBlock($this, $this);
			$this->level->addSound(new RespawnAnchorChargeSound($this));
			return true;
		}

		if ($this->meta > 0) {
			if ($player === null) {
				return false;
			}

			$action = PlayerRespawnAnchorUseEvent::ACTION_EXPLODE;
			if ($this->level->getDimension() === DimensionIds::NETHER) {
				$action = PlayerRespawnAnchorUseEvent::ACTION_SET_SPAWN;
			}

			$ev = new PlayerRespawnAnchorUseEvent($player, $this, $action);
			$ev->call();
			if ($ev->isCancelled()) {
				return false;
			}

			switch ($ev->getAction()) {
				case PlayerRespawnAnchorUseEvent::ACTION_EXPLODE:
					if ($this->level->getGameRules()->getBool(GameRules::RULE_RESPAWN_BLOCKS_EXPLODE)) {
						$this->explode($player);
					}
					return true;

				case PlayerRespawnAnchorUseEvent::ACTION_SET_SPAWN:
					if ($player->getSpawn() !== null && $player->getSpawn()->equals($this)) {
						return true;
					}

					$player->setSpawn($this);
					$this->level->addSound(new RespawnAnchorSetSpawnSound($this));
					$player->sendMessage(new TranslationContainer(TextFormat::GRAY . "%tile.respawn_anchor.respawnSet"));
					return true;
			}
		}
		return false;
	}

	private function explode(?Player $player) : void
	{
		$ev = new BlockPreExplodeEvent($this, 5, $player);
		$ev->setIncendiary(true);

		$ev->call();
		if ($ev->isCancelled()) {
			return;
		}

		$this->level->setBlock($this, BlockFactory::get(BlockIds::AIR));

		$explosion = new Explosion(Position::fromObject($this->add(0.5, 0.5, 0.5), $this->level), $ev->getRadius(), $this);
		$explosion->setFireChance($ev->getFireChance());

		if ($ev->isBlockBreaking()) {
			$explosion->explodeA();
		}
		$explosion->explodeB();
	}

	public function getBlockProtocol(int $playerProtocol) : ?Block
	{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_419) {
			return BlockFactory::get(BlockIds::OBSIDIAN);
		}

		return null;
	}
}
