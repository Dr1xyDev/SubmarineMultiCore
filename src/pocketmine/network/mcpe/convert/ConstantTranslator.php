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

namespace pocketmine\network\mcpe\convert;

use Closure;
use pocketmine\level\particle\Particle;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags110;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags137;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags223;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags274;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags291;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags354;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags390;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags428;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags475;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags560;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags575;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags594;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags630;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags671;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags776;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags786;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags800;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags818;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\flags\ActorFlags844;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\properties\ActorProperties110;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\properties\ActorProperties223;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\properties\ActorProperties340;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\properties\ActorProperties354;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\properties\ActorProperties361;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\properties\ActorProperties428;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\properties\ActorProperties594;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\properties\ActorProperties712;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\properties\ActorProperties776;
use pocketmine\network\mcpe\convert\constants\actorMetadataList\properties\ActorProperties800;
use pocketmine\network\mcpe\convert\constants\bossBarColor\BossBarColor110;
use pocketmine\network\mcpe\convert\constants\bossBarColor\BossBarColor622;
use pocketmine\network\mcpe\convert\constants\commandArgumentTypeIds\CommandArgumentTypeIds137;
use pocketmine\network\mcpe\convert\constants\commandArgumentTypeIds\CommandArgumentTypeIds274;
use pocketmine\network\mcpe\convert\constants\commandArgumentTypeIds\CommandArgumentTypeIds332;
use pocketmine\network\mcpe\convert\constants\commandArgumentTypeIds\CommandArgumentTypeIds340;
use pocketmine\network\mcpe\convert\constants\commandArgumentTypeIds\CommandArgumentTypeIds370;
use pocketmine\network\mcpe\convert\constants\commandArgumentTypeIds\CommandArgumentTypeIds388;
use pocketmine\network\mcpe\convert\constants\commandArgumentTypeIds\CommandArgumentTypeIds428;
use pocketmine\network\mcpe\convert\constants\commandArgumentTypeIds\CommandArgumentTypeIds503;
use pocketmine\network\mcpe\convert\constants\commandArgumentTypeIds\CommandArgumentTypeIds527;
use pocketmine\network\mcpe\convert\constants\commandArgumentTypeIds\CommandArgumentTypeIds582;
use pocketmine\network\mcpe\convert\constants\commandArgumentTypeIds\CommandArgumentTypeIds662;
use pocketmine\network\mcpe\convert\constants\itemStackRequestActionTypeIds\ItemStackRequestActionType407;
use pocketmine\network\mcpe\convert\constants\itemStackRequestActionTypeIds\ItemStackRequestActionType422;
use pocketmine\network\mcpe\convert\constants\itemStackRequestActionTypeIds\ItemStackRequestActionType428;
use pocketmine\network\mcpe\convert\constants\itemStackRequestActionTypeIds\ItemStackRequestActionType486;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds110;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds137;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds141;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds223;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds261;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds428;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds475;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds486;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds560;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds567;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds575;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds594;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds622;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds630;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds662;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds671;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds685;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds712;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds729;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds766;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds818;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds819;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds827;
use pocketmine\network\mcpe\convert\constants\levelSoundIds\LevelSoundIds844;
use pocketmine\network\mcpe\convert\constants\particleIds\ParticleIds110;
use pocketmine\network\mcpe\convert\constants\particleIds\ParticleIds137;
use pocketmine\network\mcpe\convert\constants\particleIds\ParticleIds274;
use pocketmine\network\mcpe\convert\constants\particleIds\ParticleIds313;
use pocketmine\network\mcpe\convert\constants\particleIds\ParticleIds361;
use pocketmine\network\mcpe\convert\constants\particleIds\ParticleIds389;
use pocketmine\network\mcpe\convert\constants\particleIds\ParticleIds431;
use pocketmine\network\mcpe\convert\constants\particleIds\ParticleIds448;
use pocketmine\network\mcpe\convert\constants\particleIds\ParticleIds630;
use pocketmine\network\mcpe\convert\constants\particleIds\ParticleIds649;
use pocketmine\network\mcpe\convert\constants\particleIds\ParticleIds662;
use pocketmine\network\mcpe\convert\constants\particleIds\ParticleIds712;
use pocketmine\network\mcpe\convert\constants\particleIds\ParticleIds766;
use pocketmine\network\mcpe\convert\constants\particleIds\ParticleIds844;
use pocketmine\network\mcpe\convert\constants\playerActionIds\PlayerActionIds110;
use pocketmine\network\mcpe\convert\constants\playerActionIds\PlayerActionIds137;
use pocketmine\network\mcpe\convert\constants\playerActionIds\PlayerActionIds419;
use pocketmine\network\mcpe\convert\constants\playerActionIds\PlayerActionIds428;
use pocketmine\network\mcpe\convert\constants\playerActionIds\PlayerActionIds527;
use pocketmine\network\mcpe\convert\constants\playerActionIds\PlayerActionIds567;
use pocketmine\network\mcpe\convert\constants\playerActionIds\PlayerActionIds594;
use pocketmine\network\mcpe\convert\constants\playerActionIds\PlayerActionIds618;
use pocketmine\network\mcpe\convert\constants\playerActionIds\PlayerActionIds622;
use pocketmine\network\mcpe\convert\constants\playerActionIds\PlayerActionIds748;
use pocketmine\network\mcpe\convert\constants\playerActionIds\PlayerActionIds818;
use pocketmine\network\mcpe\convert\constants\resourcePackTypeIds\ResourcePackTypeIds361;
use pocketmine\network\mcpe\convert\constants\resourcePackTypeIds\ResourcePackTypeIds370;
use pocketmine\network\mcpe\convert\constants\textPacketTypeIds\TextPacketTypeIds110;
use pocketmine\network\mcpe\convert\constants\textPacketTypeIds\TextPacketTypeIds137;
use pocketmine\network\mcpe\convert\constants\textPacketTypeIds\TextPacketTypeIds407;
use pocketmine\network\mcpe\convert\constants\textPacketTypeIds\TextPacketTypeIds554;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\PacketDecodeException;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\network\mcpe\protocol\types\BossBarColor;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\ItemStackRequestActionType;
use pocketmine\network\mcpe\protocol\types\resourcepacks\ResourcePackType;
use pocketmine\utils\SingletonTrait;
use ReflectionClass;
use SplFixedArray;

use function array_filter;
use function max;
use function str_starts_with;

use const ARRAY_FILTER_USE_BOTH;

class ConstantTranslator
{
	use SingletonTrait;

	private const int CORE_TO_NETWORK = 0;
	private const int NETWORK_TO_CORE = 1;

	/** @var SplFixedArray<int>[][][] */
	private array $constantsMapping = [];

	public function __construct()
	{
		$this->collect(LevelSoundEventPacket::class, [
			ProtocolInfo::PROTOCOL_844 => LevelSoundIds844::class,
			ProtocolInfo::PROTOCOL_827 => LevelSoundIds827::class,
			ProtocolInfo::PROTOCOL_819 => LevelSoundIds819::class,
			ProtocolInfo::PROTOCOL_818 => LevelSoundIds818::class,
			ProtocolInfo::PROTOCOL_766 => LevelSoundIds766::class,
			ProtocolInfo::PROTOCOL_729 => LevelSoundIds729::class,
			ProtocolInfo::PROTOCOL_712 => LevelSoundIds712::class,
			ProtocolInfo::PROTOCOL_685 => LevelSoundIds685::class,
			ProtocolInfo::PROTOCOL_671 => LevelSoundIds671::class,
			ProtocolInfo::PROTOCOL_662 => LevelSoundIds662::class,
			ProtocolInfo::PROTOCOL_630 => LevelSoundIds630::class,
			ProtocolInfo::PROTOCOL_622 => LevelSoundIds622::class,
			ProtocolInfo::PROTOCOL_594 => LevelSoundIds594::class,
			ProtocolInfo::PROTOCOL_575 => LevelSoundIds575::class,
			ProtocolInfo::PROTOCOL_567 => LevelSoundIds567::class,
			ProtocolInfo::PROTOCOL_560 => LevelSoundIds560::class,
			ProtocolInfo::PROTOCOL_486 => LevelSoundIds486::class,
			ProtocolInfo::PROTOCOL_475 => LevelSoundIds475::class,
			ProtocolInfo::PROTOCOL_428 => LevelSoundIds428::class,
			ProtocolInfo::PROTOCOL_261 => LevelSoundIds261::class,
			ProtocolInfo::PROTOCOL_223 => LevelSoundIds223::class,
			ProtocolInfo::PROTOCOL_141 => LevelSoundIds141::class,
			ProtocolInfo::PROTOCOL_137 => LevelSoundIds137::class,
			ProtocolInfo::PROTOCOL_110 => LevelSoundIds110::class,
		], function ($value, string $name) : bool {
			return str_starts_with($name, "SOUND_");
		});

		$this->collect(AvailableCommandsPacket::class, [
			ProtocolInfo::PROTOCOL_662 => CommandArgumentTypeIds662::class,
			ProtocolInfo::PROTOCOL_582 => CommandArgumentTypeIds582::class,
			ProtocolInfo::PROTOCOL_527 => CommandArgumentTypeIds527::class,
			ProtocolInfo::PROTOCOL_503 => CommandArgumentTypeIds503::class,
			ProtocolInfo::PROTOCOL_428 => CommandArgumentTypeIds428::class,
			ProtocolInfo::PROTOCOL_388 => CommandArgumentTypeIds388::class,
			ProtocolInfo::PROTOCOL_370 => CommandArgumentTypeIds370::class,
			ProtocolInfo::PROTOCOL_340 => CommandArgumentTypeIds340::class,
			ProtocolInfo::PROTOCOL_332 => CommandArgumentTypeIds332::class,
			ProtocolInfo::PROTOCOL_274 => CommandArgumentTypeIds274::class,
			ProtocolInfo::PROTOCOL_137 => CommandArgumentTypeIds137::class
		], function ($value, string $name) : bool {
			return str_starts_with($name, "ARG_TYPE");
		});

		$this->collect(PlayerActionPacket::class, [
			ProtocolInfo::PROTOCOL_818 => PlayerActionIds818::class,
			ProtocolInfo::PROTOCOL_748 => PlayerActionIds748::class,
			ProtocolInfo::PROTOCOL_622 => PlayerActionIds622::class,
			ProtocolInfo::PROTOCOL_618 => PlayerActionIds618::class,
			ProtocolInfo::PROTOCOL_594 => PlayerActionIds594::class,
			ProtocolInfo::PROTOCOL_567 => PlayerActionIds567::class,
			ProtocolInfo::PROTOCOL_527 => PlayerActionIds527::class,
			ProtocolInfo::PROTOCOL_428 => PlayerActionIds428::class,
			ProtocolInfo::PROTOCOL_419 => PlayerActionIds419::class,
			ProtocolInfo::PROTOCOL_137 => PlayerActionIds137::class,
			ProtocolInfo::PROTOCOL_110 => PlayerActionIds110::class,
		], function ($value, string $name) : bool {
			return str_starts_with($name, "ACTION_");
		});

		$this->collect(ResourcePackType::class, [
			ProtocolInfo::PROTOCOL_370 => ResourcePackTypeIds370::class,
			ProtocolInfo::PROTOCOL_361 => ResourcePackTypeIds361::class,
		]);

		$this->collect(TextPacket::class, [
			ProtocolInfo::PROTOCOL_554 => TextPacketTypeIds554::class,
			ProtocolInfo::PROTOCOL_407 => TextPacketTypeIds407::class,
			ProtocolInfo::PROTOCOL_137 => TextPacketTypeIds137::class,
			ProtocolInfo::PROTOCOL_110 => TextPacketTypeIds110::class,
		], function ($value, string $name) : bool {
			return str_starts_with($name, "TYPE_");
		});

		$this->collect(Particle::class, [
			ProtocolInfo::PROTOCOL_844 => ParticleIds844::class,
			ProtocolInfo::PROTOCOL_766 => ParticleIds766::class,
			ProtocolInfo::PROTOCOL_712 => ParticleIds712::class,
			ProtocolInfo::PROTOCOL_662 => ParticleIds662::class,
			ProtocolInfo::PROTOCOL_649 => ParticleIds649::class,
			ProtocolInfo::PROTOCOL_630 => ParticleIds630::class,
			ProtocolInfo::PROTOCOL_448 => ParticleIds448::class,
			ProtocolInfo::PROTOCOL_431 => ParticleIds431::class,
			ProtocolInfo::PROTOCOL_389 => ParticleIds389::class,
			ProtocolInfo::PROTOCOL_361 => ParticleIds361::class,
			ProtocolInfo::PROTOCOL_313 => ParticleIds313::class,
			ProtocolInfo::PROTOCOL_274 => ParticleIds274::class,
			ProtocolInfo::PROTOCOL_137 => ParticleIds137::class,
			ProtocolInfo::PROTOCOL_110 => ParticleIds110::class,
		], function ($value, string $name) : bool {
			return str_starts_with($name, "TYPE_");
		});

		$this->collect(ItemStackRequestActionType::class, [
			ProtocolInfo::PROTOCOL_486 => ItemStackRequestActionType486::class,
			ProtocolInfo::PROTOCOL_428 => ItemStackRequestActionType428::class,
			ProtocolInfo::PROTOCOL_422 => ItemStackRequestActionType422::class,
			ProtocolInfo::PROTOCOL_407 => ItemStackRequestActionType407::class,
		]);

		$this->collect(BossBarColor::class, [
			ProtocolInfo::PROTOCOL_622 => BossBarColor622::class,
			ProtocolInfo::PROTOCOL_110 => BossBarColor110::class,
		]);

		$this->collect(EntityMetadataFlags::class, [
			ProtocolInfo::PROTOCOL_844 => ActorFlags844::class,
			ProtocolInfo::PROTOCOL_818 => ActorFlags818::class,
			ProtocolInfo::PROTOCOL_800 => ActorFlags800::class,
			ProtocolInfo::PROTOCOL_786 => ActorFlags786::class,
			ProtocolInfo::PROTOCOL_776 => ActorFlags776::class,
			ProtocolInfo::PROTOCOL_671 => ActorFlags671::class,
			ProtocolInfo::PROTOCOL_630 => ActorFlags630::class,
			ProtocolInfo::PROTOCOL_594 => ActorFlags594::class,
			ProtocolInfo::PROTOCOL_575 => ActorFlags575::class,
			ProtocolInfo::PROTOCOL_560 => ActorFlags560::class,
			ProtocolInfo::PROTOCOL_475 => ActorFlags475::class,
			ProtocolInfo::PROTOCOL_428 => ActorFlags428::class,
			ProtocolInfo::PROTOCOL_390 => ActorFlags390::class,
			ProtocolInfo::PROTOCOL_354 => ActorFlags354::class,
			ProtocolInfo::PROTOCOL_291 => ActorFlags291::class,
			ProtocolInfo::PROTOCOL_274 => ActorFlags274::class,
			ProtocolInfo::PROTOCOL_223 => ActorFlags223::class,
			ProtocolInfo::PROTOCOL_137 => ActorFlags137::class,
			ProtocolInfo::PROTOCOL_110 => ActorFlags110::class
		]);

		$this->collect(EntityMetadataProperties::class, [
			ProtocolInfo::PROTOCOL_800 => ActorProperties800::class,
			ProtocolInfo::PROTOCOL_776 => ActorProperties776::class,
			ProtocolInfo::PROTOCOL_712 => ActorProperties712::class,
			ProtocolInfo::PROTOCOL_594 => ActorProperties594::class,
			ProtocolInfo::PROTOCOL_428 => ActorProperties428::class,
			ProtocolInfo::PROTOCOL_361 => ActorProperties361::class,
			ProtocolInfo::PROTOCOL_354 => ActorProperties354::class,
			ProtocolInfo::PROTOCOL_340 => ActorProperties340::class,
			ProtocolInfo::PROTOCOL_223 => ActorProperties223::class,
			ProtocolInfo::PROTOCOL_110 => ActorProperties110::class
		]);
	}

	public function collect(string $coreClassConstants, array $netClassesConstants, ?Closure $filter = null) : void
	{
		try {
			$coreConstants = (new ReflectionClass($coreClassConstants))->getConstants();
			if ($filter !== null) {
				$coreConstants = array_filter($coreConstants, $filter, ARRAY_FILTER_USE_BOTH);
			}

			foreach ($netClassesConstants as $protocolVersion => $netClassConstants) {
				$netConstants = (new ReflectionClass($netClassConstants))->getConstants();
				$coreToNet = new SplFixedArray(max($coreConstants) + 1);
				$netToCore = new SplFixedArray(max($netConstants) + 1);

				foreach ($coreConstants as $name => $value) {
					if (isset($netConstants[$name])) {
						$coreToNet[$value] = $netConstants[$name];
					}
				}

				foreach ($netConstants as $name => $value) {
					if (isset($coreConstants[$name])) {
						$netToCore[$value] = $coreConstants[$name];
					}
				}

				$protocolValues = new SplFixedArray(2);
				$protocolValues[self::CORE_TO_NETWORK] = $coreToNet;
				$protocolValues[self::NETWORK_TO_CORE] = $netToCore;

				$this->constantsMapping[$coreClassConstants][$protocolVersion] = $protocolValues;
			}

		} catch (\ReflectionException $exception) {
			\GlobalLogger::get()->error($exception->getMessage());
		}
	}

	public function fromNetworkId(string $coreClassConstants, int $id, int $playerProtocol) : int
	{
		try {
			if (!isset($this->constantsMapping[$coreClassConstants])) {
				throw new ConstantTranslatorException("The $coreClassConstants class for translating constants was not found.");
			}

			foreach ($this->constantsMapping[$coreClassConstants] as $protocol => $ids) {
				if ($playerProtocol >= $protocol) {
					return
						$ids[self::NETWORK_TO_CORE][$id] ??
						throw new ConstantTranslatorException("ID not found $id for class $coreClassConstants");
				}
			}

			throw new ConstantTranslatorException("ID not found $id for class $coreClassConstants");
		} catch (ConstantTranslatorException $exception) {
			throw PacketDecodeException::wrap($exception);
		}
	}

	public function toNetworkId(string $coreClassConstants, int $id, int $playerProtocol, int $default = null) : int
	{
		if (!isset($this->constantsMapping[$coreClassConstants])) {
			throw new ConstantTranslatorException("The $coreClassConstants class for translating constants was not found.");
		}

		foreach ($this->constantsMapping[$coreClassConstants] as $protocol => $ids) {
			if ($playerProtocol >= $protocol) {
				return
					$ids[self::CORE_TO_NETWORK][$id] ??
					$ids[self::CORE_TO_NETWORK][$default] ??
					$default ??
					throw new ConstantTranslatorException("ID not found $id for class $coreClassConstants");
			}
		}

		throw new ConstantTranslatorException("ID not found $id for class $coreClassConstants");
	}

	public function fromNetworkIds(string $coreClassConstants, int $playerProtocol) : array
	{
		try {
			if (!isset($this->constantsMapping[$coreClassConstants])) {
				throw new ConstantTranslatorException("The $coreClassConstants class for translating constants was not found.");
			}

			foreach ($this->constantsMapping[$coreClassConstants] as $protocol => $ids) {
				if ($playerProtocol >= $protocol) {
					return $ids[self::NETWORK_TO_CORE]->toArray();
				}
			}

			return [];
		} catch (ConstantTranslatorException $exception) {
			throw PacketDecodeException::wrap($exception);
		}
	}

	public function toNetworkIds(string $coreClassConstants, int $playerProtocol) : array
	{
		if (!isset($this->constantsMapping[$coreClassConstants])) {
			throw new ConstantTranslatorException("The $coreClassConstants class for translating constants was not found.");
		}

		foreach ($this->constantsMapping[$coreClassConstants] as $protocol => $ids) {
			if ($playerProtocol >= $protocol) {
				return $ids[self::CORE_TO_NETWORK]->toArray();
			}
		}

		return [];
	}
}
