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

class PotionTypeRecipe implements BrewingRecipe{

	public function __construct(
		private Item $input,
		private Item $ingredient,
		private Item $output
	){
		$this->input = clone $input;
		$this->ingredient = clone $ingredient;
		$this->output = clone $output;
	}

	public function getInput() : Item{
		return clone $this->input;
	}

	public function getIngredient() : Item{
		return clone $this->ingredient;
	}

	public function getOutput() : Item{
		return clone $this->output;
	}

	public function getResultFor(Item $input) : ?Item{
		return $input->equals($this->input, true, false) ? $this->getOutput() : null;
	}
}
