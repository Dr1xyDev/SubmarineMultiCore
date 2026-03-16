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

use function socket_close;
use function socket_create;
use function socket_last_error;
use function socket_set_block;
use function socket_set_nonblock;
use function socket_set_option;
use function socket_strerror;
use function trim;

use const AF_INET;
use const AF_INET6;
use const IPPROTO_IPV6;
use const IPV6_V6ONLY;
use const SO_RCVBUF;
use const SO_RCVTIMEO;
use const SO_SNDBUF;
use const SOCK_DGRAM;
use const SOL_SOCKET;
use const SOL_UDP;

abstract class Socket
{
	protected \Socket $socket;

	/**
	 * @throws SocketException
	 */
	protected function __construct(bool $ipv6)
	{
		$socket = @socket_create($ipv6 ? AF_INET6 : AF_INET, SOCK_DGRAM, SOL_UDP);
		if ($socket === false) {
			throw new \RuntimeException("Failed to create socket: " . trim(socket_strerror(socket_last_error())));
		}
		$this->socket = $socket;

		if ($ipv6) {
			socket_set_option($this->socket, IPPROTO_IPV6, IPV6_V6ONLY, 1); //Don't map IPv4 to IPv6, the implementation can create another RakLib instance to handle IPv4
		}
	}

	public function getSocket() : \Socket
	{
		return $this->socket;
	}

	public function close() : void
	{
		socket_close($this->socket);
	}

	public function getLastError() : int
	{
		return socket_last_error($this->socket);
	}

	/**
	 * @return $this
	 */
	public function setSendBuffer(int $size)
	{
		@socket_set_option($this->socket, SOL_SOCKET, SO_SNDBUF, $size);

		return $this;
	}

	/**
	 * @return $this
	 */
	public function setRecvBuffer(int $size)
	{
		@socket_set_option($this->socket, SOL_SOCKET, SO_RCVBUF, $size);

		return $this;
	}

	/**
	 * @return $this
	 */
	public function setRecvTimeout(int $seconds, int $microseconds = 0)
	{
		@socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, ["sec" => $seconds, "usec" => $microseconds]);

		return $this;
	}

	public function setBlocking(bool $blocking) : void
	{
		if ($blocking) {
			socket_set_block($this->socket);
		} else {
			socket_set_nonblock($this->socket);
		}
	}
}
