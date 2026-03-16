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

use raklib\utils\InternetAddress;

class OpenConnectionReply2 extends OfflineMessage
{
	public static $ID = MessageIdentifiers::ID_OPEN_CONNECTION_REPLY_2;

	public int $serverID;
	public InternetAddress $clientAddress;
	public int $mtuSize;
	public bool $serverSecurity = false;

	public static function create(int $serverId, InternetAddress $clientAddress, int $mtuSize, bool $serverSecurity) : self
	{
		$result = new self();
		$result->serverID = $serverId;
		$result->clientAddress = $clientAddress;
		$result->mtuSize = $mtuSize;
		$result->serverSecurity = $serverSecurity;
		return $result;
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$this->writeMagic($out);
		$out->putLong($this->serverID);
		$out->putAddress($this->clientAddress);
		$out->putShort($this->mtuSize);
		$out->putByte($this->serverSecurity ? 1 : 0);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->readMagic($in);
		$this->serverID = $in->getLong();
		$this->clientAddress = $in->getAddress();
		$this->mtuSize = $in->getShort();
		$this->serverSecurity = $in->getByte() !== 0;
	}
}
