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

class ConnectionRequestAccepted extends ConnectedPacket
{
	public static $ID = MessageIdentifiers::ID_CONNECTION_REQUEST_ACCEPTED;

	public InternetAddress $address;
	/** @var InternetAddress[] */
	public array $systemAddresses = [];
	public int $sendPingTime;
	public int $sendPongTime;

	/**
	 * @param InternetAddress[] $systemAddresses
	 */
	public static function create(InternetAddress $clientAddress, array $systemAddresses, int $sendPingTime, int $sendPongTime) : self
	{
		$result = new self();
		$result->address = $clientAddress;
		$result->systemAddresses = $systemAddresses;
		$result->sendPingTime = $sendPingTime;
		$result->sendPongTime = $sendPongTime;
		return $result;
	}

	public function __construct()
	{
		$this->systemAddresses[] = new InternetAddress("127.0.0.1", 0, 4);
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putAddress($this->address);
		$out->putShort(0);

		$dummy = new InternetAddress("0.0.0.0", 0, 4);
		for ($i = 0; $i < RakLib::$SYSTEM_ADDRESS_COUNT; ++$i) {
			$out->putAddress($this->systemAddresses[$i] ?? $dummy);
		}

		$out->putLong($this->sendPingTime);
		$out->putLong($this->sendPongTime);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->address = $in->getAddress();
		$in->getShort(); //TODO: check this

		$len = strlen($in->getBuffer());
		$dummy = new InternetAddress("0.0.0.0", 0, 4);

		for ($i = 0; $i < RakLib::$SYSTEM_ADDRESS_COUNT; ++$i) {
			$this->systemAddresses[$i] = $in->getOffset() + 16 < $len ? $in->getAddress() : $dummy; //HACK: avoids trying to read too many addresses on bad data
		}

		$this->sendPingTime = $in->getLong();
		$this->sendPongTime = $in->getLong();
	}
}
