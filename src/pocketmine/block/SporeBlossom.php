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
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;

class SporeBlossom extends Flowable
{
	protected $id = self::SPORE_BLOSSOM;

	public function __construct(int $meta = 0)
	{
		$this->meta = $meta;
	}

	public function getName() : string
	{
		return "Spore Blossom";
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB
	{
		return new AxisAlignedBB(
			$this->x + 0.125,
			$this->y + 0.8125,
			$this->z + 0.125,
			$this->x + 0.875,
			$this->y + 1,
			$this->z + 0.875,
		);
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool
	{
		if ($this->canSupportToFullSolid($this->getSide(Facing::UP))) {
			return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}
		return false;
	}

	public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock) : bool
	{
		return $this->canSupportToFullSolid($blockReplace->getSide(Facing::UP)) && parent::canBePlacedAt($blockReplace, $clickVector, $face, $isClickedBlock);
	}

	public function onNearbyBlockChange() : void
	{
		if (!$this->canSupportToFullSolid($this->getSide(Facing::UP))) {
			$this->level->useBreakOn($this);
		}
	}

	public function getBlockProtocol(int $playerProtocol) : ?Block
	{
		if ($playerProtocol < ProtocolInfo::PROTOCOL_440) {
			return BlockFactory::get(BlockIds::RED_FLOWER);
		}

		return null;
	}
}
