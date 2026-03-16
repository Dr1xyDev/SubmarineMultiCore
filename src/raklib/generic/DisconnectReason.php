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

namespace raklib\generic;

final class DisconnectReason
{
	public const CLIENT_DISCONNECT = 0;
	public const SERVER_DISCONNECT = 1;
	public const PEER_TIMEOUT = 2;
	public const CLIENT_RECONNECT = 3;
	public const SERVER_SHUTDOWN = 4; //TODO: do we really need a separate reason for this in addition to SERVER_DISCONNECT?
	public const SPLIT_PACKET_TOO_LARGE = 5;
	public const SPLIT_PACKET_TOO_MANY_CONCURRENT = 6;
	public const SPLIT_PACKET_INVALID_PART_INDEX = 7;
	public const SPLIT_PACKET_INCONSISTENT_HEADER = 8;

	public static function toString(int $reason) : string
	{
		return match($reason) {
			self::CLIENT_DISCONNECT => "client disconnect",
			self::SERVER_DISCONNECT => "server disconnect",
			self::PEER_TIMEOUT => "timeout",
			self::CLIENT_RECONNECT => "new session established on same address and port",
			self::SERVER_SHUTDOWN => "server shutdown",
			self::SPLIT_PACKET_TOO_LARGE => "received packet split into more parts than allowed",
			self::SPLIT_PACKET_TOO_MANY_CONCURRENT => "too many received split packets being reassembled at once",
			self::SPLIT_PACKET_INVALID_PART_INDEX => "invalid split packet part index",
			self::SPLIT_PACKET_INCONSISTENT_HEADER => "received split packet header inconsistent with previous fragments",
			default => "Unknown reason $reason"
		};
	}
}
