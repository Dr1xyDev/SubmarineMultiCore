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

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Filesystem;
use function array_map;
use function is_array;
use function json_decode;

final class CraftingManagerFromDataHelper{

	/**
	 * @param Item[] $items
	 */
	private static function containsUnknownOutputs(array $items) : bool{
		foreach($items as $item){
			if($item->hasAnyDamageValue()){
				throw new \InvalidArgumentException("Recipe outputs must not have wildcard meta values");
			}
			if(!ItemFactory::isRegistered($item->getId(), $item->getDamage())){
				return true;
			}
		}

		return false;
	}

	public static function make(string $filePath) : CraftingManager{
		$recipes = json_decode(Filesystem::fileGetContents($filePath), true);
		if(!is_array($recipes)){
			throw new AssumptionFailedError("recipes.json root should contain a map of recipe types");
		}

		$result = new CraftingManager();

		$itemDeserializerFunc = \Closure::fromCallable([Item::class, 'jsonDeserialize']);

		$protocol = ProtocolInfo::PROTOCOL_110; //TODO: make your own recipes for each protocol

		foreach($recipes["shapeless"] as $recipe){
			$recipeType = match($recipe["block"]){
				"crafting_table" => ShapelessRecipeType::CRAFTING(),
				"stonecutter" => ShapelessRecipeType::STONECUTTER(),
				"smithing" => ShapelessRecipeType::SMITHING(),
				"cartography" => ShapelessRecipeType::CARTOGRAPHY(),
				default => null
			};
			if($recipeType === null){
				continue;
			}
			$output = array_map($itemDeserializerFunc, $recipe["output"]);
			if(self::containsUnknownOutputs($output)){
				continue;
			}

			$result->registerShapelessRecipe(new ShapelessRecipe(
				array_map($itemDeserializerFunc, $recipe["input"]),
				$output,
				$recipeType
			), $protocol);
		}
		foreach($recipes["shaped"] as $recipe){
			if($recipe["block"] !== "crafting_table"){ //TODO: filter others out for now to avoid breaking economics
				continue;
			}
			$output = array_map($itemDeserializerFunc, $recipe["output"]);
			if(self::containsUnknownOutputs($output)){
				continue;
			}

			$ingredients = array_map($itemDeserializerFunc, $recipe["input"]);
			/** @var Item[] $ingredients */
			foreach ($ingredients as $ingredient) {
				//fix planks > 1.20.50
				if ($ingredient->getId() === ItemIds::PLANKS && $ingredient->getDamage() === -1) {
					for ($meta = 0; $meta <= 5; ++$meta) {
						/** @var Item[] $fixIngredients */
						$fixIngredients = array_map($itemDeserializerFunc, $recipe["input"]);
						foreach ($ingredients as $key => $fixIngredient) {
							if ($fixIngredient->getId() === ItemIds::PLANKS && $fixIngredient->getDamage() === -1) {
								$fixIngredients[$key]->setDamage($meta);
							}
						}

						$result->registerShapedRecipe(new ShapedRecipe(
							$recipe["shape"],
							$fixIngredients,
							$output
						), $protocol);
					}

					continue 2;
				}
			}

			$result->registerShapedRecipe(new ShapedRecipe(
				$recipe["shape"],
				array_map($itemDeserializerFunc, $recipe["input"]),
				$output
			), $protocol);
		}
		foreach($recipes["smelting"] as $recipe){
			$furnaceType = match ($recipe["block"]){
				"furnace" => FurnaceType::FURNACE(),
				"blast_furnace" => FurnaceType::BLAST_FURNACE(),
				"smoker" => FurnaceType::SMOKER(),
				"campfire" => FurnaceType::CAMPFIRE(),
				"soul_campfire" => FurnaceType::SOUL_CAMPFIRE(),
				default => null
			};
			if($furnaceType === null){
				continue;
			}
			$output = Item::jsonDeserialize($recipe["output"]);
			if(self::containsUnknownOutputs([$output])){
				continue;
			}

			$result->registerFurnaceRecipe(new FurnaceRecipe($output, Item::jsonDeserialize($recipe["input"])), $protocol, $furnaceType);
		}
		foreach($recipes["potion_type"] as $recipe){
			$output = Item::jsonDeserialize($recipe["output"]);
			if(self::containsUnknownOutputs([$output])){
				continue;
			}
			$result->registerPotionTypeRecipe(new PotionTypeRecipe(
				Item::jsonDeserialize($recipe["input"]),
				Item::jsonDeserialize($recipe["ingredient"]),
				$output
			), $protocol);
		}
		foreach($recipes["potion_container_change"] as $recipe){
			if(!ItemFactory::isRegistered($recipe["output_item_id"])){
				continue;
			}
			$result->registerPotionContainerChangeRecipe(new PotionContainerChangeRecipe(
				$recipe["input_item_id"],
				Item::jsonDeserialize($recipe["ingredient"]),
				$recipe["output_item_id"]
			), $protocol);
		}

		return $result;
	}
}
