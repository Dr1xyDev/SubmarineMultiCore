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

namespace pocketmine\level\generator;

use pocketmine\level\format\Chunk;
use pocketmine\level\format\io\FastChunkSerializer;
use pocketmine\level\Level;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class PopulationTask extends AsyncTask
{
	public $state;
	public $levelId;
	public $chunk;

	public $chunk0;
	public $chunk1;
	public $chunk2;
	public $chunk3;
	//center chunk
	public $chunk5;
	public $chunk6;
	public $chunk7;
	public $chunk8;

	public function __construct(Level $level, Chunk $chunk)
	{
		$this->state = true;
		$this->levelId = $level->getId();
		$this->chunk = FastChunkSerializer::serializeTerrain($chunk);

		foreach ($level->getAdjacentChunks($chunk->getX(), $chunk->getZ()) as $i => $c) {
			$this->{"chunk$i"} = $c !== null ? FastChunkSerializer::serializeTerrain($c) : null;
		}
	}

	public function onRun() : void
	{
		$managerContext = ThreadLocalGeneratorContext::fetch($this->levelId);
		$generatorContext = ThreadLocalManagerContext::fetch($this->levelId);
		if ($managerContext === null || $generatorContext === null) {
			$this->state = false;
			return;
		}

		$manager = $generatorContext->getManager();
		$generator = $managerContext->getGenerator();

		/** @var Chunk[] $chunks */
		$chunks = [];

		$chunk = FastChunkSerializer::deserializeTerrain($this->chunk);

		for ($i = 0; $i < 9; ++$i) {
			if ($i === 4) {
				continue;
			}
			$xx = -1 + $i % 3;
			$zz = -1 + (int) ($i / 3);
			$ck = $this->{"chunk$i"};
			if ($ck === null) {
				$chunks[$i] = new Chunk($chunk->getX() + $xx, $chunk->getZ() + $zz);
			} else {
				$chunks[$i] = FastChunkSerializer::deserializeTerrain($ck);
			}
		}

		$manager->setChunk($chunk->getX(), $chunk->getZ(), $chunk);
		if (!$chunk->isGenerated()) {
			$generator->generateChunk($chunk->getX(), $chunk->getZ());
			$chunk->setGenerated();
		}

		foreach ($chunks as $c) {
			if ($c !== null) {
				$manager->setChunk($c->getX(), $c->getZ(), $c);
				if (!$c->isGenerated()) {
					$generator->generateChunk($c->getX(), $c->getZ());
					$c = $manager->getChunk($c->getX(), $c->getZ());
					$c->setGenerated();
				}
			}
		}

		$generator->populateChunk($chunk->getX(), $chunk->getZ());

		$chunk = $manager->getChunk($chunk->getX(), $chunk->getZ());
		$chunk->recalculateHeightMap();
		$chunk->populateSkyLight();
		$chunk->setLightPopulated();
		$chunk->setPopulated();
		$this->chunk = FastChunkSerializer::serializeTerrain($chunk);

		$manager->setChunk($chunk->getX(), $chunk->getZ(), null);

		foreach ($chunks as $i => $c) {
			if ($c !== null) {
				$c = $chunks[$i] = $manager->getChunk($c->getX(), $c->getZ());
				if (!$c->hasChanged()) {
					$chunks[$i] = null;
				}
			} else {
				//This way non-changed chunks are not set
				$chunks[$i] = null;
			}
		}

		$manager->cleanChunks();

		for ($i = 0; $i < 9; ++$i) {
			if ($i === 4) {
				continue;
			}

			$this->{"chunk$i"} = $chunks[$i] !== null ? FastChunkSerializer::serializeTerrain($chunks[$i]) : null;
		}
	}

	public function onCompletion(Server $server) : void
	{
		$level = $server->getLevel($this->levelId);
		if ($level !== null) {
			if (!$this->state) {
				//$level->registerGeneratorToWorker($this->workerId);
				return;
			}

			$chunk = FastChunkSerializer::deserializeTerrain($this->chunk);

			for ($i = 0; $i < 9; ++$i) {
				if ($i === 4) {
					continue;
				}
				$c = $this->{"chunk$i"};
				if ($c !== null) {
					$c = FastChunkSerializer::deserializeTerrain($c);
					$level->generateChunkCallback($c->getX(), $c->getZ(), $this->state ? $c : null);
				}
			}

			$level->generateChunkCallback($chunk->getX(), $chunk->getZ(), $this->state ? $chunk : null);
		}
	}
}
