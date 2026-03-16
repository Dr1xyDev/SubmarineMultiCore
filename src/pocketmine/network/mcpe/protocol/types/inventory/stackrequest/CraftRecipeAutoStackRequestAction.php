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

use pocketmine\item\Item;
use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

use function count;

/**
 * Tells that the current transaction crafted the specified recipe, using the recipe book. This is effectively the same
 * as the regular crafting result action.
 */
final class CraftRecipeAutoStackRequestAction extends ItemStackRequestAction
{
	use GetTypeIdFromConstTrait;

	public const ID = ItemStackRequestActionType::CRAFTING_RECIPE_AUTO;

	/**
	 * @param Item[] $ingredients
	 * @phpstan-param list<Item> $ingredients
	 */
	final public function __construct(
		private int $recipeId,
		private int $repetitions,
		private int $repetitions2,
		private array $ingredients
	) {
	}

	public function getRecipeId() : int
	{
		return $this->recipeId;
	}

	public function getRepetitions() : int
	{
		return $this->repetitions;
	}

	public function getRepetitions2() : int
	{
		return $this->repetitions2;
	}

	/**
	 * @return Item[]
	 * @phpstan-return list<Item>
	 */
	public function getIngredients() : array
	{
		return $this->ingredients;
	}

	public static function read(NetworkBinaryStream $in, int $playerProtocol) : self
	{
		$recipeId = $in->readRecipeNetId();
		$repetitions = $in->getByte();
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_712) {
			$repetitions2 = $in->getByte(); //repetitions property is sent twice, mojang...
		}
		$ingredients = [];
		for ($i = 0, $count = $in->getByte(); $i < $count; ++$i) {
			$ingredients[] = $in->getRecipeIngredient($playerProtocol);
		}
		return new self($recipeId, $repetitions, $repetitions2 ?? 0, $ingredients);
	}

	public function write(NetworkBinaryStream $out, int $playerProtocol) : void
	{
		$out->writeRecipeNetId($this->recipeId);
		$out->putByte($this->repetitions);
		if ($playerProtocol >= ProtocolInfo::PROTOCOL_712) {
			$out->putByte($this->repetitions2);
		}
		$out->putByte(count($this->ingredients));
		foreach ($this->ingredients as $ingredient) {
			$out->putRecipeIngredient($ingredient, $playerProtocol);
		}
	}
}
