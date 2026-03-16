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
use raklib\server\ServerInterface;

use function chr;
use function strlen;

class UserToRakLibThreadMessageSender implements ServerInterface
{
	public function __construct(
		private InterThreadChannelWriter $channel
	) {
	}

	public function sendEncapsulated(int $sessionId, EncapsulatedPacket $packet, bool $immediate = false) : void
	{
		$flags =
			($immediate ? ITCProtocol::ENCAPSULATED_FLAG_IMMEDIATE : 0) |
			($packet->identifierACK !== null ? ITCProtocol::ENCAPSULATED_FLAG_NEED_ACK : 0);

		$buffer = chr(ITCProtocol::PACKET_ENCAPSULATED) .
			Binary::writeInt($sessionId) .
			chr($flags) .
			chr($packet->reliability) .
			($packet->identifierACK !== null ? Binary::writeInt($packet->identifierACK) : "") .
			(PacketReliability::isSequencedOrOrdered($packet->reliability) ? chr($packet->orderChannel) : "") .
			$packet->buffer;
		$this->channel->write($buffer);
	}

	public function sendRaw(string $address, int $port, string $payload) : void
	{
		$buffer = chr(ITCProtocol::PACKET_RAW) . chr(strlen($address)) . $address . Binary::writeShort($port) . $payload;
		$this->channel->write($buffer);
	}

	public function closeSession(int $sessionId) : void
	{
		$buffer = chr(ITCProtocol::PACKET_CLOSE_SESSION) . Binary::writeInt($sessionId);
		$this->channel->write($buffer);
	}

	public function setName(string $name) : void
	{
		$this->channel->write(chr(ITCProtocol::PACKET_SET_NAME) . $name);
	}

	public function setPortCheck(bool $value) : void
	{
		$this->channel->write(chr($value ? ITCProtocol::PACKET_ENABLE_PORT_CHECK : ITCProtocol::PACKET_DISABLE_PORT_CHECK));
	}

	public function setPacketsPerTickLimit(int $limit) : void
	{
		$this->channel->write(chr(ITCProtocol::PACKET_SET_PACKETS_PER_TICK_LIMIT) . Binary::writeLong($limit));
	}

	public function blockAddress(string $address, int $timeout) : void
	{
		$buffer = chr(ITCProtocol::PACKET_BLOCK_ADDRESS) . chr(strlen($address)) . $address . Binary::writeInt($timeout);
		$this->channel->write($buffer);
	}

	public function unblockAddress(string $address) : void
	{
		$buffer = chr(ITCProtocol::PACKET_UNBLOCK_ADDRESS) . chr(strlen($address)) . $address;
		$this->channel->write($buffer);
	}

	public function addRawPacketFilter(string $regex) : void
	{
		$this->channel->write(chr(ITCProtocol::PACKET_RAW_FILTER) . $regex);
	}
}
