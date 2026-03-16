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

use function in_array;

final class SimpleProtocolAcceptor implements ProtocolAcceptor
{
	public function __construct(
		private array $protocolVersions
	) {
	}

	public function accepts(int $protocolVersion) : bool
	{
		return in_array($protocolVersion, $this->protocolVersions, true);
	}

	public function getPrimaryVersions() : array
	{
		return $this->protocolVersions;
	}
}
