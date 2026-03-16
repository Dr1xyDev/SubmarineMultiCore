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

namespace pocketmine\network\mcpe\protocol\types;

class StructureSettings
{
	/** @var string */
	public $paletteName;
	/** @var bool */
	public $ignoreEntities;
	/** @var bool */
	public $ignoreBlocks;
	/** @var int */
	public $structureSizeX;
	/** @var int */
	public $structureSizeY;
	/** @var int */
	public $structureSizeZ;
	/** @var int */
	public $structureOffsetX;
	/** @var int */
	public $structureOffsetY;
	/** @var int */
	public $structureOffsetZ;
	/** @var int */
	public $lastTouchedByPlayerID;
	/** @var int */
	public $rotation;
	/** @var int */
	public $mirror;
	/** @var float */
	public $integrityValue;
	/** @var int */
	public $integritySeed;
}
