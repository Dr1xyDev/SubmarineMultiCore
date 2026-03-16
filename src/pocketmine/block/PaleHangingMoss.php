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

use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;

class PaleHangingMoss extends Flowable
{
	protected $id = self::PALE_HANGING_MOSS;

	public const int HANGING_FULL = 0;
	public const int HANGING_END = 1;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getToolType() : int
	{
		return BlockToolType::TYPE_SHEARS;
	}

	public function getName() : string
	{
		return "Pale Hanging Moss";
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool
	{
		$up = $this->getSide(Facing::UP);
		if (($up->isSolid() && !$up->isTransparent()) || $up->getId() === self::PALE_HANGING_MOSS) {
			if ($up->getId() === self::PALE_HANGING_MOSS && $up->getDamage() === self::HANGING_END) {
				$up->setDamage(self::HANGING_FULL);
				$this->getLevel()->setBlock($up, $up, true, true);
			}

			$this->meta = self::HANGING_END;
			$this->getLevel()->setBlock($this, $this, true, true);
			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void
	{
		$up = $this->getSide(Facing::UP);
		if (!(($up->isSolid() && !$up->isTransparent()) || $up->getId() === self::PALE_HANGING_MOSS)) {
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function onBreak(Item $item, Player $player = null) : bool
	{
		$up = $this->getSide(Facing::UP);
		if ($up->getId() === self::PALE_HANGING_MOSS && $up->getDamage() === self::HANGING_FULL) {
			$up->setDamage(self::HANGING_END);
			$this->getLevel()->setBlock($up, $up, true, true);
		}

		$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), true, true);
		return true;
	}

	public function getFlameEncouragement() : int
	{
		return 5;
	}

	public function getFlammability() : int
	{
		return 5;
	}

	public function getBlockProtocol(int $playerProtocol) : ?Block
	{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_766) {
			return BlockFactory::get(BlockIds::REEDS_BLOCK);
		}

		return null;
	}
}
