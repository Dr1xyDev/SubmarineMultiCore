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

use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\utils\SingletonTrait;

use function array_diff;
use function file_exists;
use function rsort;
use function scandir;

class ProtocolConvertor
{
	use SingletonTrait;

	public const array PROTOCOL_CHUNK_VERSIONS = [
		ProtocolInfo::PROTOCOL_844,
		ProtocolInfo::PROTOCOL_827,
		ProtocolInfo::PROTOCOL_800,
		ProtocolInfo::PROTOCOL_786,
		ProtocolInfo::PROTOCOL_776,
		ProtocolInfo::PROTOCOL_766,
		ProtocolInfo::PROTOCOL_748,
		ProtocolInfo::PROTOCOL_729,
		ProtocolInfo::PROTOCOL_712,
		ProtocolInfo::PROTOCOL_685,
		ProtocolInfo::PROTOCOL_671,
		ProtocolInfo::PROTOCOL_662,
		ProtocolInfo::PROTOCOL_649,
		ProtocolInfo::PROTOCOL_630,
		ProtocolInfo::PROTOCOL_622,
		ProtocolInfo::PROTOCOL_618,
		ProtocolInfo::PROTOCOL_594,
		ProtocolInfo::PROTOCOL_589,
		ProtocolInfo::PROTOCOL_582,
		ProtocolInfo::PROTOCOL_575,
		ProtocolInfo::PROTOCOL_560,
		ProtocolInfo::PROTOCOL_544,
		ProtocolInfo::PROTOCOL_534,
		ProtocolInfo::PROTOCOL_527,
		ProtocolInfo::PROTOCOL_503,
		ProtocolInfo::PROTOCOL_486,
		ProtocolInfo::PROTOCOL_475,
		ProtocolInfo::PROTOCOL_471,
		ProtocolInfo::PROTOCOL_465,
		ProtocolInfo::PROTOCOL_448,
		ProtocolInfo::PROTOCOL_440,
		ProtocolInfo::PROTOCOL_428,
		ProtocolInfo::PROTOCOL_419,
		ProtocolInfo::PROTOCOL_408,
		ProtocolInfo::PROTOCOL_407,
		ProtocolInfo::PROTOCOL_389,
		ProtocolInfo::PROTOCOL_370,
		ProtocolInfo::PROTOCOL_361,
		ProtocolInfo::PROTOCOL_354,
		ProtocolInfo::PROTOCOL_340,
		ProtocolInfo::PROTOCOL_332,
		ProtocolInfo::PROTOCOL_332,
		ProtocolInfo::PROTOCOL_313,
		ProtocolInfo::PROTOCOL_282,
		ProtocolInfo::PROTOCOL_274,
		ProtocolInfo::PROTOCOL_261,
		ProtocolInfo::PROTOCOL_137,
		ProtocolInfo::PROTOCOL_110,
	];

	public function getChunkProtocol(int $protocolVersion) : int
	{
		foreach (self::PROTOCOL_CHUNK_VERSIONS as $protocol) {
			if ($protocolVersion >= $protocol) {
				return $protocol;
			}
		}

		throw new \UnexpectedValueException("Unknown chunk protocol");
	}

	public const array PROTOCOL_CRAFTING_VERSIONS = [
		ProtocolInfo::PROTOCOL_859,
		ProtocolInfo::PROTOCOL_844,
		ProtocolInfo::PROTOCOL_827,
		ProtocolInfo::PROTOCOL_818,
		ProtocolInfo::PROTOCOL_800,
		ProtocolInfo::PROTOCOL_786,
		ProtocolInfo::PROTOCOL_776,
		ProtocolInfo::PROTOCOL_766,
		ProtocolInfo::PROTOCOL_748,
		ProtocolInfo::PROTOCOL_729,
		ProtocolInfo::PROTOCOL_712,
		ProtocolInfo::PROTOCOL_685,
		ProtocolInfo::PROTOCOL_671,
		ProtocolInfo::PROTOCOL_662,
		ProtocolInfo::PROTOCOL_649,
		ProtocolInfo::PROTOCOL_630,
		ProtocolInfo::PROTOCOL_622,
		ProtocolInfo::PROTOCOL_618,
		ProtocolInfo::PROTOCOL_594,
		ProtocolInfo::PROTOCOL_589,
		ProtocolInfo::PROTOCOL_582,
		ProtocolInfo::PROTOCOL_575,
		ProtocolInfo::PROTOCOL_567,
		ProtocolInfo::PROTOCOL_560,
		ProtocolInfo::PROTOCOL_554,
		ProtocolInfo::PROTOCOL_544,
		ProtocolInfo::PROTOCOL_534,
		ProtocolInfo::PROTOCOL_527,
		ProtocolInfo::PROTOCOL_503,
		ProtocolInfo::PROTOCOL_486,
		ProtocolInfo::PROTOCOL_475,
		ProtocolInfo::PROTOCOL_471,
		ProtocolInfo::PROTOCOL_465,
		ProtocolInfo::PROTOCOL_448,
		ProtocolInfo::PROTOCOL_440,
		ProtocolInfo::PROTOCOL_431,
		ProtocolInfo::PROTOCOL_428,
		ProtocolInfo::PROTOCOL_419,
		ProtocolInfo::PROTOCOL_407,
		ProtocolInfo::PROTOCOL_388,
		ProtocolInfo::PROTOCOL_361,
		ProtocolInfo::PROTOCOL_354,
		ProtocolInfo::PROTOCOL_332,
		ProtocolInfo::PROTOCOL_313,
		ProtocolInfo::PROTOCOL_282,
		ProtocolInfo::PROTOCOL_137,
		ProtocolInfo::PROTOCOL_110
	];

	public function getCratingProtocol(int $playerProtocol) : int
	{
		foreach (self::PROTOCOL_CRAFTING_VERSIONS as $protocol) {
			if ($playerProtocol >= $protocol) {
				return $protocol;
			}
		}

		throw new \UnexpectedValueException("Unknown crafting protocol");
	}

	public const array PROTOCOL_BLOCK_PALETTE_VERSIONS = [
		ProtocolInfo::PROTOCOL_844,
		ProtocolInfo::PROTOCOL_827,
		ProtocolInfo::PROTOCOL_800,
		ProtocolInfo::PROTOCOL_786,
		ProtocolInfo::PROTOCOL_776,
		ProtocolInfo::PROTOCOL_766,
		ProtocolInfo::PROTOCOL_748,
		ProtocolInfo::PROTOCOL_729,
		ProtocolInfo::PROTOCOL_712,
		ProtocolInfo::PROTOCOL_685,
		ProtocolInfo::PROTOCOL_671,
		ProtocolInfo::PROTOCOL_662,
		ProtocolInfo::PROTOCOL_649,
		ProtocolInfo::PROTOCOL_630,
		ProtocolInfo::PROTOCOL_622,
		ProtocolInfo::PROTOCOL_618,
		ProtocolInfo::PROTOCOL_594,
		ProtocolInfo::PROTOCOL_589,
		ProtocolInfo::PROTOCOL_582,
		ProtocolInfo::PROTOCOL_575,
		ProtocolInfo::PROTOCOL_567,
		ProtocolInfo::PROTOCOL_560,
		ProtocolInfo::PROTOCOL_544,
		ProtocolInfo::PROTOCOL_527,
		ProtocolInfo::PROTOCOL_503,
		ProtocolInfo::PROTOCOL_486,
		ProtocolInfo::PROTOCOL_471,
		ProtocolInfo::PROTOCOL_465,
		ProtocolInfo::PROTOCOL_448,
		ProtocolInfo::PROTOCOL_440,
		ProtocolInfo::PROTOCOL_428,
		ProtocolInfo::PROTOCOL_419,
		ProtocolInfo::PROTOCOL_370,
		ProtocolInfo::PROTOCOL_361,
		ProtocolInfo::PROTOCOL_354,
		ProtocolInfo::PROTOCOL_340,
		ProtocolInfo::PROTOCOL_332,
		ProtocolInfo::PROTOCOL_313,
		ProtocolInfo::PROTOCOL_282,
		ProtocolInfo::PROTOCOL_274,
		ProtocolInfo::PROTOCOL_261,
		ProtocolInfo::PROTOCOL_223
	];

	public function getBlockPaletteProtocol(int $playerProtocol) : int
	{
		foreach (self::PROTOCOL_BLOCK_PALETTE_VERSIONS as $protocol) {
			if ($playerProtocol >= $protocol) {
				return $protocol;
			}
		}

		throw new \UnexpectedValueException("Unknown block palette protocol");
	}

	public const array PROTOCOL_ITEM_PALETTE_VERSIONS = [
		ProtocolInfo::PROTOCOL_859,
		ProtocolInfo::PROTOCOL_844,
		ProtocolInfo::PROTOCOL_827,
		ProtocolInfo::PROTOCOL_819,
		ProtocolInfo::PROTOCOL_818,
		ProtocolInfo::PROTOCOL_800,
		ProtocolInfo::PROTOCOL_786,
		ProtocolInfo::PROTOCOL_776,
		ProtocolInfo::PROTOCOL_766,
		ProtocolInfo::PROTOCOL_748,
		ProtocolInfo::PROTOCOL_729,
		ProtocolInfo::PROTOCOL_712,
		ProtocolInfo::PROTOCOL_685,
		ProtocolInfo::PROTOCOL_671,
		ProtocolInfo::PROTOCOL_662,
		ProtocolInfo::PROTOCOL_649,
		ProtocolInfo::PROTOCOL_630,
		ProtocolInfo::PROTOCOL_618,
		ProtocolInfo::PROTOCOL_594,
		ProtocolInfo::PROTOCOL_589,
		ProtocolInfo::PROTOCOL_582,
		ProtocolInfo::PROTOCOL_575,
		ProtocolInfo::PROTOCOL_567,
		ProtocolInfo::PROTOCOL_560,
		ProtocolInfo::PROTOCOL_534,
		ProtocolInfo::PROTOCOL_527,
		ProtocolInfo::PROTOCOL_503,
		ProtocolInfo::PROTOCOL_486,
		ProtocolInfo::PROTOCOL_475,
		ProtocolInfo::PROTOCOL_465,
		ProtocolInfo::PROTOCOL_448,
		ProtocolInfo::PROTOCOL_440,
		ProtocolInfo::PROTOCOL_431,
		ProtocolInfo::PROTOCOL_419
	];

	public function getItemPaletteProtocol(int $playerProtocol) : int
	{
		foreach (self::PROTOCOL_ITEM_PALETTE_VERSIONS as $protocol) {
			if ($playerProtocol >= $protocol) {
				return $protocol;
			}
		}

		throw new \UnexpectedValueException("Unknown item palette protocol");
	}

	public const array PROTOCOL_ITEM_LEGACY_VERSIONS = [
		ProtocolInfo::PROTOCOL_844,
		ProtocolInfo::PROTOCOL_827,
		ProtocolInfo::PROTOCOL_819,
		ProtocolInfo::PROTOCOL_818,
		ProtocolInfo::PROTOCOL_800,
		ProtocolInfo::PROTOCOL_786,
		ProtocolInfo::PROTOCOL_766,
		ProtocolInfo::PROTOCOL_748,
		ProtocolInfo::PROTOCOL_729,
		ProtocolInfo::PROTOCOL_712,
		ProtocolInfo::PROTOCOL_685,
		ProtocolInfo::PROTOCOL_671,
		ProtocolInfo::PROTOCOL_662,
		ProtocolInfo::PROTOCOL_649,
		ProtocolInfo::PROTOCOL_630,
		ProtocolInfo::PROTOCOL_589,
		ProtocolInfo::PROTOCOL_582,
		ProtocolInfo::PROTOCOL_575,
		ProtocolInfo::PROTOCOL_567,
		ProtocolInfo::PROTOCOL_560,
		ProtocolInfo::PROTOCOL_527,
		ProtocolInfo::PROTOCOL_503,
		ProtocolInfo::PROTOCOL_486,
		ProtocolInfo::PROTOCOL_475,
		ProtocolInfo::PROTOCOL_465,
		ProtocolInfo::PROTOCOL_448,
		ProtocolInfo::PROTOCOL_440,
		ProtocolInfo::PROTOCOL_407,
		ProtocolInfo::PROTOCOL_389,
		ProtocolInfo::PROTOCOL_370,
		ProtocolInfo::PROTOCOL_361,
		ProtocolInfo::PROTOCOL_340,
		ProtocolInfo::PROTOCOL_332,
		ProtocolInfo::PROTOCOL_313,
		ProtocolInfo::PROTOCOL_282,
		ProtocolInfo::PROTOCOL_274,
		ProtocolInfo::PROTOCOL_261,
		ProtocolInfo::PROTOCOL_137,
		ProtocolInfo::PROTOCOL_110
	];

	public function getLegacyItemProtocol(int $playerProtocol) : int
	{
		foreach (self::PROTOCOL_ITEM_LEGACY_VERSIONS as $protocol) {
			if ($playerProtocol >= $protocol) {
				return $protocol;
			}
		}

		throw new \UnexpectedValueException("Unknown item legacy protocol");
	}

	public const array PROTOCOL_BLOCK_LEGACY_VERSIONS = [
		ProtocolInfo::PROTOCOL_844,
		ProtocolInfo::PROTOCOL_827,
		ProtocolInfo::PROTOCOL_800,
		ProtocolInfo::PROTOCOL_786,
		ProtocolInfo::PROTOCOL_766,
		ProtocolInfo::PROTOCOL_748,
		ProtocolInfo::PROTOCOL_729,
		ProtocolInfo::PROTOCOL_712,
		ProtocolInfo::PROTOCOL_685,
		ProtocolInfo::PROTOCOL_671,
		ProtocolInfo::PROTOCOL_662,
		ProtocolInfo::PROTOCOL_649,
		ProtocolInfo::PROTOCOL_630,
		ProtocolInfo::PROTOCOL_622,
		ProtocolInfo::PROTOCOL_618,
		ProtocolInfo::PROTOCOL_594,
		ProtocolInfo::PROTOCOL_589,
		ProtocolInfo::PROTOCOL_582,
		ProtocolInfo::PROTOCOL_575,
		ProtocolInfo::PROTOCOL_567,
		ProtocolInfo::PROTOCOL_560,
		ProtocolInfo::PROTOCOL_544,
		ProtocolInfo::PROTOCOL_527,
		ProtocolInfo::PROTOCOL_503,
		ProtocolInfo::PROTOCOL_486,
		ProtocolInfo::PROTOCOL_471,
		ProtocolInfo::PROTOCOL_465,
		ProtocolInfo::PROTOCOL_448,
		ProtocolInfo::PROTOCOL_440,
		ProtocolInfo::PROTOCOL_428,
		ProtocolInfo::PROTOCOL_419,
		ProtocolInfo::PROTOCOL_407,
		ProtocolInfo::PROTOCOL_370,
		ProtocolInfo::PROTOCOL_361,
		ProtocolInfo::PROTOCOL_354,
		ProtocolInfo::PROTOCOL_340,
		ProtocolInfo::PROTOCOL_332,
		ProtocolInfo::PROTOCOL_313,
		ProtocolInfo::PROTOCOL_282,
		ProtocolInfo::PROTOCOL_274,
		ProtocolInfo::PROTOCOL_261,
		ProtocolInfo::PROTOCOL_223,
		ProtocolInfo::PROTOCOL_137,
		ProtocolInfo::PROTOCOL_110
	];

	public function getLegacyBlockProtocol(int $playerProtocol) : int
	{
		foreach (self::PROTOCOL_BLOCK_LEGACY_VERSIONS as $protocol) {
			if ($playerProtocol >= $protocol) {
				return $protocol;
			}
		}

		throw new \UnexpectedValueException("Unknown block legacy protocol");
	}

	public const array PROTOCOL_ITEM_MAP_VERSIONS = [
		ProtocolInfo::PROTOCOL_544,
		ProtocolInfo::PROTOCOL_354,
		ProtocolInfo::PROTOCOL_141,
		ProtocolInfo::PROTOCOL_137,
		ProtocolInfo::PROTOCOL_110,
	];

	public function getItemMapProtocol(int $playerProtocol) : int
	{
		foreach (self::PROTOCOL_ITEM_MAP_VERSIONS as $protocol) {
			if ($playerProtocol >= $protocol) {
				return $protocol;
			}
		}

		throw new \UnexpectedValueException("Unknown map protocol");
	}

	public function findProtocolsFile(string $path, string $file) : array
	{
		$protocols = [];
		foreach (array_diff(scandir($path), ["..", "."]) as $protocol) {
			$fullFile = $path . "/" . $protocol . "/" . $file;
			if (file_exists($fullFile)) {
				$protocols[] = (int) $protocol;
			}
		}

		rsort($protocols);

		return $protocols;
	}
}
