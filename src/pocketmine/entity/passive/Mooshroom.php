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

namespace pocketmine\entity\passive;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\item\Bowl;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\Shears;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Mooshroom extends Cow
{
	public const NETWORK_ID = self::MOOSHROOM;

	protected $spawnableBlock = Block::MYCELIUM;

	public function getName() : string
	{
		return "Mooshroom";
	}

	public function onInteract(Player $player, Vector3 $clickPos) : bool
	{
		if (!$this->isImmobile()) {
			$item = $player->getInventory()->getItemInHand();
			if ($item instanceof Bowl && !$this->isBaby()) {
				$new = ItemFactory::get(Item::MUSHROOM_STEW);
				if ($player->isSurvival()) {
					$item->pop();
				}

				if ($player->getInventory()->canAddItem($new)) {
					$player->getInventory()->addItem($new);
				} else {
					$player->dropItem($new);
				}

				return true;
			} elseif ($item instanceof Shears && !$this->isBaby()) {
				$cow = new Cow($this->level, Entity::createBaseNBT($this));
				$cow->setRotation($this->yaw, $this->pitch);
				$cow->setHealth($this->getHealth());
				$cow->setNameTag($this->getNameTag());
				$cow->setImmobile(!$this->server->mobAiEnabled);

				$item->applyDamage(1);

				for ($i = 0; $i < 5; $i++) {
					$player->dropItem(ItemFactory::get(Block::RED_MUSHROOM));
				}

				$this->flagForDespawn();
				$cow->spawnToAll();

				return true;
			}
		}
		return parent::onInteract($player, $clickPos);
	}
}
