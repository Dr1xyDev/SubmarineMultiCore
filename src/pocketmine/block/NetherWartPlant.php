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

use pocketmine\event\block\BlockGrowEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;

use function mt_rand;

class NetherWartPlant extends Flowable
{
	protected $id = Block::NETHER_WART_PLANT;

	protected $itemId = Item::NETHER_WART;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Nether Wart";
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool
	{
		$down = $this->getSide(Facing::DOWN);
		if ($down->getId() === Block::SOUL_SAND) {
			$this->getLevel()->setBlock($blockReplace, $this, false, true);

			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void
	{
		if ($this->getSide(Facing::DOWN)->getId() !== Block::SOUL_SAND) {
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function ticksRandomly() : bool
	{
		return true;
	}

	public function onRandomTick() : void
	{
		if ($this->meta < 3 && mt_rand(0, 10) === 0) { //Still growing
			$block = clone $this;
			$block->meta++;
			$ev = new BlockGrowEvent($this, $block);
			$ev->call();
			if (!$ev->isCancelled()) {
				$this->getLevel()->setBlock($this, $ev->getNewState(), false, true);
			}
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [
			ItemFactory::get($this->getItemId(), 0, ($this->getDamage() === 3 ? mt_rand(2, 4) : 1))
		];
	}

	public function isAffectedBySilkTouch() : bool
	{
		return false;
	}
}
