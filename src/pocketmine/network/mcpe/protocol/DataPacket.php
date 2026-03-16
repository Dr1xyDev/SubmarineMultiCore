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

namespace pocketmine\network\mcpe\protocol;

use Error;
use InvalidArgumentException;
use OutOfBoundsException;
use pocketmine\network\mcpe\convert\PacketIdTranslator;
use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\utils\Utils;
use ReflectionClass;
use UnexpectedValueException;

use function bin2hex;
use function get_class;
use function is_object;
use function is_string;
use function method_exists;

abstract class DataPacket extends NetworkBinaryStream
{
	public const NETWORK_ID = 0x0;

	public const PID_MASK = 0x3ff; //10 bits

	private const SUBCLIENT_ID_MASK = 0x03; //2 bits
	private const SENDER_SUBCLIENT_ID_SHIFT = 10;
	private const RECIPIENT_SUBCLIENT_ID_SHIFT = 12;

	public bool $isEncoded = false;
	public bool $wasDecoded = false;

	public int $senderSubId = 0;
	public int $recipientSubId = 0;
	private ?int $packetProtocol = null;

	public function pid() : int
	{
		return $this::NETWORK_ID;
	}

	public function getName() : string
	{
		return (new ReflectionClass($this))->getShortName();
	}

	public function setProtocol(int $protocol) : void
	{
		$this->packetProtocol = $protocol;
	}

	public function getProtocol() : int
	{
		return $this->packetProtocol;
	}

	public function checkProtocol() : void
	{
		if ($this->packetProtocol === null) {
			throw new InvalidArgumentException('Protocol has not passed. Please use $packet->setProtocol(int $protocol)->... for fix it.');
		}
	}

	public function canBeSentBeforeLogin() : bool
	{
		return false;
	}

	/**
	 * Returns whether the packet may legally have unread bytes left in the buffer.
	 */
	public function mayHaveUnreadBytes() : bool
	{
		return false;
	}

	public function mustBeDecoded() : bool
	{
		return true;
	}

	/**
	 * @throws OutOfBoundsException
	 * @throws UnexpectedValueException
	 */
	public function decode() : void
	{
		$this->rewind();
		$this->checkProtocol();
		$this->decodeHeader();
		$this->decodePayload();
		$this->wasDecoded = true;
	}

	/**
	 * @throws OutOfBoundsException
	 * @throws UnexpectedValueException
	 */
	protected function decodeHeader() : void
	{
		if ($this->packetProtocol < ProtocolInfo::PROTOCOL_282) {
			$this->getByte();
			if ($this->packetProtocol >= ProtocolInfo::PROTOCOL_137) {
				$this->senderSubId = $this->getByte();
				$this->recipientSubId = $this->getByte();
				if ($this->senderSubId > 0x4 || $this->recipientSubId > 0x4) {
					throw new PacketDecodeException(($this->getName()) . ": Packet decode headers error");
				}
			}
		} else {
			$pid = $this->getUnsignedVarInt();
			$this->senderSubId = ($pid >> self::SENDER_SUBCLIENT_ID_SHIFT) & self::SUBCLIENT_ID_MASK;
			$this->recipientSubId = ($pid >> self::RECIPIENT_SUBCLIENT_ID_SHIFT) & self::SUBCLIENT_ID_MASK;
		}
	}

	/**
	 * Note for plugin developers: If you're adding your own packets, you should perform decoding in here.
	 *
	 * @throws OutOfBoundsException
	 * @throws UnexpectedValueException
	 */
	protected function decodePayload() : void
	{

	}

	public function encode() : void
	{
		$this->reset();
		$this->checkProtocol();
		$this->encodeHeader();
		$this->encodePayload();
		$this->isEncoded = true;
	}

	protected function encodeHeader() : void
	{
		$pid = PacketIdTranslator::getInstance()->toNetworkId($this->packetProtocol, $this->pid());
		if ($this->packetProtocol < ProtocolInfo::PROTOCOL_282) {
			$this->putByte($pid);
			if ($this->packetProtocol >= ProtocolInfo::PROTOCOL_137) {
				$this->putByte($this->senderSubId);
				$this->putByte($this->recipientSubId);
			}
		} else {
			$this->putUnsignedVarInt(
				$pid |
				($this->senderSubId << self::SENDER_SUBCLIENT_ID_SHIFT) |
				($this->recipientSubId << self::RECIPIENT_SUBCLIENT_ID_SHIFT)
			);
		}
	}

	/**
	 * Note for plugin developers: If you're adding your own packets, you should perform encoding in here.
	 */
	protected function encodePayload() : void
	{

	}

	/**
	 * Performs handling for this packet. Usually you'll want an appropriately named method in the NetworkSession for this.
	 *
	 * This method returns a bool to indicate whether the packet was handled or not. If the packet was unhandled, a debug message will be logged with a hexdump of the packet.
	 * Typically this method returns the return value of the handler in the supplied NetworkSession. See other packets for examples how to implement this.
	 *
	 * @return bool true if the packet was handled successfully, false if not.
	 */
	abstract public function handle(NetworkSession $session) : bool;

	public function __debugInfo()
	{
		$data = [];
		foreach ((array) $this as $k => $v) {
			if ($k === "buffer" && is_string($v)) {
				$data[$k] = bin2hex($v);
			} elseif (is_string($v) || (is_object($v) && method_exists($v, "__toString"))) {
				$data[$k] = Utils::printable((string) $v);
			} else {
				$data[$k] = $v;
			}
		}

		return $data;
	}

	public function __get($name)
	{
		throw new Error("Undefined property: " . get_class($this) . "::\$" . $name);
	}

	public function __set($name, $value)
	{
		throw new Error("Undefined property: " . get_class($this) . "::\$" . $name);
	}

}
