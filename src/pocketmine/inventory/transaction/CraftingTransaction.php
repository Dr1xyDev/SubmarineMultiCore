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

namespace pocketmine\inventory\transaction;

use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\inventory\CraftingRecipe;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\Player;

use function array_pop;
use function count;
use function intdiv;

/**
 * This transaction type is specialized for crafting validation. It shares most of the same semantics of the base
 * inventory transaction type, but the requirement for validity is slightly different.
 *
 * It is expected that the actions in this transaction type will produce an **unbalanced result**, i.e. some inputs won't
 * have corresponding outputs, and vice versa. The reason why is because the unmatched inputs are recipe inputs, and
 * the unmatched outputs are recipe results.
 *
 * Therefore, the validity requirement is that the imbalance of the transaction should match the expected inputs and
 * outputs of a registered crafting recipe.
 *
 * This transaction allows multiple repetitions of the same recipe to be crafted in a single batch. In the case of batch
 * crafting, the number of unmatched inputs and outputs must be exactly divisible by the expected recipe ingredients and
 * results, with no remainder. Any leftovers are expected to be emitted back to the crafting grid.
 */
class CraftingTransaction extends InventoryTransaction
{
	protected ?CraftingRecipe $recipe = null;
	protected ?int $repetitions = null;

	/** @var Item[] */
	protected array $inputs = [];
	/** @var Item[] */
	protected array $outputs = [];

	public function __construct(Player $source, array $actions = [], ?CraftingRecipe $recipe = null, ?int $repetitions = null)
	{
		parent::__construct($source, $actions);
		$this->recipe = $recipe;
		$this->repetitions = $repetitions;
	}

	/**
	 * @param Item[] $txItems
	 * @param Item[] $recipeItems
	 *
	 * @throws TransactionValidationException
	 */
	protected function matchRecipeItems(array $txItems, array $recipeItems, bool $wildcards, int $iterations = 0) : int
	{
		if (count($recipeItems) === 0) {
			throw new TransactionValidationException("No recipe items given");
		}
		if (count($txItems) === 0) {
			throw new TransactionValidationException("No transaction items given");
		}

		while (count($recipeItems) > 0) {
			/** @var Item $recipeItem */
			$recipeItem = array_pop($recipeItems);
			$needCount = $recipeItem->getCount();
			foreach ($recipeItems as $i => $otherRecipeItem) {
				if ($otherRecipeItem->canStackWith($recipeItem)) { //make sure they have the same wildcards set
					$needCount += $otherRecipeItem->getCount();
					unset($recipeItems[$i]);
				}
			}

			$haveCount = 0;
			foreach ($txItems as $j => $txItem) {
				if ($txItem->equals($recipeItem, !$wildcards || !$recipeItem->hasAnyDamageValue(), !$wildcards || $recipeItem->hasNamedTag())) {
					$haveCount += $txItem->getCount();
					unset($txItems[$j]);
				}
			}

			if ($haveCount % $needCount !== 0) {
				//wrong count for this output, should divide exactly
				throw new TransactionValidationException("Expected an exact multiple of required $recipeItem (given: $haveCount, needed: $needCount)");
			}

			$multiplier = intdiv($haveCount, $needCount);
			if ($multiplier < 1) {
				throw new TransactionValidationException("Expected more than zero items matching $recipeItem (given: $haveCount, needed: $needCount)");
			}
			if ($iterations === 0) {
				$iterations = $multiplier;
			} elseif ($multiplier !== $iterations) {
				//wrong count for this output, should match previous outputs
				throw new TransactionValidationException("Expected $recipeItem x$iterations, but found x$multiplier");
			}
		}

		if (count($txItems) > 0) {
			//all items should be destroyed in this process
			throw new TransactionValidationException("Expected 0 ingredients left over, have " . count($txItems));
		}

		return $iterations;
	}

	private function validateRecipe(CraftingRecipe $recipe, ?int $expectedRepetitions) : int
	{
		//compute number of times recipe was crafted
		$repetitions = $this->matchRecipeItems($this->outputs, $recipe->getResultsFor($this->source->getCraftingGrid()), false);
		if ($expectedRepetitions !== null && $repetitions !== $expectedRepetitions) {
			throw new TransactionValidationException("Expected $expectedRepetitions repetitions, got $repetitions");
		}
		//assert that $repetitions x recipe ingredients should be consumed
		$this->matchRecipeItems($this->inputs, $recipe->getIngredientList(), true, $repetitions);
		return $repetitions;
	}

	public function validate() : void
	{
		$this->squashDuplicateSlotChanges();
		if (count($this->actions) < 1) {
			throw new TransactionValidationException("Transaction must have at least one action to be executable");
		}

		$this->matchItems($this->outputs, $this->inputs);

		if ($this->recipe === null) {
			$failed = 0;
			foreach ($this->source->getServer()->getCraftingManager()->matchRecipeByOutputs($this->outputs, $this->source->getCraftingProtocol()) as $recipe) {
				try {
					//compute number of times recipe was crafted
					$this->repetitions = $this->matchRecipeItems($this->outputs, $recipe->getResultsFor($this->source->getCraftingGrid()), false);
					//assert that $repetitions x recipe ingredients should be consumed
					$this->matchRecipeItems($this->inputs, $recipe->getIngredientList(), true, $this->repetitions);

					//Success!
					$this->recipe = $recipe;
					break;
				} catch (TransactionValidationException $e) {
					//failed
					++$failed;
				}
			}

			if ($this->recipe === null) {
				throw new TransactionValidationException("Unable to match a recipe to transaction (tried to match against $failed recipes)");
			}
		} else {
			$this->repetitions = $this->validateRecipe($this->recipe, $this->repetitions);
		}
	}

	protected function callExecuteEvent() : bool
	{
		$ev = new CraftItemEvent($this, $this->recipe, $this->repetitions, $this->inputs, $this->outputs);
		$ev->call();
		return !$ev->isCancelled();
	}

	protected function sendInventories() : void
	{
		if ($this->source->getProtocolVersion() < ProtocolInfo::PROTOCOL_137) {
			return;
		}

		parent::sendInventories();

		/*
		 * TODO: HACK!
		 * we can't resend the contents of the crafting window, so we force the client to close it instead.
		 * So people don't whine about messy desync issues when someone cancels CraftItemEvent, or when a crafting
		 * transaction goes wrong.
		 */
		$pk = new ContainerClosePacket();
		if ($this->source->getProtocolVersion() >= ProtocolInfo::PROTOCOL_407) {
			$pk->windowId = ContainerIds::INVENTORY;
		} else {
			$pk->windowId = ContainerIds::NONE;
		}
		$pk->windowType = $this->source->getCurrentWindowType();
		$pk->server = true;
		$this->source->dataPacket($pk);
	}
}
