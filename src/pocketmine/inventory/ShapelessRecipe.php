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
use pocketmine\utils\Utils;
use function count;

class ShapelessRecipe implements CraftingRecipe{
	/** @var Item[] */
	private array $ingredients = [];
	/** @var Item[] */
	private array $results;
	private ShapelessRecipeType $type;

	/**
	 * @param Item[] $ingredients No more than 9 total. This applies to sum of item stack counts, not count of array.
	 * @param Item[] $results     List of result items created by this recipe.
	 *
	 * TODO: we'll want to make the type parameter mandatory in PM5
	 */
	public function __construct(array $ingredients, array $results, ?ShapelessRecipeType $type = null){
		$this->type = $type ?? ShapelessRecipeType::CRAFTING();
		foreach($ingredients as $item){
			//Ensure they get split up properly
			if(count($this->ingredients) + $item->getCount() > 9){
				throw new \InvalidArgumentException("Shapeless recipes cannot have more than 9 ingredients");
			}

			while($item->getCount() > 0){
				$this->ingredients[] = $item->pop();
			}
		}

		$this->results = Utils::cloneObjectArray($results);
	}

	/**
	 * @return Item[]
	 */
	public function getResults() : array{
		return Utils::cloneObjectArray($this->results);
	}

	public function getResultsFor(CraftingGrid $grid) : array{
		return $this->getResults();
	}

	public function getType() : ShapelessRecipeType{
		return $this->type;
	}

	/**
	 * @return Item[]
	 */
	public function getIngredientList() : array{
		return Utils::cloneObjectArray($this->ingredients);
	}

	public function getIngredientCount() : int{
		$count = 0;
		foreach($this->ingredients as $ingredient){
			$count += $ingredient->getCount();
		}

		return $count;
	}

	public function matchesCraftingGrid(CraftingGrid $grid) : bool{
		//don't pack the ingredients - shapeless recipes require that each ingredient be in a separate slot
		$input = $grid->getContents();

		foreach($this->ingredients as $needItem){
			foreach($input as $j => $haveItem){
				if($haveItem->equals($needItem, !$needItem->hasAnyDamageValue(), $needItem->hasNamedTag()) && $haveItem->getCount() >= $needItem->getCount()){
					unset($input[$j]);
					continue 2;
				}
			}

			return false; //failed to match the needed item to a given item
		}

		return count($input) === 0; //crafting grid should be empty apart from the given ingredient stacks
	}
}
