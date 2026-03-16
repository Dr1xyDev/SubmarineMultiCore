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

namespace raklib\client;

use raklib\generic\Socket;
use raklib\generic\SocketException;
use raklib\utils\InternetAddress;

use function socket_connect;
use function socket_last_error;
use function socket_recv;
use function socket_send;
use function socket_strerror;
use function strlen;
use function trim;

use const SOCKET_EWOULDBLOCK;

class ClientSocket extends Socket
{
	public function __construct(
		private InternetAddress $connectAddress
	) {
		parent::__construct($this->connectAddress->getVersion() === 6);

		if (!@socket_connect($this->socket, $this->connectAddress->getIp(), $this->connectAddress->getPort())) {
			$error = socket_last_error($this->socket);
			throw new SocketException("Failed to connect to " . $this->connectAddress . ": " . trim(socket_strerror($error)), $error);
		}
		//TODO: is an 8 MB buffer really appropriate for a client??
		$this->setSendBuffer(1024 * 1024 * 8)->setRecvBuffer(1024 * 1024 * 8);
	}

	public function getConnectAddress() : InternetAddress
	{
		return $this->connectAddress;
	}

	/**
	 * @throws SocketException
	 */
	public function readPacket() : ?string
	{
		$buffer = "";
		if (@socket_recv($this->socket, $buffer, 65535, 0) === false) {
			$errno = socket_last_error($this->socket);
			if ($errno === SOCKET_EWOULDBLOCK) {
				return null;
			}
			throw new SocketException("Failed to recv (errno $errno): " . trim(socket_strerror($errno)), $errno);
		}
		return $buffer;
	}

	/**
	 * @throws SocketException
	 */
	public function writePacket(string $buffer) : int
	{
		$result = @socket_send($this->socket, $buffer, strlen($buffer), 0);
		if ($result === false) {
			$errno = socket_last_error($this->socket);
			throw new SocketException("Failed to send packet (errno $errno): " . trim(socket_strerror($errno)), $errno);
		}
		return $result;
	}
}
