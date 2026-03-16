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

use pocketmine\block\Block;
use pocketmine\block\Lava;
use pocketmine\block\Liquid;
use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\math\Vector3;
use pocketmine\Player;

class LiquidBucket extends Item
{
	private Liquid $liquid;

	public function __construct(int $id, int $meta, string $name, Liquid $liquid)
	{
		parent::__construct($id, $meta, $name);
		$this->liquid = $liquid;
	}

	public function getMaxStackSize() : int
	{
		return 1;
	}

	public function getFuelTime() : int
	{
		if ($this->liquid instanceof Lava) {
			return 20000;
		}

		return 0;
	}

	public function getFuelResidue() : Item
	{
		return Item::get(Item::BUCKET);
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool
	{
		//TODO: move this to generic placement logic
		$resultBlock = clone $this->liquid;

		if ($blockReplace->canBeReplaced()) {
			$ev = new PlayerBucketEmptyEvent($player, $blockReplace, $face, $this, ItemFactory::get(Item::BUCKET));
			$ev->call();
			if (!$ev->isCancelled()) {
				$player->getLevel()->setBlock($blockReplace, $resultBlock->getFlowingForm(), true, true);
				$player->getLevel()->broadcastLevelSoundEvent($blockReplace->add(0.5, 0.5, 0.5), $resultBlock->getBucketEmptySound());

				if ($player->hasFiniteResources()) {
					$player->getInventory()->setItemInHand($ev->getItem());
				}
				return true;
			} else {
				$player->getInventory()->sendContents($player);
			}
		}

		return false;
	}

	public function getLiquid() : Liquid
	{
		return $this->liquid;
	}
}
