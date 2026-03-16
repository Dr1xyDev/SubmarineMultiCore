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

class IncompatibleProtocolVersion extends OfflineMessage
{
	public static $ID = MessageIdentifiers::ID_INCOMPATIBLE_PROTOCOL_VERSION;

	public int $protocolVersion;
	public int $serverId;

	public static function create(int $protocolVersion, int $serverId) : self
	{
		$result = new self();
		$result->protocolVersion = $protocolVersion;
		$result->serverId = $serverId;
		return $result;
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putByte($this->protocolVersion);
		$this->writeMagic($out);
		$out->putLong($this->serverId);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->protocolVersion = $in->getByte();
		$this->readMagic($in);
		$this->serverId = $in->getLong();
	}
}
