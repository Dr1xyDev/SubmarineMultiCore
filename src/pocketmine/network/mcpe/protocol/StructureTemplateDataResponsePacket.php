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

class StructureTemplateDataResponsePacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::STRUCTURE_TEMPLATE_DATA_RESPONSE_PACKET;

	/** @var string */
	public $structureTemplateName;
	/** @var string|null */
	public $namedtag;

	protected function decodePayload() : void
	{
		$this->structureTemplateName = $this->getString();
		if ($this->getBool()) {
			$this->namedtag = $this->getRemaining();
		}
	}

	protected function encodePayload() : void
	{
		$this->putString($this->structureTemplateName);
		$this->putBool($this->namedtag !== null);
		if ($this->namedtag !== null) {
			$this->put($this->namedtag);
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleStructureTemplateDataResponse($this);
	}
}
