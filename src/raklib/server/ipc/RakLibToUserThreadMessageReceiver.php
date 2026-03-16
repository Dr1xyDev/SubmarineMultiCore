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

use pocketmine\utils\Binary;
use raklib\server\ipc\RakLibToUserThreadMessageProtocol as ITCProtocol;
use raklib\server\ServerEventListener;

use function inet_ntop;
use function ord;
use function substr;

final class RakLibToUserThreadMessageReceiver
{
	public function __construct(
		private InterThreadChannelReader $channel
	) {
	}

	public function handle(ServerEventListener $listener) : bool
	{
		if (($packet = $this->channel->read()) !== null) {
			$id = ord($packet[0]);
			$offset = 1;
			if ($id === ITCProtocol::PACKET_ENCAPSULATED) {
				$sessionId = Binary::readInt(substr($packet, $offset, 4));
				$offset += 4;
				$buffer = substr($packet, $offset);
				$listener->onPacketReceive($sessionId, $buffer);
			} elseif ($id === ITCProtocol::PACKET_RAW) {
				$len = ord($packet[$offset++]);
				$address = substr($packet, $offset, $len);
				$offset += $len;
				$port = Binary::readShort(substr($packet, $offset, 2));
				$offset += 2;
				$payload = substr($packet, $offset);
				$listener->onRawPacketReceive($address, $port, $payload);
			} elseif ($id === ITCProtocol::PACKET_REPORT_BANDWIDTH_STATS) {
				$sentBytes = Binary::readLong(substr($packet, $offset, 8));
				$offset += 8;
				$receivedBytes = Binary::readLong(substr($packet, $offset, 8));
				$listener->onBandwidthStatsUpdate($sentBytes, $receivedBytes);
			} elseif ($id === ITCProtocol::PACKET_OPEN_SESSION) {
				$sessionId = Binary::readInt(substr($packet, $offset, 4));
				$offset += 4;
				$len = ord($packet[$offset++]);
				$rawAddr = substr($packet, $offset, $len);
				$offset += $len;
				$address = inet_ntop($rawAddr);
				if ($address === false) {
					throw new \RuntimeException("Unexpected invalid IP address in inter-thread message");
				}
				$port = Binary::readShort(substr($packet, $offset, 2));
				$offset += 2;
				$protocol = ord($packet[$offset++]);
				$clientID = Binary::readLong(substr($packet, $offset, 8));
				$listener->onClientConnect($sessionId, $address, $port, $clientID, $protocol);
			} elseif ($id === ITCProtocol::PACKET_CLOSE_SESSION) {
				$sessionId = Binary::readInt(substr($packet, $offset, 4));
				$offset += 4;
				$reason = ord($packet[$offset]);
				$listener->onClientDisconnect($sessionId, $reason);
			} elseif ($id === ITCProtocol::PACKET_ACK_NOTIFICATION) {
				$sessionId = Binary::readInt(substr($packet, $offset, 4));
				$offset += 4;
				$identifierACK = Binary::readInt(substr($packet, $offset, 4));
				$listener->onPacketAck($sessionId, $identifierACK);
			} elseif ($id === ITCProtocol::PACKET_REPORT_PING) {
				$sessionId = Binary::readInt(substr($packet, $offset, 4));
				$offset += 4;
				$pingMS = Binary::readInt(substr($packet, $offset, 4));
				$listener->onPingMeasure($sessionId, $pingMS);
			}

			return true;
		}

		return false;
	}
}
