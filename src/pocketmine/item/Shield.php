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

use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;

class Shield extends Item
{
	public function __construct(int $meta = 0)
	{
		parent::__construct(self::SHIELD, $meta, "Shield");
	}

	public function onUpdate(Player $player) : void
	{
		$player->setGenericFlag(Entity::DATA_FLAG_BLOCKING, $player->isSneaking());
	}

	public function getMaxStackSize() : int
	{
		return 1;
	}

	public function getItemProtocol(int $playerProtocol) : ?TranslatedItemData
	{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_340) {
			return new TranslatedItemData(ItemIds::PAPER, $this->getDamage());
		}

		return parent::getItemProtocol($playerProtocol);
	}
}
