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

namespace raklib\protocol;

class UnconnectedPong extends OfflineMessage
{
	public static $ID = MessageIdentifiers::ID_UNCONNECTED_PONG;

	public int $sendPingTime;
	public int $serverId;
	public string $serverName;

	public static function create(int $sendPingTime, int $serverId, string $serverName) : self
	{
		$result = new self();
		$result->sendPingTime = $sendPingTime;
		$result->serverId = $serverId;
		$result->serverName = $serverName;
		return $result;
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putLong($this->sendPingTime);
		$out->putLong($this->serverId);
		$this->writeMagic($out);
		$out->putString($this->serverName);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->sendPingTime = $in->getLong();
		$this->serverId = $in->getLong();
		$this->readMagic($in);
		$this->serverName = $in->getString();
	}
}
