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
 * This interface contains descriptions of ITC packets used to transmit data into RakLib from the main thread.
 */
final class UserToRakLibThreadMessageProtocol
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
	 * byte (flags, last 3 bits, priority)
	 * byte (reliability)
	 * int32 (ack identifier)
	 * byte? (order channel, only when sequenced or ordered reliability)
	 * byte[] (user packet payload)
	 */
	public const PACKET_ENCAPSULATED = 0x01;

	public const ENCAPSULATED_FLAG_NEED_ACK = 1 << 0;
	public const ENCAPSULATED_FLAG_IMMEDIATE = 1 << 1;

	/*
	 * CLOSE_SESSION payload:
	 * int32 (internal session ID)
	 */
	public const PACKET_CLOSE_SESSION = 0x02;

	/*
	 * RAW payload:
	 * byte (address length)
	 * byte[] (address from/to)
	 * short (port)
	 * byte[] (payload)
	 */
	public const PACKET_RAW = 0x04;

	/*
	 * BLOCK_ADDRESS payload:
	 * byte (address length)
	 * byte[] (address)
	 * int (timeout)
	 */
	public const PACKET_BLOCK_ADDRESS = 0x05;

	/*
	 * UNBLOCK_ADDRESS payload:
	 * byte (address length)
	 * byte[] (address)
	 */
	public const PACKET_UNBLOCK_ADDRESS = 0x06;

	/*
	 * RAW_FILTER payload:
	 * byte[] (pattern)
	 */
	public const PACKET_RAW_FILTER = 0x07;

	/*
	 * SET_NAME payload:
	 * byte[] (name)
	 */
	public const PACKET_SET_NAME = 0x08;

	/* No payload */
	public const PACKET_ENABLE_PORT_CHECK = 0x09;

	/* No payload */
	public const PACKET_DISABLE_PORT_CHECK = 0x10;

	/*
	 * PACKETS_PER_TICK_LIMIT payload:
	 * int64 (limit)
	 */
	public const PACKET_SET_PACKETS_PER_TICK_LIMIT = 0x11;
}
