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

namespace pocketmine\event\block;

use pocketmine\block\Block;
use pocketmine\event\Cancellable;

/**
 * Called when plants or crops grow.
 */
class BlockGrowEvent extends BlockEvent implements Cancellable
{
	/** @var Block */
	private $newState;

	public function __construct(Block $block, Block $newState)
	{
		parent::__construct($block);
		$this->newState = $newState;
	}

	public function getNewState() : Block
	{
		return $this->newState;
	}

	public function setNewState(Block $block) : void
	{
		$this->newState = $block;
	}
}
