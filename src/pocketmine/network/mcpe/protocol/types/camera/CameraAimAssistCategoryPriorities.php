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

namespace pocketmine\network\mcpe\protocol\types\camera;

use pocketmine\network\mcpe\NetworkBinaryStream;

use function count;

final class CameraAimAssistCategoryPriorities
{
	/**
	 * @param CameraAimAssistCategoryEntityPriority[] $entities
	 * @param CameraAimAssistCategoryBlockPriority[]  $blocks
	 */
	public function __construct(
		private array $entities,
		private array $blocks,
		private ?int $defaultEntityPriority,
		private ?int $defaultBlockPriority
	) {
	}

	/**
	 * @return CameraAimAssistCategoryEntityPriority[]
	 */
	public function getEntities() : array
	{
		return $this->entities;
	}

	/**
	 * @return CameraAimAssistCategoryBlockPriority[]
	 */
	public function getBlocks() : array
	{
		return $this->blocks;
	}

	public function getDefaultEntityPriority() : ?int
	{
		return $this->defaultEntityPriority;
	}

	public function getDefaultBlockPriority() : ?int
	{
		return $this->defaultBlockPriority;
	}

	public static function read(NetworkBinaryStream $in) : self
	{
		$entities = [];
		for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
			$entities[] = CameraAimAssistCategoryEntityPriority::read($in);
		}

		$blocks = [];
		for ($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i) {
			$blocks[] = CameraAimAssistCategoryBlockPriority::read($in);
		}

		$defaultEntityPriority = $in->readOptional(fn () => $in->getLInt());
		$defaultBlockPriority = $in->readOptional(fn () => $in->getLInt());
		return new self(
			$entities,
			$blocks,
			$defaultEntityPriority,
			$defaultBlockPriority
		);
	}

	public function write(NetworkBinaryStream $out) : void
	{
		$out->putUnsignedVarInt(count($this->entities));
		foreach ($this->entities as $entity) {
			$entity->write($out);
		}

		$out->putUnsignedVarInt(count($this->blocks));
		foreach ($this->blocks as $block) {
			$block->write($out);
		}

		$out->writeOptional($this->defaultEntityPriority, fn (int $v) => $out->putLInt($v));
		$out->writeOptional($this->defaultBlockPriority, fn (int $v) => $out->putLInt($v));
	}
}
