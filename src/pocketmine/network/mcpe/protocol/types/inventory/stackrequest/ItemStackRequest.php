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

namespace pocketmine\network\mcpe\protocol\types\inventory\stackrequest;

use pocketmine\network\mcpe\convert\ConstantTranslator;
use pocketmine\network\mcpe\convert\ConstantTranslatorException;
use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\PacketDecodeException;
use pocketmine\network\mcpe\protocol\ProtocolInfo;

use function count;

final class ItemStackRequest
{
	/**
	 * @param ItemStackRequestAction[] $actions
	 * @param string[]                 $filterStrings
	 * @phpstan-param list<string> $filterStrings
	 */
	public function __construct(
		private int $requestId,
		private array $actions,
		private array $filterStrings,
		private int $filterStringCause
	) {
	}

	public function getRequestId() : int
	{
		return $this->requestId;
	}

	/** @return ItemStackRequestAction[] */
	public function getActions() : array
	{
		return $this->actions;
	}

	/**
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function getFilterStrings() : array
	{
		return $this->filterStrings;
	}

	public function getFilterStringCause() : int
	{
		return $this->filterStringCause;
	}

	private static function readAction(NetworkBinaryStream $in, int $typeId, int $playerProtocol) : ItemStackRequestAction
	{
		$typeId = ConstantTranslator::getInstance()->fromNetworkId(ItemStackRequestActionType::class, $typeId, $playerProtocol);
		return match($typeId) {
			TakeStackRequestAction::ID => TakeStackRequestAction::read($in, $playerProtocol),
			PlaceStackRequestAction::ID => PlaceStackRequestAction::read($in, $playerProtocol),
			SwapStackRequestAction::ID => SwapStackRequestAction::read($in, $playerProtocol),
			DropStackRequestAction::ID => DropStackRequestAction::read($in, $playerProtocol),
			DestroyStackRequestAction::ID => DestroyStackRequestAction::read($in, $playerProtocol),
			CraftingConsumeInputStackRequestAction::ID => CraftingConsumeInputStackRequestAction::read($in, $playerProtocol),
			CraftingCreateSpecificResultStackRequestAction::ID => CraftingCreateSpecificResultStackRequestAction::read($in, $playerProtocol),
			LabTableCombineStackRequestAction::ID => LabTableCombineStackRequestAction::read($in, $playerProtocol),
			BeaconPaymentStackRequestAction::ID => BeaconPaymentStackRequestAction::read($in, $playerProtocol),
			MineBlockStackRequestAction::ID => MineBlockStackRequestAction::read($in, $playerProtocol),
			CraftRecipeStackRequestAction::ID => CraftRecipeStackRequestAction::read($in, $playerProtocol),
			CraftRecipeAutoStackRequestAction::ID => CraftRecipeAutoStackRequestAction::read($in, $playerProtocol),
			CreativeCreateStackRequestAction::ID => CreativeCreateStackRequestAction::read($in, $playerProtocol),
			CraftRecipeOptionalStackRequestAction::ID => CraftRecipeOptionalStackRequestAction::read($in, $playerProtocol),
			GrindstoneStackRequestAction::ID => GrindstoneStackRequestAction::read($in, $playerProtocol),
			LoomStackRequestAction::ID => LoomStackRequestAction::read($in, $playerProtocol),
			DeprecatedCraftingNonImplementedStackRequestAction::ID => DeprecatedCraftingNonImplementedStackRequestAction::read($in, $playerProtocol),
			DeprecatedCraftingResultsStackRequestAction::ID => DeprecatedCraftingResultsStackRequestAction::read($in, $playerProtocol),
			default => throw new PacketDecodeException("Unhandled item stack request action type $typeId for protocol $playerProtocol "),
		};
	}

	public static function read(NetworkBinaryStream $in, int $playerProtocol) : self
	{
		$requestId = $in->readItemStackRequestId();
		$actions = [];
		for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
			$typeId = $in->getByte();
			$actions[] = self::readAction($in, $typeId, $playerProtocol);
		}
		$filterStrings = [];
		for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
			$filterStrings[] = $in->getString();
		}
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_557) {
			$filterStringCause = $in->getLInt();
		}
		return new self($requestId, $actions, $filterStrings, $filterStringCause ?? 0);
	}

	public function write(NetworkBinaryStream $out, int $playerProtocol) : void
	{
		$out->writeItemStackRequestId($this->requestId);
		$out->putUnsignedVarInt(count($this->actions));
		foreach ($this->actions as $action) {
			try {
				$typeId = ConstantTranslator::getInstance()->toNetworkId(ItemStackRequestActionType::class, $action->getTypeId(), $playerProtocol);
				$out->putByte($typeId);
				$action->write($out, $playerProtocol);
			} catch (ConstantTranslatorException $exception) {
			}
		}
		$out->putUnsignedVarInt(count($this->filterStrings));
		foreach ($this->filterStrings as $string) {
			$out->putString($string);
		}
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_557) {
			$out->putLInt($this->filterStringCause);
		}
	}
}
