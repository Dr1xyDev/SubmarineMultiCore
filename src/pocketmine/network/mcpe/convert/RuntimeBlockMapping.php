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

namespace pocketmine\network\mcpe\convert;

use pocketmine\block\Block;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\utils\AssumptionFailedError;

use function count;
use function file_get_contents;
use function getmypid;
use function json_decode;
use function mt_rand;

use function mt_srand;
use function shuffle;
use const pocketmine\BEDROCK_DATA_PATH;

/**
 * @internal
 */
final class RuntimeBlockMapping
{
	/** @var int[] */
	private array $legacyToRuntimeMap = [];
	/** @var int[] */
	private array $runtimeToLegacyMap = [];
	/** @var CompoundTag[]|array[]|null */
	private ?array $bedrockKnownStates = null;
	private ?string $bedrockEncodeBedrockKnownStates = null;

	/** @var self[] */
	private static array $instance = [];

	public static function getInstance(int $protocolVersion) : self
	{
		$protocolVersion = ProtocolConvertor::getInstance()->getBlockPaletteProtocol($protocolVersion);
		if (!isset(self::$instance[$protocolVersion])) {
			self::$instance[$protocolVersion] = new self($protocolVersion);
		}
		return self::$instance[$protocolVersion];
	}

	/**
	 * Randomizes the order of the runtimeID table to prevent plugins relying on them.
	 * Plugins shouldn't use this stuff anyway, but plugin devs have an irritating habit of ignoring what they
	 * aren't supposed to do, so we have to deliberately break it to make them stop.
	 */
	private static function randomizeTable(array $table) : array
	{
		$postSeed = mt_rand(); //save a seed to set afterwards, to avoid poor quality randoms
		mt_srand(getmypid()); //Use a seed which is the same on all threads. This isn't a secure seed, but we don't care.
		shuffle($table);
		mt_srand($postSeed); //restore a good quality seed that isn't dependent on PID
		return $table;
	}

	private function __construct(
		private readonly int $protocolVersion
	) {
		if ($protocolVersion >= ProtocolInfo::PROTOCOL_370) {
			$requiredBlockListFile = file_get_contents(BEDROCK_DATA_PATH . "block/" . $protocolVersion . "/required_block_states.nbt");
			if ($requiredBlockListFile === false) {
				throw new AssumptionFailedError("Missing required resource file");
			}
			$stream = new NetworkBinaryStream($requiredBlockListFile);
			$list = [];
			/** @var CompoundTag[] $list */
			while (!$stream->feof()) {
				$list[] = $stream->getNbtCompoundRoot();
			}

			if ($protocolVersion >= ProtocolInfo::PROTOCOL_419) {
				$this->bedrockKnownStates = $list;
				foreach ($list as $state) {
					self::registerMapping($state->getInt("runtime_id"), $state->getInt("legacy_id"), $state->getShort("data"));
				}
			} else {
				$list = self::randomizeTable($list);
				$this->bedrockKnownStates = $list;
				foreach ($list as $runtimeId => $tag) {
					$legacyStates = $tag->getListTag("LegacyStates")->getValue();
					foreach ($legacyStates as $legacyState) {
						/** @var CompoundTag $legacyState */
						$id = $legacyState->getInt("id");
						$meta = $legacyState->getShort("val");

						self::registerMapping($runtimeId, $id, $meta);
					}
				}
			}
		} else {
			$legacyStringToIntMap = LegacyBlockIdToStringIdMap::getInstance($protocolVersion);

			if ($protocolVersion >= ProtocolInfo::PROTOCOL_354) {
				$compressedTable = json_decode(file_get_contents(BEDROCK_DATA_PATH . "block/" . $protocolVersion . "/BlockPalette.json"), true);
				$decompressed = [];
				foreach ($compressedTable as $prefix => $entries) {
					foreach ($entries as $shortStringId => $states) {
						foreach ($states as $state) {
							$name = "$prefix:$shortStringId";
							if ($protocolVersion >= ProtocolInfo::PROTOCOL_361) {
								$legacyId = $legacyStringToIntMap->stringToLegacy($name);
								if ($legacyId === null) {
									continue;
								}

								$decompressed[] = [
									"name" => $name,
									"data" => $state,
									"legacy_id" => $legacyId
								];
							} else {
								$decompressed[] = [
									"name" => $name,
									"data" => $state
								];
							}
						}
					}
				}

				$bedrockKnownStates = self::randomizeTable($decompressed);
				$this->bedrockKnownStates = $bedrockKnownStates;

				foreach ($bedrockKnownStates as $k => $obj) {
					if ($protocolVersion >= ProtocolInfo::PROTOCOL_361) {
						if ($obj["data"] > 15) {
							//TODO: in 1.12 they started using data values bigger than 4 bits which we can't handle right now
							continue;
						}
						//this has to use the json offset to make sure the mapping is consistent with what we send over network, even though we aren't using all the entries
						self::registerMapping($k, $obj["legacy_id"], $obj["data"]);
					} else {
						$legacyId = $legacyStringToIntMap->stringToLegacy($obj["name"]);
						//this has to use the json offset to make sure the mapping is consistent with what we send over network, even though we aren't using all the entries
						if ($legacyId === null) {
							continue;
						}

						self::registerMapping($k, $legacyId, $obj["data"]);
					}
				}
			} elseif ($protocolVersion >= ProtocolInfo::PROTOCOL_223) {
				/** @var mixed[] $runtimeIdMap */
				$runtimeIdMap = json_decode(file_get_contents(BEDROCK_DATA_PATH . "block/" . $protocolVersion . "/BlockPalette.json"), true);
				$this->bedrockKnownStates = $runtimeIdMap;
				if ($protocolVersion >= ProtocolInfo::PROTOCOL_332) {
					foreach ($runtimeIdMap as $k => $obj) {
						$legacyId = $legacyStringToIntMap->stringToLegacy($obj["name"]);
						//this has to use the json offset to make sure the mapping is consistent with what we send over network, even though we aren't using all the entries
						if ($legacyId === null) {
							continue;
						}
						self::registerMapping($k, $legacyId, $obj["data"]);
					}
				} else {
					foreach ($runtimeIdMap as $k => $obj) {
						self::registerMapping($k, $obj["id"], $obj["data"]);
					}
				}
			}
		}
	}

	private function loadEncodeBedrockKnownStates() : string
	{
		if ($this->protocolVersion >= ProtocolInfo::PROTOCOL_370) {
			return (new NetworkLittleEndianNBTStream())->write(new ListTag("", $this->bedrockKnownStates));
		} elseif ($this->protocolVersion >= ProtocolInfo::PROTOCOL_223) {
			$bedrockKnownStates = $this->bedrockKnownStates;

			$stream = new NetworkBinaryStream();
			$stream->putUnsignedVarInt(count($bedrockKnownStates));
			foreach ($bedrockKnownStates as $v) {
				$stream->putString($v["name"]);
				$stream->putLShort($v["data"]);
				if ($this->protocolVersion >= ProtocolInfo::PROTOCOL_361) {
					$stream->putLShort($v["legacy_id"]);
				}
			}

			return $stream->getBuffer();
		} else {
			return "";
		}
	}

	public function toRuntimeId(int $internalStateId) : int
	{
		return $this->legacyToRuntimeMap[$internalStateId] ?? $this->legacyToRuntimeMap[Block::INFO_UPDATE << Block::INTERNAL_METADATA_BITS] ?? 0;
	}

	public function fromRuntimeId(int $runtimeId) : int
	{
		return $this->runtimeToLegacyMap[$runtimeId] ?? 0;
	}

	private function registerMapping(int $staticRuntimeId, int $legacyId, int $legacyMeta) : void
	{
		if ($legacyMeta >= (1 << Block::INTERNAL_METADATA_BITS)) {
			return;
		}

		$this->legacyToRuntimeMap[($legacyId << Block::INTERNAL_METADATA_BITS) | $legacyMeta] = $staticRuntimeId;
		$this->runtimeToLegacyMap[$staticRuntimeId] = ($legacyId << Block::INTERNAL_METADATA_BITS) | $legacyMeta;
	}

	/**
	 * WARNING: This method may load the palette from disk, which is a slow operation.
	 * Afterwards, it will cache the palette in memory, which requires (in some cases) tens of MB of memory.
	 * Avoid using this where possible.
	 *
	 * @return CompoundTag[]|array[]
	 */
	public function getBedrockKnownStates() : array
	{
		return $this->bedrockKnownStates;
	}

	public function getEncodeBedrockKnownStates() : string
	{
		return $this->bedrockEncodeBedrockKnownStates ??= $this->loadEncodeBedrockKnownStates();
	}

	public function getProtocolVersion() : int
	{
		return $this->protocolVersion;
	}
}
