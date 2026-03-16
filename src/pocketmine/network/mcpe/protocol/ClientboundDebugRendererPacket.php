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
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\NetworkSession;

class ClientboundDebugRendererPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_DEBUG_RENDERER_PACKET;

	public const int TYPE_CLEAR = 1;
	public const int TYPE_ADD_CUBE = 2;

	public int $type;

	//TODO: if more types are added, we'll probably want to make a separate data type and interfaces
	public string $text;
	public Vector3 $position;
	public float $red;
	public float $green;
	public float $blue;
	public float $alpha;
	public int $durationMillis;

	private static function base(int $type) : self
	{
		$result = new self();
		$result->type = $type;
		return $result;
	}

	public static function clear() : self
	{
		return self::base(self::TYPE_CLEAR);
	}

	public static function addCube(string $text, Vector3 $position, float $red, float $green, float $blue, float $alpha, int $durationMillis) : self
	{
		$result = self::base(self::TYPE_ADD_CUBE);
		$result->text = $text;
		$result->position = $position;
		$result->red = $red;
		$result->green = $green;
		$result->blue = $blue;
		$result->alpha = $alpha;
		$result->durationMillis = $durationMillis;
		return $result;
	}

	public function getType() : int
	{
		return $this->type;
	}

	public function getText() : string
	{
		return $this->text;
	}

	public function getPosition() : Vector3
	{
		return $this->position;
	}

	public function getRed() : float
	{
		return $this->red;
	}

	public function getGreen() : float
	{
		return $this->green;
	}

	public function getBlue() : float
	{
		return $this->blue;
	}

	public function getAlpha() : float
	{
		return $this->alpha;
	}

	public function getDurationMillis() : int
	{
		return $this->durationMillis;
	}

	protected function decodePayload() : void
	{
		$this->type = $this->getLInt();

		switch ($this->type) {
			case self::TYPE_CLEAR:
				//NOOP
				break;
			case self::TYPE_ADD_CUBE:
				$this->text = $this->getString();
				$this->position = $this->getVector3();
				$this->red = $this->getLFloat();
				$this->green = $this->getLFloat();
				$this->blue = $this->getLFloat();
				$this->alpha = $this->getLFloat();
				$this->durationMillis = $this->getLLong();
				break;
			default:
				throw new PacketDecodeException("Unknown type " . $this->type);
		}
	}

	protected function encodePayload() : void
	{
		$this->putLInt($this->type);

		switch ($this->type) {
			case self::TYPE_CLEAR:
				//NOOP
				break;
			case self::TYPE_ADD_CUBE:
				$this->putString($this->text);
				$this->putVector3($this->position);
				$this->putLFloat($this->red);
				$this->putLFloat($this->green);
				$this->putLFloat($this->blue);
				$this->putLFloat($this->alpha);
				$this->putLLong($this->durationMillis);
				break;
			default:
				throw new InvalidArgumentException("Unknown type " . $this->type);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleClientboundDebugRenderer($this);
	}
}
