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

namespace pocketmine\inventory\transaction\action;

use pocketmine\event\player\PlayerBlockPickEvent;
use pocketmine\inventory\FakeInventory;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\cache\CreativeInventoryCache;
use pocketmine\Player;

use function abs;
use function implode;

/**
 * Represents an action causing a change in an inventory slot.
 */
class ContainerSlotChangeAction extends SlotChangeAction
{
	protected int $fails = 0;

	/**
	 * Sets the item into the target inventory.
	 */
	public function execute(Player $source) : void
	{
		//because we alternate actions every time, the slot in the inventory changes, and everything goes wrong
		$sourceItem = $this->getInventory()->getItem($this->inventorySlot);

		$out = null;
		$in = null;

		if ($sourceItem->equalsExact($this->targetItem)) {
			//This should never happen, somehow a change happened where nothing changed
		} elseif ($sourceItem->equals($this->targetItem, true, true)) {
			$item = clone $sourceItem;
			$countDiff = $this->targetItem->getCount() - $sourceItem->getCount();
			$item->setCount(abs($countDiff));

			if ($countDiff < 0) { //Count decreased
				$out = $item;
			} elseif ($countDiff > 0) { //Count increased
				$in = $item;
			} else {
				//Should be impossible (identical items and no count change)
				//This should be caught by the first condition even if it was possible
			}
		} elseif ($sourceItem->getId() !== ItemIds::AIR && $this->targetItem->getId() === ItemIds::AIR) {
			//Slot emptied (item removed)
			$out = clone $sourceItem;
		} elseif ($sourceItem->getId() === ItemIds::AIR && $this->targetItem->getId() !== ItemIds::AIR) {
			//Slot filled (item added)
			$in = clone $this->targetItem;
		} else {
			//Some other slot change - an item swap (tool damage changes will be ignored as they are processed server-side before any change is sent by the client

			$out = clone $sourceItem;
			$in = clone $this->targetItem;
		}

		if ($out !== null) {
			if (!$this->inventory->getItem($this->getSlot())->equals($out, $out->hasAnyDamageValue(), !$out->hasNamedTag())) {
				$source->getServer()->getLogger()->debug("Player inventory not contains " . $out . " in slot " . $this->getSlot() . ". Have " . $this->getInventory()->getItem($this->getSlot()));
				if (++$this->fails >= 5) {
					return;
				}

				$source->addInventoryTransactionActions($this);
				return;
			}
		}

		if ($in !== null) {
			$validIsInItem = function () use ($source, $in) : bool {
				if ($source->getCraftingGrid()->contains($in)) {
					return true;
				} elseif ($source->isCreative(true)) {
					if (CreativeInventoryCache::getInstance()->getItemIndex($in) !== -1) {
						return true;
					}

					$targetBlock = $source->getTargetBlock(6);
					if ($targetBlock !== null && $targetBlock->getId() === $in->getId() && $targetBlock->getDamage() === $in->getDamage()) {
						$ev = new PlayerBlockPickEvent($source, $targetBlock, $in);
						$ev->call();
						if (!$ev->isCancelled()) {
							return true;
						}
					}
				}

				return false;
			};

			if (!$validIsInItem()) {
				$source->getServer()->getLogger()->debug("Transaction inventory not contains " . $in . ". Transaction inventory contents: " . implode("; ", $source->getCraftingGrid()->getContents()));

				if (++$this->fails >= 5) {
					return;
				}

				$source->addInventoryTransactionActions($this);
				return;
			}
		}

		if ($out !== null) {
			$source->getCraftingGrid()->addItem($out);
		}

		if ($in !== null) {
			$source->getCraftingGrid()->removeItem($in);
		}

		$this->inventory->setItem($this->inventorySlot, $this->targetItem, false);
	}

	public function revert(Player $source) : void
	{
		if ($this->inventory instanceof FakeInventory) {
			return;
		}

		$this->inventory->sendSlot($this->inventorySlot, $source);
	}
}
