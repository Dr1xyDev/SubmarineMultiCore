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
use pocketmine\network\mcpe\protocol\types\ScoreboardIdentityPacketEntry;

use function count;

class SetScoreboardIdentityPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SET_SCOREBOARD_IDENTITY_PACKET;

	public const TYPE_REGISTER_IDENTITY = 0;
	public const TYPE_CLEAR_IDENTITY = 1;

	/** @var int */
	public $type;
	/** @var ScoreboardIdentityPacketEntry[] */
	public $entries = [];

	protected function decodePayload() : void
	{
		$this->type = $this->getByte();
		for ($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i) {
			$entry = new ScoreboardIdentityPacketEntry();
			$entry->scoreboardId = $this->getVarLong();
			if ($this->type === self::TYPE_REGISTER_IDENTITY) {
				$entry->entityUniqueId = $this->getEntityUniqueId();
			}

			$this->entries[] = $entry;
		}
	}

	protected function encodePayload() : void
	{
		$this->putByte($this->type);
		$this->putUnsignedVarInt(count($this->entries));
		foreach ($this->entries as $entry) {
			$this->putVarLong($entry->scoreboardId);
			if ($this->type === self::TYPE_REGISTER_IDENTITY) {
				$this->putEntityUniqueId($entry->entityUniqueId);
			}
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleSetScoreboardIdentity($this);
	}
}
