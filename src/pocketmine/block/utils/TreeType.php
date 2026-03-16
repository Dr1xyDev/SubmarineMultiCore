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

namespace pocketmine\block\utils;

use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static TreeType ACACIA()
 * @method static TreeType BIRCH()
 * @method static TreeType DARK_OAK()
 * @method static TreeType JUNGLE()
 * @method static TreeType OAK()
 * @method static TreeType SPRUCE()
 * @method static TreeType CHERRY()
 * @method static TreeType PALE_OAK()
 */
final class TreeType
{
	use EnumTrait {
		register as Enum_register;
		__construct as Enum___construct;
	}

	/** @var TreeType[] */
	private static array $numericIdMap = [];

	protected static function setup() : void
	{
		self::registerAll(
			new TreeType("oak", "Oak"),
			new TreeType("spruce", "Spruce"),
			new TreeType("birch", "Birch"),
			new TreeType("jungle", "Jungle"),
			new TreeType("acacia", "Acacia"),
			new TreeType("dark_oak", "Dark Oak"),
			new TreeType("cherry", "Cherry"),
			new TreeType("pale_oak", "Pale Oak")
		);
	}

	protected static function register(TreeType $member) : void
	{
		self::Enum_register($member);
	}

	private function __construct(
		string $enumName,
		private string $displayName
	) {
		$this->Enum___construct($enumName);
	}

	public function getDisplayName() : string
	{
		return $this->displayName;
	}
}
