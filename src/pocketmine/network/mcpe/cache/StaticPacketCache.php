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

namespace pocketmine\network\mcpe\cache;

use pocketmine\network\mcpe\protocol\AvailableActorIdentifiersPacket;
use pocketmine\network\mcpe\protocol\BiomeDefinitionListPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\biome\BiomeDefinitionEntry;
use pocketmine\utils\Color;
use pocketmine\utils\Filesystem;
use pocketmine\utils\SingletonTrait;

use function array_diff;
use function count;
use function json_decode;
use function krsort;
use function scandir;

use const pocketmine\BEDROCK_DATA_PATH;

class StaticPacketCache
{
	use SingletonTrait;

	private static function make() : self
	{
		$biomeDefs = [];
		foreach (array_diff(scandir($biomeDefsDirectory = BEDROCK_DATA_PATH . 'biomes/'), ["..", "."]) as $protocol) {
			if ($protocol >= ProtocolInfo::PROTOCOL_800) {
				$biomeEntries = json_decode(Filesystem::fileGetContents($biomeDefsDirectory . $protocol . '/biome_definitions.json'), true);
				$entries = [];
				foreach ($biomeEntries as $name => $entry) {
					$entries[] = new BiomeDefinitionEntry(
						$name,
						$entry["id"],
						$entry["temperature"],
						$entry["downfall"],
						$entry["foliageSnow"] ?? 0,
						$entry["redSporeDensity"] ?? 0,
						$entry["blueSporeDensity"] ?? 0,
						$entry["ashDensity"] ?? 0,
						$entry["whiteAshDensity"] ?? 0,
						$entry["depth"],
						$entry["scale"],
						new Color(
							$entry["mapWaterColour"]["r"],
							$entry["mapWaterColour"]["g"],
							$entry["mapWaterColour"]["b"],
							$entry["mapWaterColour"]["a"]
						),
						$entry["rain"],
						count($entry["tags"]) > 0 ? $entry["tags"] : null,
					);
				}

				$biomeDefs[$protocol] = BiomeDefinitionListPacket::create("", $entries);
			} else {
				$biomeDefs[$protocol] = BiomeDefinitionListPacket::create(Filesystem::fileGetContents($biomeDefsDirectory . $protocol . '/biome_definitions.nbt'), []);
			}
		}
		krsort($biomeDefs);

		$actorIds = [];
		foreach (array_diff(scandir($actorIdsDirectory = BEDROCK_DATA_PATH . 'entity/'), ["..", "."]) as $protocol) {
			$actorIds[$protocol] = AvailableActorIdentifiersPacket::create(Filesystem::fileGetContents($actorIdsDirectory . $protocol . '/entity_identifiers.nbt'));
		}
		krsort($actorIds);

		return new self(
			$biomeDefs,
			$actorIds
		);
	}

	/**
	 * @param BiomeDefinitionListPacket[]       $biomeDefs
	 * @param AvailableActorIdentifiersPacket[] $availableActorIdentifiers
	 */
	public function __construct(
		private array $biomeDefs,
		private array $availableActorIdentifiers
	) {
	}

	public function getBiomeDefs(int $protocolVersion) : BiomeDefinitionListPacket
	{
		foreach ($this->biomeDefs as $protocol => $cache) {
			if ($protocolVersion >= $protocol) {
				return $cache;
			}
		}

		return BiomeDefinitionListPacket::create("", []);
	}

	public function getAvailableActorIdentifiers(int $protocolVersion) : AvailableActorIdentifiersPacket
	{
		foreach ($this->availableActorIdentifiers as $protocol => $cache) {
			if ($protocolVersion >= $protocol) {
				return $cache;
			}
		}

		return AvailableActorIdentifiersPacket::create("");
	}
}
