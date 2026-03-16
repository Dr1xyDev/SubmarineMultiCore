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

use raklib\generic\Session;
use raklib\protocol\ConnectionRequest;
use raklib\protocol\ConnectionRequestAccepted;
use raklib\protocol\MessageIdentifiers;
use raklib\protocol\NewIncomingConnection;
use raklib\protocol\Packet;
use raklib\protocol\PacketReliability;
use raklib\protocol\PacketSerializer;
use raklib\RakLib;
use raklib\utils\InternetAddress;

use function ord;

class ServerSession extends Session
{
	public const DEFAULT_MAX_SPLIT_PART_COUNT = 256;
	public const DEFAULT_MAX_CONCURRENT_SPLIT_COUNT = 4;

	private Server $server;
	private int $internalId;

	public function __construct(
		Server $server,
		\Logger $logger,
		InternetAddress $address,
		int $clientId,
		int $mtuSize,
		int $internalId,
		int $recvMaxSplitParts = self::DEFAULT_MAX_SPLIT_PART_COUNT,
		int $recvMaxConcurrentSplits = self::DEFAULT_MAX_CONCURRENT_SPLIT_COUNT,
		int $protocol = RakLib::DEFAULT_PROTOCOL_VERSION
	) {
		$this->server = $server;
		$this->internalId = $internalId;
		parent::__construct($logger, $address, $clientId, $mtuSize, $recvMaxSplitParts, $recvMaxConcurrentSplits, $protocol);
	}

	/**
	 * Returns an ID used to identify this session across threads.
	 */
	public function getInternalId() : int
	{
		return $this->internalId;
	}

	final protected function sendPacket(Packet $packet) : void
	{
		$this->server->sendPacket($packet, $this->address);
	}

	protected function onPacketAck(int $identifierACK) : void
	{
		$this->server->getEventListener()->onPacketAck($this->internalId, $identifierACK);
	}

	protected function onDisconnect(int $reason) : void
	{
		$this->server->getEventListener()->onClientDisconnect($this->internalId, $reason);
	}

	final protected function handleRakNetConnectionPacket(string $packet) : void
	{
		$id = ord($packet[0]);
		if ($id === MessageIdentifiers::ID_CONNECTION_REQUEST) {
			$dataPacket = new ConnectionRequest();
			$dataPacket->decode(new PacketSerializer($packet));
			$this->queueConnectedPacket(ConnectionRequestAccepted::create(
				$this->address,
				[],
				$dataPacket->sendPingTime,
				$this->getRakNetTimeMS()
			), PacketReliability::UNRELIABLE, 0, true);
		} elseif ($id === MessageIdentifiers::ID_NEW_INCOMING_CONNECTION) {
			$dataPacket = new NewIncomingConnection();
			$dataPacket->decode(new PacketSerializer($packet));

			if ($dataPacket->address->getPort() === $this->server->getPort() || !$this->server->portChecking) {
				$this->state = self::STATE_CONNECTED; //FINALLY!
				$this->server->openSession($this);

				//$this->handlePong($dataPacket->sendPingTime, $dataPacket->sendPongTime); //can't use this due to system-address count issues in MCPE >.<
				$this->sendPing();
			}
		}
	}

	protected function onPacketReceive(string $packet) : void
	{
		$this->server->getEventListener()->onPacketReceive($this->internalId, $packet);
	}

	protected function onPingMeasure(int $pingMS) : void
	{
		$this->server->getEventListener()->onPingMeasure($this->internalId, $pingMS);
	}
}
