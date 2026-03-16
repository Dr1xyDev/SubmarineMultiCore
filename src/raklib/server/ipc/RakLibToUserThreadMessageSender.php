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

use function chr;
use function inet_pton;
use function strlen;

final class RakLibToUserThreadMessageSender implements ServerEventListener
{
	public function __construct(
		private InterThreadChannelWriter $channel
	) {
	}

	public function onClientConnect(int $sessionId, string $address, int $port, int $clientID, int $protocolVersion) : void
	{
		$rawAddr = inet_pton($address);
		if ($rawAddr === false) {
			throw new \InvalidArgumentException("Invalid IP address");
		}
		$this->channel->write(
			chr(ITCProtocol::PACKET_OPEN_SESSION) .
			Binary::writeInt($sessionId) .
			chr(strlen($rawAddr)) . $rawAddr .
			Binary::writeShort($port) .
			chr($protocolVersion) .
			Binary::writeLong($clientID)
		);
	}

	public function onClientDisconnect(int $sessionId, int $reason) : void
	{
		$this->channel->write(
			chr(ITCProtocol::PACKET_CLOSE_SESSION) .
			Binary::writeInt($sessionId) .
			chr($reason)
		);
	}

	public function onPacketReceive(int $sessionId, string $packet) : void
	{
		$this->channel->write(
			chr(ITCProtocol::PACKET_ENCAPSULATED) .
			Binary::writeInt($sessionId) .
			$packet
		);
	}

	public function onRawPacketReceive(string $address, int $port, string $payload) : void
	{
		$this->channel->write(
			chr(ITCProtocol::PACKET_RAW) .
			chr(strlen($address)) . $address .
			Binary::writeShort($port) .
			$payload
		);
	}

	public function onPacketAck(int $sessionId, int $identifierACK) : void
	{
		$this->channel->write(
			chr(ITCProtocol::PACKET_ACK_NOTIFICATION) .
			Binary::writeInt($sessionId) .
			Binary::writeInt($identifierACK)
		);
	}

	public function onBandwidthStatsUpdate(int $bytesSentDiff, int $bytesReceivedDiff) : void
	{
		$this->channel->write(
			chr(ITCProtocol::PACKET_REPORT_BANDWIDTH_STATS) .
			Binary::writeLong($bytesSentDiff) .
			Binary::writeLong($bytesReceivedDiff)
		);
	}

	public function onPingMeasure(int $sessionId, int $pingMS) : void
	{
		$this->channel->write(
			chr(ITCProtocol::PACKET_REPORT_PING) .
			Binary::writeInt($sessionId) .
			Binary::writeInt($pingMS)
		);
	}
}
