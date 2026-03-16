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

namespace raklib\server\ipc;

/**
 * @internal
 * This interface contains descriptions of ITC packets used to transmit data from RakLib to the main thread.
 */
final class RakLibToUserThreadMessageProtocol
{
	private function __construct()
	{
		//NOOP
	}

	/*
	 * Internal Packet:
	 * byte (packet ID)
	 * byte[] (payload)
	 */

	/*
	 * ENCAPSULATED payload:
	 * int32 (internal session ID)
	 * byte[] (user packet payload)
	 */
	public const PACKET_ENCAPSULATED = 0x01;

	/*
	 * OPEN_SESSION payload:
	 * int32 (internal session ID)
	 * byte (address length)
	 * byte[] (address)
	 * short (port)
	 * long (clientID)
	 */
	public const PACKET_OPEN_SESSION = 0x02;

	/*
	 * CLOSE_SESSION payload:
	 * int32 (internal session ID)
	 * byte (reason)
	 */
	public const PACKET_CLOSE_SESSION = 0x03;

	/*
	 * ACK_NOTIFICATION payload:
	 * int32 (internal session ID)
	 * int32 (identifierACK)
	 */
	public const PACKET_ACK_NOTIFICATION = 0x04;

	/*
	 * REPORT_BANDWIDTH_STATS payload:
	 * int64 (sent bytes diff)
	 * int64 (received bytes diff)
	 */
	public const PACKET_REPORT_BANDWIDTH_STATS = 0x05;

	/*
	 * RAW payload:
	 * byte (address length)
	 * byte[] (address from/to)
	 * short (port)
	 * byte[] (payload)
	 */
	public const PACKET_RAW = 0x06;

	/*
	 * REPORT_PING payload:
	 * int32 (internal session ID)
	 * int32 (measured latency in MS)
	 */
	public const PACKET_REPORT_PING = 0x07;

}
