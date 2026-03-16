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

namespace raklib\protocol;

final class SplitPacketInfo
{
	public function __construct(
		private int $id,
		private int $partIndex,
		private int $totalPartCount
	) {
		//TODO: argument validation
	}

	public function getId() : int
	{
		return $this->id;
	}

	public function getPartIndex() : int
	{
		return $this->partIndex;
	}

	public function getTotalPartCount() : int
	{
		return $this->totalPartCount;
	}
}
