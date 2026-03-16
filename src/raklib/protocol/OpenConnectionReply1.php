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

class OpenConnectionReply1 extends OfflineMessage
{
	public static $ID = MessageIdentifiers::ID_OPEN_CONNECTION_REPLY_1;

	public int $serverID;
	public bool $serverSecurity = false;
	public int $mtuSize;

	public static function create(int $serverId, bool $serverSecurity, int $mtuSize) : self
	{
		$result = new self();
		$result->serverID = $serverId;
		$result->serverSecurity = $serverSecurity;
		$result->mtuSize = $mtuSize;
		return $result;
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$this->writeMagic($out);
		$out->putLong($this->serverID);
		$out->putByte($this->serverSecurity ? 1 : 0);
		$out->putShort($this->mtuSize);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->readMagic($in);
		$this->serverID = $in->getLong();
		$this->serverSecurity = $in->getByte() !== 0;
		$this->mtuSize = $in->getShort();
	}
}
