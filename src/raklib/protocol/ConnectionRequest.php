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

class ConnectionRequest extends ConnectedPacket
{
	public static $ID = MessageIdentifiers::ID_CONNECTION_REQUEST;

	public int $clientID;
	public int $sendPingTime;
	public bool $useSecurity = false;

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putLong($this->clientID);
		$out->putLong($this->sendPingTime);
		$out->putByte($this->useSecurity ? 1 : 0);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->clientID = $in->getLong();
		$this->sendPingTime = $in->getLong();
		$this->useSecurity = $in->getByte() !== 0;
	}
}
