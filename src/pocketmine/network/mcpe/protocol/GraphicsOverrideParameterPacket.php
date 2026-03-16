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
use pocketmine\network\mcpe\protocol\types\GraphicsOverrideParameterType;
use pocketmine\network\mcpe\protocol\types\ParameterKeyframeValue;
use function count;

class GraphicsOverrideParameterPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::GRAPHICS_OVERRIDE_PARAMETER_PACKET;

	/** @var ParameterKeyframeValue[] */
	private array $values = [];
	private string $biomeIdentifier;
	private GraphicsOverrideParameterType $parameterType;
	private bool $reset;

	/**
	 * @generate-create-func
	 * @param ParameterKeyframeValue[] $values
	 */
	public static function create(array $values, string $biomeIdentifier, GraphicsOverrideParameterType $parameterType, bool $reset) : self{
		$result = new self();
		$result->values = $values;
		$result->biomeIdentifier = $biomeIdentifier;
		$result->parameterType = $parameterType;
		$result->reset = $reset;
		return $result;
	}

	/**
	 * @return ParameterKeyframeValue[]
	 */
	public function getValues() : array{ return $this->values; }

	public function getBiomeIdentifier() : string{ return $this->biomeIdentifier; }

	public function getParameterType() : GraphicsOverrideParameterType{ return $this->parameterType; }

	public function isReset() : bool{ return $this->reset; }

	protected function decodePayload() : void{
		for($i = 0; $i < $this->getUnsignedVarInt(); ++$i){
			$this->values[] = ParameterKeyframeValue::read($this);
		}
		$this->biomeIdentifier = $this->getString();
		$this->parameterType = GraphicsOverrideParameterType::fromPacket($this->getByte());
		$this->reset = $this->getBool();
	}

	protected function encodePayload() : void{
		$this->putUnsignedVarInt(count($this->values));
		foreach($this->values as $value){
			$value->write($this);
		}
		$this->putString($this->biomeIdentifier);
		$this->putByte($this->parameterType->value);
		$this->putBool($this->reset);
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleGraphicsOverrideParameter($this);
	}
}
