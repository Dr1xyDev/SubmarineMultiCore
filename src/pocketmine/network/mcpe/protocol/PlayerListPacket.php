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

use pocketmine\entity\Skin;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\protocol\types\skin\SerializedSkin;
use pocketmine\utils\Color;

use function count;
use function is_array;
use function json_decode;
use function json_encode;
use function strtolower;

class PlayerListPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::PLAYER_LIST_PACKET;

	public const TYPE_ADD = 0;
	public const TYPE_REMOVE = 1;

	/** @var PlayerListEntry[] */
	public $entries = [];
	/** @var int */
	public $type;

	public static function getPESkinId(Skin $skin) : string
	{
		$skinId = $skin->getSkinId();

		if (SerializedSkin::isSkinIdPE($skinId)) {
			return $skinId;
		}

		$type = match ($skin->getGeometryName()) {
			"geometry.humanoid.customSlim" => "CustomSlim",
			default => "Custom",
		};

		return "Standard_" . $type;
	}

	protected function decodePayload() : void
	{
		$this->type = $this->getByte();
		$count = $this->getUnsignedVarInt();
		for ($i = 0; $i < $count; ++$i) {
			$entry = new PlayerListEntry();

			if ($this->type === self::TYPE_ADD) {
				$entry->uuid = $this->getUUID();
				$entry->entityUniqueId = $this->getEntityUniqueId();
				$entry->username = $this->getString();
				if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_137) {
					if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_223 && $this->getProtocol() < ProtocolInfo::PROTOCOL_291) {
						$entry->thirdPartyName = $this->getString();
						$entry->platform = $this->getVarInt();
					}

					if ($this->getProtocol() < ProtocolInfo::PROTOCOL_370) {
						$skinId = $this->getString();
						$skinData = $this->getString();
						$capeData = $this->getString();
						$geometryName = $this->getString();
						$geometryData = $this->getString();

						$entry->skin = new Skin(
							$skinId,
							$skinData,
							$capeData,
							$geometryName,
							$geometryData
						);
					}
					$entry->xboxUserId = $this->getString();
					if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_223) {
						$entry->platformChatId = $this->getString();
						if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_370) {
							if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_388) {
								$entry->buildPlatform = $this->getLInt();
							}
							$entry->skin = $this->getSkin($this->getProtocol());
							if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_388) {
								$entry->isTeacher = $this->getBool();
								$entry->isHost = $this->getBool();
								if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_649) {
									$entry->isSubClient = $this->getBool();
									if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_800) {
										$entry->color = Color::fromARGB($this->getLInt());
									}
								}
							}
						}
					}
				} else {
					$skinId = $this->getString();
					$skinData = $this->getString();

					$entry->skin = new Skin(
						$skinId,
						$skinData
					);
				}
			} else {
				$entry->uuid = $this->getUUID();
			}

			$this->entries[$i] = $entry;
		}
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_390) {
			if ($this->type === self::TYPE_ADD) {
				for ($i = 0; $i < $count; ++$i) {
					$this->getBool();
				}
			}
		}
	}

	protected function encodePayload() : void
	{
		$this->putByte($this->type);
		$this->putUnsignedVarInt(count($this->entries));
		foreach ($this->entries as $entry) {
			if ($this->type === self::TYPE_ADD) {
				$this->putUUID($entry->uuid);
				$this->putEntityUniqueId($entry->entityUniqueId);
				$this->putString($entry->username);
				if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_137) {
					if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_223 && $this->getProtocol() < ProtocolInfo::PROTOCOL_291) {
						$this->putString($entry->thirdPartyName);
						$this->putVarInt($entry->platform);
					}
					if ($this->getProtocol() < ProtocolInfo::PROTOCOL_370) {
						$this->putString($entry->skin->getSkinId());
						$this->putString($entry->skin->getClientFriendlySkinData($this->getProtocol()));
						$this->putString($entry->skin->getCapeData());

						$skinGeometryName = $entry->skin->getGeometryName();
						$skinGeometryData = $entry->skin->getGeometryData();
						if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_313) {
							$skinGeometryName = strtolower($skinGeometryName);
							$tempData = json_decode($skinGeometryData, true);
							if (is_array($tempData)) {
								foreach ($tempData as $key => $value) {
									unset($tempData[$key]);
									$tempData[strtolower($key)] = $value;
								}

								$skinGeometryData = json_encode($tempData);
							}
						}
						$this->putString($skinGeometryName);
						$this->putString($this->prepareGeometryDataForOld($skinGeometryData));
					}

					$this->putString($entry->xboxUserId);
					if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_223) {
						$this->putString($entry->platformChatId);
						if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_370) {
							if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_388) {
								$this->putLInt($entry->buildPlatform);
							}
							$this->putSkin($entry->skin, $this->getProtocol());
							if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_388) {
								$this->putBool($entry->isTeacher);
								$this->putBool($entry->isHost);
								if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_649) {
									$this->putBool($entry->isSubClient);
									if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_800) {
										$this->putLInt(($entry->color ?? new Color(255, 255, 255))->toARGB());
									}
								}
							}
						}
					}
				} else {
					$this->putString(self::getPESkinId($entry->skin));
					$this->putString($entry->skin->getClientFriendlySkinData($this->getProtocol()));
				}
			} else {
				$this->putUUID($entry->uuid);
			}
		}
		if ($this->getProtocol() >= ProtocolInfo::PROTOCOL_390) {
			if ($this->type === self::TYPE_ADD) {
				foreach ($this->entries as $entry) {
					$this->putBool($entry->skin->getSerializedSkin()->isTrustedSkin());
				}
			}
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handlePlayerList($this);
	}
}
