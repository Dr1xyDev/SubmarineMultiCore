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

namespace pocketmine\inventory;

use InvalidArgumentException;
use LogicException;
use pocketmine\entity\Human;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\network\mcpe\cache\CreativeInventoryCache;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\Player;
use RuntimeException;
use SplFixedArray;
use function array_search;
use function range;

class PlayerInventory extends BaseInventory
{
	/** @var Human */
	protected $holder;
	/** @var int */
	protected $itemInHandIndex = 0;
	/** @var SplFixedArray<int> */
	protected $hotbar;

	public function __construct(Human $player)
	{
		$this->holder = $player;
		$this->resetHotbar();
		parent::__construct();
	}

	public function getName() : string
	{
		return "Player";
	}

	public function getDefaultSize() : int
	{
		return 36;
	}

	/**
	 * Returns the index of the inventory slot mapped to the specified hotbar slot, or -1 if the hotbar slot does not exist.
	 */
	public function getHotbarSlotIndex(int $index) : int
	{
		return $this->hotbar[$index] ?? -1;
	}

	/**
	 * Links a hotbar slot to the specified slot in the main inventory. -1 links to no slot and will clear the hotbar slot.
	 * This method is intended for use in network interaction with clients only.
	 *
	 * NOTE: Do not change hotbar slot mapping with plugins, this will cause myriad client-sided bugs, especially with desktop GUI clients.
	 *
	 * @throws RuntimeException if the hotbar slot is out of range
	 * @throws InvalidArgumentException if the inventory slot is out of range
	 */
	public function setHotbarSlotIndex(int $hotbarSlot, int $inventorySlot)
	{
		if ($inventorySlot < -1 || $inventorySlot >= $this->getSize()) {
			throw new InvalidArgumentException("Inventory slot index \"$inventorySlot\" is out of range");
		}

		if ($inventorySlot !== -1 && ($alreadyEquippedIndex = array_search($inventorySlot, $this->getHotbar(), true)) !== false) {
			/* Swap the slots
			 * This assumes that the equipped slot can only be equipped in one other slot
			 * it will not account for ancient bugs where the same slot ended up linked to several hotbar slots.
			 * Such bugs will require a hotbar reset to default.
			 */
			$this->hotbar[$alreadyEquippedIndex] = $this->hotbar[$hotbarSlot];
		}

		$this->hotbar[$hotbarSlot] = $inventorySlot;
	}

	/**
	 * Returns the item in the slot linked to the specified hotbar slot, or Air if the slot is not linked to any hotbar slot.
	 */
	public function getHotbarSlotItem(int $hotbarSlotIndex) : Item
	{
		$inventorySlot = $this->getHotbarSlotIndex($hotbarSlotIndex);
		if ($inventorySlot !== -1) {
			return $this->getItem($inventorySlot);
		} else {
			return ItemFactory::air();
		}
	}

	public function getHotbar() : array
	{
		return $this->hotbar->toArray();
	}

	/**
	 * Resets hotbar links to their original defaults.
	 */
	public function resetHotbar() : void
	{
		$this->hotbar = SplFixedArray::fromArray(range(0, $this->getHotbarSize() - 1, 1));
	}

	public function isHotbarSlot(int $hotbarSlot) : bool
	{
		return $hotbarSlot >= 0 && $hotbarSlot <= $this->getHotbarSize();
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function throwIfNotHotbarSlot(int $slot)
	{
		if (!$this->isHotbarSlot($slot)) {
			throw new InvalidArgumentException("$slot is not a valid hotbar slot index (expected 0 - " . ($this->getHotbarSize() - 1) . ")");
		}
	}

	/**
	 * Returns the hotbar slot number the holder is currently holding.
	 */
	public function getHeldItemIndex() : int
	{
		return $this->itemInHandIndex;
	}

	/**
	 * Called when a client equips a hotbar slot. This method should not be used by plugins.
	 * This method will call PlayerItemHeldEvent.
	 *
	 * @param int $hotbarSlot Number of the hotbar slot to equip.
	 *
	 * @return bool if the equipment change was successful, false if not.
	 */
	public function equipItem(int $hotbarSlot, ?int $inventorySlot = null) : bool
	{
		$holder = $this->getHolder();
		if (!$this->isHotbarSlot($hotbarSlot)) {
			if ($holder instanceof Player) {
				$this->sendContents($holder);
			}
			return false;
		}

		if ($holder instanceof Player) {
			$ev = new PlayerItemHeldEvent($holder, $inventorySlot === null ? $this->getItem($hotbarSlot) : $this->getItem($inventorySlot), $hotbarSlot);
			$ev->call();

			if ($ev->isCancelled()) {
				$this->sendHeldItem($holder);
				return false;
			}
		}
		$this->setHeldItemIndex($hotbarSlot, false, $inventorySlot);

		return true;
	}

	/**
	 * Sets which hotbar slot the player is currently loading.
	 *
	 * @param int  $hotbarSlot 0-8 index of the hotbar slot to hold
	 * @param bool $send       Whether to send updates back to the inventory holder. This should usually be true for plugin calls.
	 *                         It should only be false to prevent feedback loops of equipment packets between client and server.
	 *
	 * @throws InvalidArgumentException if the hotbar slot is out of range
	 */
	public function setHeldItemIndex(int $hotbarSlot, bool $send = true, ?int $inventorySlot = null)
	{
		$this->throwIfNotHotbarSlot($hotbarSlot);
		$this->itemInHandIndex = $hotbarSlot;

		if ($inventorySlot !== null) {
			/* Handle a hotbar slot mapping change. This allows PE to select different inventory slots.
			 * This is the only time slot mapping should ever be changed. */
			$this->setHotbarSlotIndex($hotbarSlot, $inventorySlot);
		}

		if ($this->getHolder() instanceof Player && $send) {
			$this->sendHeldItem($this->getHolder());
		}

		$this->sendHeldItem($this->getHolder()->getViewers());
	}

	/**
	 * Returns the currently-held item.
	 */
	public function getItemInHand() : Item
	{
		return $this->getHotbarSlotItem($this->itemInHandIndex);
	}

	/**
	 * Sets the item in the currently-held slot to the specified item.
	 */
	public function setItemInHand(Item $item) : bool
	{
		return $this->setItem($this->getHeldItemSlot(), $item);
	}

	/**
	 * Returns the hotbar slot number currently held.
	 */
	public function getHeldItemSlot() : int
	{
		return $this->getHotbarSlotIndex($this->itemInHandIndex);
	}

	/**
	 * Sets the hotbar slot link of the currently-held hotbar slot.
	 * @deprecated Do not change hotbar slot mapping with plugins, this will cause myriad client-sided bugs, especially with desktop GUI clients.
	 */
	public function setHeldItemSlot(int $slot)
	{
		if ($slot >= -1 && $slot < $this->getSize()) {
			$this->setHotbarSlotIndex($this->getHeldItemIndex(), $slot);
		}
	}

	/**
	 * Sends the currently-held item to specified targets.
	 * @param Player|Player[] $target
	 */
	public function sendHeldItem($target)
	{
		if ($target instanceof Player) {
			$target = [$target];
		}

		/** @var Player[][] $protocolPlayers */
		$protocolPlayers = [];
		foreach ($target as $player) {
			$protocolPlayers[$player->getProtocolVersion()][] = $player;
		}

		foreach ($protocolPlayers as $protocolVersion => $players) {
			$pk = MobEquipmentPacket::create(
				$this->getHolder()->getId(),
				ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($this->getItemInHand(), $protocolVersion)),
				($protocolVersion >= ProtocolInfo::PROTOCOL_137 ? $this->getHeldItemSlot() : $this->getHeldItemIndex()),
				$this->getHeldItemIndex(),
				ContainerIds::INVENTORY
			);

			foreach ($players as $player) {
				$player->sendDataPacket($pk);
				if ($this->getHeldItemSlot() !== -1 && $player === $this->getHolder()) {
					$this->sendSlot($this->getHeldItemSlot(), $player);
				}
			}
		}
	}

	/**
	 * Returns the number of slots in the hotbar.
	 */
	public function getHotbarSize() : int
	{
		return 9;
	}

	public function sendCreativeContents() : void
	{
		//TODO: this mess shouldn't be in here
		$holder = $this->getHolder();
		if (!($holder instanceof Player)) {
			throw new LogicException("Cannot send creative inventory contents to non-player inventory holder");
		}

		$holder->sendDataPacket(CreativeInventoryCache::getInstance()->buildPacket($holder));
	}

	public function clearAll(bool $send = true) : void
	{
		$this->resetHotbar();
		parent::clearAll($send);
	}

	/**
	 * This override is here for documentation and code completion purposes only.
	 * @return Human|Player
	 */
	public function getHolder()
	{
		return $this->holder;
	}
}
