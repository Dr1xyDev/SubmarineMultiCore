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

namespace raklib\utils;

final class InternetAddress
{
	public function __construct(
		private string $ip,
		private int $port,
		private int $version
	) {
		if ($port < 0 || $port > 65535) {
			throw new \InvalidArgumentException("Invalid port range");
		}
	}

	public function getIp() : string
	{
		return $this->ip;
	}

	public function getPort() : int
	{
		return $this->port;
	}

	public function getVersion() : int
	{
		return $this->version;
	}

	public function __toString()
	{
		return $this->ip . " " . $this->port;
	}

	public function toString() : string
	{
		return $this->__toString();
	}

	public function equals(InternetAddress $address) : bool
	{
		return $this->ip === $address->ip && $this->port === $address->port && $this->version === $address->version;
	}
}
