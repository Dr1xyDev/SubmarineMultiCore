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

use InvalidArgumentException;
use pocketmine\network\mcpe\NetworkSession;

class BookEditPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::BOOK_EDIT_PACKET;

	public const TYPE_REPLACE_PAGE = 0;
	public const TYPE_ADD_PAGE = 1;
	public const TYPE_DELETE_PAGE = 2;
	public const TYPE_SWAP_PAGES = 3;
	public const TYPE_SIGN_BOOK = 4;

	/** @var int */
	public $type;
	/** @var int */
	public $inventorySlot;
	/** @var int */
	public $pageNumber;
	/** @var int */
	public $secondaryPageNumber;

	/** @var string */
	public $text;
	/** @var string */
	public $photoName;

	/** @var string */
	public $title;
	/** @var string */
	public $author;
	/** @var string */
	public $xuid;

	protected function decodePayload() : void
	{
		$this->type = $this->getByte();
		$this->inventorySlot = $this->getByte();

		switch ($this->type) {
			case self::TYPE_REPLACE_PAGE:
			case self::TYPE_ADD_PAGE:
				$this->pageNumber = $this->getByte();
				$this->text = $this->getString();
				$this->photoName = $this->getString();
				break;
			case self::TYPE_DELETE_PAGE:
				$this->pageNumber = $this->getByte();
				break;
			case self::TYPE_SWAP_PAGES:
				$this->pageNumber = $this->getByte();
				$this->secondaryPageNumber = $this->getByte();
				break;
			case self::TYPE_SIGN_BOOK:
				$this->title = $this->getString();
				$this->author = $this->getString();
				if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_223) {
					$this->xuid = $this->getString();
				}
				break;
			default:
				throw new PacketDecodeException("Unknown book edit type $this->type!");
		}
	}

	protected function encodePayload() : void
	{
		$this->putByte($this->type);
		$this->putByte($this->inventorySlot);

		switch ($this->type) {
			case self::TYPE_REPLACE_PAGE:
			case self::TYPE_ADD_PAGE:
				$this->putByte($this->pageNumber);
				$this->putString($this->text);
				$this->putString($this->photoName);
				break;
			case self::TYPE_DELETE_PAGE:
				$this->putByte($this->pageNumber);
				break;
			case self::TYPE_SWAP_PAGES:
				$this->putByte($this->pageNumber);
				$this->putByte($this->secondaryPageNumber);
				break;
			case self::TYPE_SIGN_BOOK:
				$this->putString($this->title);
				$this->putString($this->author);
				if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_223) {
					$this->putString($this->xuid);
				}
				break;
			default:
				throw new InvalidArgumentException("Unknown book edit type $this->type!");
		}
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleBookEdit($this);
	}
}
