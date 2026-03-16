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

namespace pocketmine\network\mcpe\protocol\types\recipe;

use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\utils\UUID;
use function count;

final class ShapelessRecipe extends RecipeWithTypeId{
	/**
	 * @param RecipeIngredient[] $inputs
	 * @param ItemStack[]        $outputs
	 * @phpstan-param list<RecipeIngredient> $inputs
	 * @phpstan-param list<ItemStack> $outputs
	 */
	public function __construct(
		int $typeId,
		private string $recipeId,
		private array $inputs,
		private array $outputs,
		private UUID $uuid,
		private string $blockName,
		private int $priority,
		private RecipeUnlockingRequirement $unlockingRequirement,
		private int $recipeNetId
	){
		parent::__construct($typeId);
	}

	public function getRecipeId() : string{
		return $this->recipeId;
	}

	/**
	 * @return RecipeIngredient[]
	 * @phpstan-return list<RecipeIngredient>
	 */
	public function getInputs() : array{
		return $this->inputs;
	}

	/**
	 * @return ItemStack[]
	 * @phpstan-return list<ItemStack>
	 */
	public function getOutputs() : array{
		return $this->outputs;
	}

	public function getUuid() : UUID{
		return $this->uuid;
	}

	public function getBlockName() : string{
		return $this->blockName;
	}

	public function getPriority() : int{
		return $this->priority;
	}

	public function getUnlockingRequirement() : RecipeUnlockingRequirement{ return $this->unlockingRequirement; }

	public function getRecipeNetId() : int{
		return $this->recipeNetId;
	}

	public static function decode(int $recipeType, NetworkBinaryStream $in, int $protocol) : self{
		if ($protocol >= ProtocolInfo::PROTOCOL_361) {
			$recipeId = $in->getString();
		}

		$input = [];
		for($j = 0, $ingredientCount = $in->getUnsignedVarInt(); $j < $ingredientCount; ++$j){
			$input[] = $in->getRecipeIngredient($protocol);
		}
		$output = [];
		for($k = 0, $resultCount = $in->getUnsignedVarInt(); $k < $resultCount; ++$k){
			$output[] = $in->getItemStackWithoutStackId($protocol);
		}

		$uuid = $in->getUUID();
		if ($protocol >= ProtocolInfo::PROTOCOL_354) {
			$block = $in->getString();
			if ($protocol >= ProtocolInfo::PROTOCOL_361) {
				$priority = $in->getVarInt();
				if ($protocol >= ProtocolInfo::PROTOCOL_407) {
					if ($protocol >= ProtocolInfo::PROTOCOL_685) {
						$unlockingRequirement = RecipeUnlockingRequirement::read($in, $protocol);
					}

					$recipeNetId = $in->readRecipeNetId();
				}
			}
		}

		return new self(
			$recipeType,
			$recipeId ?? "",
			$input,
			$output,
			$uuid,
			$block ?? CraftingRecipeBlockName::CRAFTING_TABLE,
			$priority ?? 1,
			$unlockingRequirement ?? new RecipeUnlockingRequirement([]),
			$recipeNetId ?? 1
		);
	}

	public function encode(NetworkBinaryStream $out, int $protocol) : void{
		if ($protocol >= ProtocolInfo::PROTOCOL_361) {
			$out->putString($this->recipeId);
		}

		$out->putUnsignedVarInt(count($this->inputs));
		foreach ($this->inputs as $item) {
			$out->putRecipeIngredient($item, $protocol);
		}

		$out->putUnsignedVarInt(count($this->outputs));
		foreach ($this->outputs as $item) {
			$out->putItemStackWithoutStackId($item, $protocol);
		}

		$out->putUUID($this->uuid);
		if ($protocol >= ProtocolInfo::PROTOCOL_354) {
			$out->putString($this->blockName);
			if ($protocol >= ProtocolInfo::PROTOCOL_361) {
				$out->putVarInt($this->priority);
				if ($protocol >= ProtocolInfo::PROTOCOL_407) {
					if ($protocol >= ProtocolInfo::PROTOCOL_685) {
						$this->unlockingRequirement->write($out, $protocol);
					}

					$out->writeRecipeNetId($this->recipeNetId);
				}
			}
		}
	}
}
