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

namespace pocketmine\level\light;

use pocketmine\block\BlockFactory;
use pocketmine\level\format\Chunk;
use pocketmine\level\format\io\FastChunkSerializer;
use pocketmine\level\Level;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class LightPopulationTask extends AsyncTask
{
	public $levelId;
	public $chunk;

	public function __construct(Level $level, Chunk $chunk)
	{
		$this->levelId = $level->getId();
		$this->chunk = FastChunkSerializer::serializeTerrain($chunk);
	}

	public function onRun() : void
	{
		if (!BlockFactory::isInit()) {
			BlockFactory::init();
		}

		$chunk = FastChunkSerializer::deserializeTerrain($this->chunk);

		$chunk->recalculateHeightMap();
		$chunk->populateSkyLight();
		$chunk->setLightPopulated();

		$this->chunk = FastChunkSerializer::serializeTerrain($chunk);
	}

	public function onCompletion(Server $server)
	{
		$level = $server->getLevel($this->levelId);
		if ($level !== null) {
			/** @var Chunk $chunk */
			$chunk = FastChunkSerializer::deserializeTerrain($this->chunk);
			$level->generateChunkCallback($chunk->getX(), $chunk->getZ(), $chunk);
		}
	}
}
