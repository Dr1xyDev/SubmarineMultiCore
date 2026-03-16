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

use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\level\particle\BoneMealParticle;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;
use function floor;

class Wildflowers extends Flowable
{
	protected $id = self::WILDFLOWERS;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Wildflowers";
	}

	public function getVariantBitmask() : int
	{
		return 0;
	}

	public function onActivate(Item $item, Player $player = null) : bool
	{
		if ($item instanceof Fertilizer) {
			if ($this->getFlowers() < 3) {
				$this->setFlowers($this->getFlowers() + 1);
				$this->level->setBlock($this, $this, true);
			} else {
				$this->level->dropItem($this->add(0.5, 0.5, 0.5), ItemFactory::get(ItemIds::WILDFLOWERS));
			}
			$this->level->addParticle(new BoneMealParticle($this));
			$item->count--;
			return true;
		}

		if ($item->getId() == $this->getItemId() && $this->getFlowers() < 3) {
			$this->setFlowers($this->getFlowers() + 1);
			$this->level->setBlock($this, $this, true);
			$item->count--;
			return true;
		}

		return false;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool
	{
		if (!$blockReplace->getSide(Facing::DOWN)->isTransparent()) {
			$this->setFace($player->getDirection());
			$this->getLevel()->setBlock($blockReplace, $this, true);
			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void
	{
		if ($this->getSide(Facing::DOWN)->isTransparent()) {
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		$items = [];
		for ($i = 0; $i <= $this->getFlowers(); ++$i) {
			$items[] = ItemFactory::get(ItemIds::WILDFLOWERS);
		}

		return $items;
	}

	public function getFlowers() : int {
		return $this->getDamage() & 0x03;
	}

	public function setFlowers(int $value) : void {
		$this->meta = ($this->meta & ~0x03) | ($value ? ($value & 0x03) : 0);
	}

	public function getFace() : int {
		return (floor($this->meta / 4) - 1 + 4) % 4;
	}

	public function setFace(int $face) : void {
		$this->meta = (4 * (($face & 0x03) + 1) % 16) | $this->getFlowers();
	}

	public function getBlockProtocol(int $playerProtocol) : ?Block
	{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_786) {
			return BlockFactory::get(BlockIds::YELLOW_FLOWER);
		}

		return null;
	}
}
