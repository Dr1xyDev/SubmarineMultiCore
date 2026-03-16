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

use pocketmine\block\utils\FortuneDropHelper;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class AmethystCluster extends AmethystBud
{
	protected $id = self::AMETHYST_CLUSTER;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Amethyst Cluster";
	}

	public function getLightLevel() : int
	{
		return 5;
	}

	public function isAffectedBySilkTouch() : bool
	{
		return true;
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [ItemFactory::get(ItemIds::AMETHYST_SHARD)->setCount(FortuneDropHelper::weighted($item, min: 4, maxBase: 4))];
	}

	public function getDropsForIncompatibleTool(Item $item) : array
	{
		return [ItemFactory::get(ItemIds::AMETHYST_SHARD)->setCount(FortuneDropHelper::weighted($item, min: 2, maxBase: 2))];
	}
}
