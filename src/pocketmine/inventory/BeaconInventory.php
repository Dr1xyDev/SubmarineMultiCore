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

namespace pocketmine\inventory;

use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\network\mcpe\protocol\types\inventory\UIInventorySlotOffset;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\Player;
use pocketmine\tile\Beacon;

class BeaconInventory extends ContainerInventory implements FakeInventory, FakeResultInventory
{
	public function __construct(Beacon $tile)
	{
		parent::__construct($tile);
	}

	public function getName() : string
	{
		return "Beacon";
	}

	public function getDefaultSize() : int
	{
		return 1;
	}

	public function getUIOffsets(?Player $player) : array
	{
		return [
			UIInventorySlotOffset::BEACON_PAYMENT => 0
		];
	}

	public function onResult(Player $player, Item $result) : bool
	{
		return true; // TODO: check beacon
	}

	public function getResultSlot() : int
	{
		return 0;
	}

	public function getNetworkType() : int
	{
		return WindowTypes::BEACON;
	}

	public function onClose(Player $who) : void
	{
		parent::onClose($who);

		$who->getLevel()->dropItem($this->getHolder()->add(0.5, 0.5, 0.5), $this->getItem($this->getResultSlot()));
		$this->clear($this->getResultSlot());
	}

	/**
	 * @param Player|Player[] $target
	 */
	public function sendContents($target) : void
	{
		if ($target instanceof Player) {
			$target = [$target];
		}

		foreach ($target as $player) {
			if (($id = $player->getWindowId($this)) === ContainerIds::NONE) {
				$this->close($player);
				continue;
			}
			if ($player->getProtocolVersion() < ProtocolInfo::PROTOCOL_137) {
				continue;
			}
			parent::sendContents($player);
		}
	}

	/**
	 * @param Player|Player[] $target
	 */
	public function sendSlot(int $index, $target) : void
	{
		if ($target instanceof Player) {
			$target = [$target];
		}

		foreach ($target as $player) {
			if (($id = $player->getWindowId($this)) === ContainerIds::NONE) {
				$this->close($player);
				continue;
			}
			if ($player->getProtocolVersion() < ProtocolInfo::PROTOCOL_137) {
				continue;
			}
			parent::sendSlot($index, $player);
		}
	}
}
