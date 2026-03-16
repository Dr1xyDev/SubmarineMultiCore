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

namespace pocketmine\inventory;

use Generator;
use pocketmine\item\Item;
use pocketmine\nbt\LittleEndianNBTStream;
use pocketmine\network\mcpe\cache\CraftingDataCache;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\utils\BinaryStream;

use function count;
use function ksort;
use function usort;

class CraftingManager
{

	/**
	 * @var ShapedRecipe[][][]
	 * @phpstan-var array<int, array<string, list<ShapedRecipe>>>
	 */
	protected array $shapedRecipes = [];
	/**
	 * @var ShapelessRecipe[][][]
	 * @phpstan-var array<int, array<string, list<ShapelessRecipe>>>
	 */
	protected array $shapelessRecipes = [];

	/**
	 * @var FurnaceRecipe[][][]
	 * @phpstan-var array<int, array<string, list<FurnaceRecipe>>>
	 */
	protected array $furnaceRecipes = [];

	/**
	 * @var CraftingRecipe[][]
	 * @phpstan-var array<int, array<int, CraftingRecipe>>
	 */
	private array $craftingRecipeIndex = [];

	/**
	 * @var PotionTypeRecipe[][][]
	 * @phpstan-var array<int, array<string, array<string, PotionTypeRecipe>>>
	 */
	protected array $potionTypeRecipes = [];

	/**
	 * @var PotionContainerChangeRecipe[][][]
	 * @phpstan-var array<int, array<int, array<string, PotionContainerChangeRecipe>>>
	 */
	protected array $potionContainerChangeRecipes = [];

	/**
	 * Function used to arrange Shapeless Recipe ingredient lists into a consistent order.
	 */
	public static function sort(Item $i1, Item $i2) : int{
		//Use spaceship operator to compare each property, then try the next one if they are equivalent.
		($retval = $i1->getId() <=> $i2->getId()) === 0 && ($retval = $i1->getDamage() <=> $i2->getDamage()) === 0 && ($retval = $i1->getCount() <=> $i2->getCount()) === 0;

		return $retval;
	}

	/**
	 * @param Item[] $items
	 *
	 * @return Item[]
	 */
	private static function pack(array $items) : array{
		/** @var Item[] $result */
		$result = [];

		foreach($items as $i => $item){
			foreach($result as $otherItem){
				if($item->canStackWith($otherItem)){
					$otherItem->setCount($otherItem->getCount() + $item->getCount());
					continue 2;
				}
			}

			//No matching item found
			$result[] = clone $item;
		}

		return $result;
	}

	/**
	 * @param Item[] $outputs
	 */
	private static function hashOutputs(array $outputs) : string{
		$outputs = self::pack($outputs);
		usort($outputs, [self::class, "sort"]);
		$result = new BinaryStream();
		foreach($outputs as $o){
			//count is not written because the outputs might be from multiple repetitions of a single recipe
			//this reduces the accuracy of the hash, but it won't matter in most cases.
			$result->putVarInt($o->getId());
			$result->putVarInt($o->getDamage());

			$tags = $o->getNamedTag()->getValue();
			ksort($tags);
			$result->put((new LittleEndianNBTStream())->write($tags));
		}

		return $result->getBuffer();
	}

	/**
	 * @return ShapelessRecipe[][]
	 * @phpstan-return array<string, list<ShapelessRecipe>>
	 */
	public function getShapelessRecipes(int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : array{
		foreach ($this->shapelessRecipes as $protocol => $shapelessRecipes) {
			if ($protocolVersion >= $protocol) {
				return $shapelessRecipes;
			}
		}

		return [];
	}

	/**
	 * @return ShapedRecipe[][]
	 * @phpstan-return array<string, list<ShapedRecipe>>
	 */
	public function getShapedRecipes(int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : array{
		foreach ($this->shapedRecipes as $protocol => $shapedRecipes) {
			if ($protocolVersion >= $protocol) {
				return $shapedRecipes;
			}
		}

		return [];
	}

	/**
	 * @return FurnaceRecipe[][]
	 * @phpstan-return array<string, list<FurnaceRecipe>>
	 */
	public function getFurnaceRecipes(int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : array{
		foreach ($this->furnaceRecipes as $protocol => $furnaceRecipes) {
			if ($protocolVersion >= $protocol) {
				return $furnaceRecipes;
			}
		}

		return [];
	}

	/**
	 * @return CraftingRecipe[]
	 * @phpstan-return array<int, CraftingRecipe>
	 */
	public function getCraftingRecipeIndex(int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : array{
		foreach ($this->craftingRecipeIndex as $protocol => $craftingRecipeIndexes) {
			if ($protocolVersion >= $protocol) {
				return $craftingRecipeIndexes;
			}
		}

		return [];
	}

	public function getCraftingRecipeFromIndex(int $index, int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : ?CraftingRecipe{
		return $this->getCraftingRecipeIndex($protocolVersion)[$index] ?? null;
	}

	/**
	 * @return PotionTypeRecipe[][]
	 * @phpstan-return array<string, array<string, PotionTypeRecipe>>
	 */
	public function getPotionTypeRecipes(int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : array{
		foreach ($this->potionTypeRecipes as $protocol => $potionTypeRecipes) {
			if ($protocolVersion >= $protocol) {
				return $potionTypeRecipes;
			}
		}

		return [];
	}

	/**
	 * @return PotionContainerChangeRecipe[][]
	 * @phpstan-return array<int, array<string, PotionContainerChangeRecipe>>
	 */
	public function getPotionContainerChangeRecipes(int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : array{
		foreach ($this->potionContainerChangeRecipes as $protocol => $potionContainerChangeRecipes) {
			if ($protocolVersion >= $protocol) {
				return $potionContainerChangeRecipes;
			}
		}

		return [];
	}

	public function registerShapedRecipe(ShapedRecipe $recipe, ?int $protocolVersion = null) : void{
		$outputHash = self::hashOutputs($recipe->getResults());
		if ($protocolVersion === null) {
			foreach ($this->shapedRecipes as $protocol => $shapedRecipes) {
				$this->shapedRecipes[$protocol][$outputHash][] = $recipe;
				$this->craftingRecipeIndex[$protocol][] = $recipe;
			}
		} else {
			$this->shapedRecipes[$protocolVersion][$outputHash][] = $recipe;
			$this->craftingRecipeIndex[$protocolVersion][] = $recipe;
		}

		CraftingDataCache::getInstance()->clearCache($this);
	}

	public function registerShapelessRecipe(ShapelessRecipe $recipe, ?int $protocolVersion = null) : void{
		$outputHash = self::hashOutputs($recipe->getResults());
		if ($protocolVersion === null) {
			foreach ($this->shapelessRecipes as $protocol => $shapelessRecipes) {
				$this->shapelessRecipes[$protocol][$outputHash][] = $recipe;
				$this->craftingRecipeIndex[$protocol][] = $recipe;
			}
		} else {
			$this->shapelessRecipes[$protocolVersion][$outputHash][] = $recipe;
			$this->craftingRecipeIndex[$protocolVersion][] = $recipe;
		}

		CraftingDataCache::getInstance()->clearCache($this);
	}

	public function registerFurnaceRecipe(FurnaceRecipe $recipe, ?int $protocolVersion = null, FurnaceType $furnaceType = FurnaceType::FURNACE) : void{
		$input = $recipe->getInput();
		if ($protocolVersion === null) {
			foreach ($this->furnaceRecipes as $protocol => $furnaceRecipes) {
				$this->furnaceRecipes[$protocol][$furnaceType->name()][$input->getId() . ":" . ($input->hasAnyDamageValue() ? "?" : $input->getDamage())] = $recipe;
			}
		} else {
			$this->furnaceRecipes[$protocolVersion][$furnaceType->name()][$input->getId() . ":" . ($input->hasAnyDamageValue() ? "?" : $input->getDamage())] = $recipe;
		}

		CraftingDataCache::getInstance()->clearCache($this);
	}

	public function registerPotionTypeRecipe(PotionTypeRecipe $recipe, ?int $protocolVersion = null) : void{
		$input = $recipe->getInput();
		$ingredient = $recipe->getIngredient();
		if ($protocolVersion === null) {
			foreach ($this->potionTypeRecipes as $protocol => $potionTypeRecipes) {
				$this->potionTypeRecipes[$protocol][$input->getId() . ":" . $input->getDamage()][$ingredient->getId() . ":" . ($ingredient->hasAnyDamageValue() ? "?" : $ingredient->getDamage())] = $recipe;
			}
		} else {
			$this->potionTypeRecipes[$protocolVersion][$input->getId() . ":" . $input->getDamage()][$ingredient->getId() . ":" . ($ingredient->hasAnyDamageValue() ? "?" : $ingredient->getDamage())] = $recipe;
		}

		CraftingDataCache::getInstance()->clearCache($this);
	}

	public function registerPotionContainerChangeRecipe(PotionContainerChangeRecipe $recipe, ?int $protocolVersion = null) : void{
		$ingredient = $recipe->getIngredient();
		if ($protocolVersion === null) {
			foreach ($this->potionContainerChangeRecipes as $protocol => $potionContainerChangeRecipes) {
				$this->potionContainerChangeRecipes[$protocol][$recipe->getInputItemId()][$ingredient->getId() . ":" . ($ingredient->hasAnyDamageValue() ? "?" : $ingredient->getDamage())] = $recipe;
			}
		} else {
			$this->potionContainerChangeRecipes[$protocolVersion][$recipe->getInputItemId()][$ingredient->getId() . ":" . ($ingredient->hasAnyDamageValue() ? "?" : $ingredient->getDamage())] = $recipe;
		}

		CraftingDataCache::getInstance()->clearCache($this);
	}

	/**
	 * @param Item[] $outputs
	 */
	public function matchRecipe(CraftingGrid $grid, array $outputs, int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : ?CraftingRecipe{
		//TODO: try to match special recipes before anything else (first they need to be implemented!)

		$outputHash = self::hashOutputs($outputs);

		$shapedRecipes = $this->getShapedRecipes($protocolVersion);
		if(isset($shapedRecipes[$outputHash])){
			foreach($shapedRecipes[$outputHash] as $recipe){
				if($recipe->matchesCraftingGrid($grid)){
					return $recipe;
				}
			}
		}

		$shapelessRecipes = $this->getShapelessRecipes($protocolVersion);
		if(isset($shapelessRecipes[$outputHash])){
			foreach($shapelessRecipes[$outputHash] as $recipe){
				if($recipe->matchesCraftingGrid($grid)){
					return $recipe;
				}
			}
		}

		return null;
	}

	/**
	 * @param Item[] $outputs
	 *
	 * @return CraftingRecipe[]|Generator
	 * @phpstan-return Generator<int, CraftingRecipe, void, void>
	 */
	public function matchRecipeByOutputs(array $outputs, int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : Generator{
		//TODO: try to match special recipes before anything else (first they need to be implemented!)

		$outputHash = self::hashOutputs($outputs);

		$shapedRecipes = $this->getShapedRecipes($protocolVersion);
		if(isset($shapedRecipes[$outputHash])){
			foreach($shapedRecipes[$outputHash] as $recipe){
				yield $recipe;
			}
		}

		$shapelessRecipes = $this->getShapelessRecipes($protocolVersion);
		if(isset($shapelessRecipes[$outputHash])){
			foreach($shapelessRecipes[$outputHash] as $recipe){
				yield $recipe;
			}
		}
	}

	public function matchFurnaceRecipe(Item $input, int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL, FurnaceType $furnaceType = FurnaceType::FURNACE) : ?FurnaceRecipe{
		$furnaceRecipes = $this->getFurnaceRecipes($protocolVersion);
		return $furnaceRecipes[$furnaceType->name()][$input->getId() . ":" . $input->getDamage()] ?? $furnaceRecipes[$furnaceType->name()][$input->getId() . ":?"] ?? null;
	}

	public function matchBrewingRecipe(Item $input, Item $ingredient, int $protocolVersion = ProtocolInfo::CURRENT_PROTOCOL) : ?BrewingRecipe{
		$potionTypeRecipes = $this->getPotionTypeRecipes($protocolVersion);
		$potionContainerChangeRecipes = $this->getPotionContainerChangeRecipes($protocolVersion);
		return $potionTypeRecipes[$input->getId() . ":" . $input->getDamage()][$ingredient->getId() . ":" . $ingredient->getDamage()] ??
			$potionTypeRecipes[$input->getId() . ":" . $input->getDamage()][$ingredient->getId() . ":?"] ??
			$potionContainerChangeRecipes[$input->getId()][$ingredient->getId() . ":" . $ingredient->getDamage()] ??
			$potionContainerChangeRecipes[$input->getId()][$ingredient->getId() . ":?"] ?? null;
	}
}
