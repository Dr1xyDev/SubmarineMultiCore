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
use raklib\protocol\EncapsulatedPacket;
use raklib\protocol\PacketReliability;
use raklib\server\ipc\UserToRakLibThreadMessageProtocol as ITCProtocol;
use raklib\server\ServerEventSource;
use raklib\server\ServerInterface;

use function ord;
use function substr;

final class UserToRakLibThreadMessageReceiver implements ServerEventSource
{
	public function __construct(
		private InterThreadChannelReader $channel
	) {
	}

	public function process(ServerInterface $server) : bool
	{
		if (($packet = $this->channel->read()) !== null) {
			$id = ord($packet[0]);
			$offset = 1;
			if ($id === ITCProtocol::PACKET_ENCAPSULATED) {
				$sessionId = Binary::readInt(substr($packet, $offset, 4));
				$offset += 4;
				$flags = ord($packet[$offset++]);
				$immediate = ($flags & ITCProtocol::ENCAPSULATED_FLAG_IMMEDIATE) !== 0;
				$needACK = ($flags & ITCProtocol::ENCAPSULATED_FLAG_NEED_ACK) !== 0;

				$encapsulated = new EncapsulatedPacket();
				$encapsulated->reliability = ord($packet[$offset++]);

				if ($needACK) {
					$encapsulated->identifierACK = Binary::readInt(substr($packet, $offset, 4));
					$offset += 4;
				}

				if (PacketReliability::isSequencedOrOrdered($encapsulated->reliability)) {
					$encapsulated->orderChannel = ord($packet[$offset++]);
				}

				$encapsulated->buffer = substr($packet, $offset);
				$server->sendEncapsulated($sessionId, $encapsulated, $immediate);
			} elseif ($id === ITCProtocol::PACKET_RAW) {
				$len = ord($packet[$offset++]);
				$address = substr($packet, $offset, $len);
				$offset += $len;
				$port = Binary::readShort(substr($packet, $offset, 2));
				$offset += 2;
				$payload = substr($packet, $offset);
				$server->sendRaw($address, $port, $payload);
			} elseif ($id === ITCProtocol::PACKET_CLOSE_SESSION) {
				$sessionId = Binary::readInt(substr($packet, $offset, 4));
				$server->closeSession($sessionId);
			} elseif ($id === ITCProtocol::PACKET_SET_NAME) {
				$server->setName(substr($packet, $offset));
			} elseif ($id === ITCProtocol::PACKET_ENABLE_PORT_CHECK) {
				$server->setPortCheck(true);
			} elseif ($id === ITCProtocol::PACKET_DISABLE_PORT_CHECK) {
				$server->setPortCheck(false);
			} elseif ($id === ITCProtocol::PACKET_SET_PACKETS_PER_TICK_LIMIT) {
				$limit = Binary::readLong(substr($packet, $offset, 8));
				$server->setPacketsPerTickLimit($limit);
			} elseif ($id === ITCProtocol::PACKET_BLOCK_ADDRESS) {
				$len = ord($packet[$offset++]);
				$address = substr($packet, $offset, $len);
				$offset += $len;
				$timeout = Binary::readInt(substr($packet, $offset, 4));
				$server->blockAddress($address, $timeout);
			} elseif ($id === ITCProtocol::PACKET_UNBLOCK_ADDRESS) {
				$len = ord($packet[$offset++]);
				$address = substr($packet, $offset, $len);
				$server->unblockAddress($address);
			} elseif ($id === ITCProtocol::PACKET_RAW_FILTER) {
				$pattern = substr($packet, $offset);
				$server->addRawPacketFilter($pattern);
			}

			return true;
		}

		return false;
	}
}
