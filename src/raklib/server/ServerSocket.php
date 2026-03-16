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

namespace raklib\server;

use raklib\generic\Socket;
use raklib\generic\SocketException;
use raklib\utils\InternetAddress;

use function socket_bind;
use function socket_last_error;
use function socket_recvfrom;
use function socket_sendto;
use function socket_set_option;
use function socket_strerror;
use function strlen;
use function trim;

use const SO_BROADCAST;
use const SOCKET_EADDRINUSE;
use const SOCKET_EWOULDBLOCK;
use const SOL_SOCKET;

class ServerSocket extends Socket
{
	public function __construct(
		private InternetAddress $bindAddress
	) {
		parent::__construct($this->bindAddress->getVersion() === 6);

		if (@socket_bind($this->socket, $this->bindAddress->getIp(), $this->bindAddress->getPort()) === true) {
			$this->setSendBuffer(1024 * 1024 * 8)->setRecvBuffer(1024 * 1024 * 8);
		} else {
			$error = socket_last_error($this->socket);
			if ($error === SOCKET_EADDRINUSE) { //platform error messages aren't consistent
				throw new SocketException("Failed to bind socket: Something else is already running on $this->bindAddress", $error);
			}
			throw new SocketException("Failed to bind to " . $this->bindAddress . ": " . trim(socket_strerror($error)), $error);
		}
	}

	public function getBindAddress() : InternetAddress
	{
		return $this->bindAddress;
	}

	public function enableBroadcast() : bool
	{
		return socket_set_option($this->socket, SOL_SOCKET, SO_BROADCAST, 1);
	}

	public function disableBroadcast() : bool
	{
		return socket_set_option($this->socket, SOL_SOCKET, SO_BROADCAST, 0);
	}

	/**
	 * @param string $source reference parameter
	 * @param int    $port   reference parameter
	 *
	 * @throws SocketException
	 */
	public function readPacket(?string &$source, ?int &$port) : ?string
	{
		$buffer = "";
		if (@socket_recvfrom($this->socket, $buffer, 65535, 0, $source, $port) === false) {
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
	public function writePacket(string $buffer, string $dest, int $port) : int
	{
		$result = @socket_sendto($this->socket, $buffer, strlen($buffer), 0, $dest, $port);
		if ($result === false) {
			$errno = socket_last_error($this->socket);
			throw new SocketException("Failed to send to $dest $port (errno $errno): " . trim(socket_strerror($errno)), $errno);
		}
		return $result;
	}
}
