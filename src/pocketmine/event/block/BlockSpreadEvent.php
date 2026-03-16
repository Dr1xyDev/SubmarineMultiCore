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

/**
 * Called when a block spreads to another block, such as grass spreading to nearby dirt blocks.
 */
class BlockSpreadEvent extends BlockFormEvent
{
	/** @var Block */
	private $source;

	public function __construct(Block $block, Block $source, Block $newState)
	{
		parent::__construct($block, $newState);
		$this->source = $source;
	}

	public function getSource() : Block
	{
		return $this->source;
	}
}
