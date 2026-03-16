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

namespace raklib\protocol;

abstract class PacketReliability
{
	/*
	 * From https://github.com/OculusVR/RakNet/blob/master/Source/PacketPriority.h
	 *
	 * Default: 0b010 (2) or 0b011 (3)
	 */

	public const UNRELIABLE = 0;
	public const UNRELIABLE_SEQUENCED = 1;
	public const RELIABLE = 2;
	public const RELIABLE_ORDERED = 3;
	public const RELIABLE_SEQUENCED = 4;

	/* The following reliabilities are used in RakNet internals, but never sent on the wire. */
	public const UNRELIABLE_WITH_ACK_RECEIPT = 5;
	public const RELIABLE_WITH_ACK_RECEIPT = 6;
	public const RELIABLE_ORDERED_WITH_ACK_RECEIPT = 7;

	public const MAX_ORDER_CHANNELS = 32;

	public static function isReliable(int $reliability) : bool
	{
		return (
			$reliability === self::RELIABLE ||
			$reliability === self::RELIABLE_ORDERED ||
			$reliability === self::RELIABLE_SEQUENCED ||
			$reliability === self::RELIABLE_WITH_ACK_RECEIPT ||
			$reliability === self::RELIABLE_ORDERED_WITH_ACK_RECEIPT
		);
	}

	public static function isSequenced(int $reliability) : bool
	{
		return (
			$reliability === self::UNRELIABLE_SEQUENCED ||
			$reliability === self::RELIABLE_SEQUENCED
		);
	}

	public static function isOrdered(int $reliability) : bool
	{
		return (
			$reliability === self::RELIABLE_ORDERED ||
			$reliability === self::RELIABLE_ORDERED_WITH_ACK_RECEIPT
		);
	}

	public static function isSequencedOrOrdered(int $reliability) : bool
	{
		return (
			$reliability === self::UNRELIABLE_SEQUENCED ||
			$reliability === self::RELIABLE_ORDERED ||
			$reliability === self::RELIABLE_SEQUENCED ||
			$reliability === self::RELIABLE_ORDERED_WITH_ACK_RECEIPT
		);
	}
}
