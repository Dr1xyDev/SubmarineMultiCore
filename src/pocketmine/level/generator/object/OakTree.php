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

namespace pocketmine\level\generator\object;

use pocketmine\block\Leaves;
use pocketmine\block\Log;
use pocketmine\level\BlockTransaction;
use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;

class OakTree extends Tree
{
	public function __construct()
	{
		parent::__construct(new Log(Log::OAK), new Leaves(Leaves::OAK));
	}

	public function getBlockTransaction(ChunkManager $world, int $x, int $y, int $z, Random $random) : ?BlockTransaction
	{
		$this->treeHeight = $random->nextBoundedInt(3) + 4;
		return parent::getBlockTransaction($world, $x, $y, $z, $random);
	}
}
