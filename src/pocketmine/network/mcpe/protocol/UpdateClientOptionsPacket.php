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

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\GraphicsMode;

class UpdateClientOptionsPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::UPDATE_CLIENT_OPTIONS_PACKET;

	private ?GraphicsMode $graphicsMode;

	/**
	 * @generate-create-func
	 */
	public static function create(?GraphicsMode $graphicsMode) : self
	{
		$result = new self();
		$result->graphicsMode = $graphicsMode;
		return $result;
	}

	public function getGraphicsMode() : ?GraphicsMode
	{
		return $this->graphicsMode;
	}

	protected function decodePayload() : void
	{
		$this->graphicsMode = $this->readOptional(fn () => GraphicsMode::fromPacket($this->getByte()));
	}

	protected function encodePayload() : void
	{
		$this->writeOptional($this->graphicsMode, fn (GraphicsMode $v) => $this->putByte($v->value));
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleUpdateClientOptions($this);
	}
}
