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

use raklib\RakLib;
use raklib\utils\InternetAddress;

use function strlen;

class NewIncomingConnection extends ConnectedPacket
{
	public static $ID = MessageIdentifiers::ID_NEW_INCOMING_CONNECTION;

	public InternetAddress $address;
	/** @var InternetAddress[] */
	public array $systemAddresses = [];
	public int $sendPingTime;
	public int $sendPongTime;

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putAddress($this->address);
		foreach ($this->systemAddresses as $address) {
			$out->putAddress($address);
		}
		$out->putLong($this->sendPingTime);
		$out->putLong($this->sendPongTime);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->address = $in->getAddress();

		//TODO: HACK!
		$stopOffset = strlen($in->getBuffer()) - 16; //buffer length - sizeof(sendPingTime) - sizeof(sendPongTime)
		$dummy = new InternetAddress("0.0.0.0", 0, 4);
		for ($i = 0; $i < RakLib::$SYSTEM_ADDRESS_COUNT; ++$i) {
			if ($in->getOffset() >= $stopOffset) {
				$this->systemAddresses[$i] = clone $dummy;
			} else {
				$this->systemAddresses[$i] = $in->getAddress();
			}
		}

		$this->sendPingTime = $in->getLong();
		$this->sendPongTime = $in->getLong();
	}
}
