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

namespace pocketmine\network\mcpe\convert\constants\commandArgumentTypeIds;

final class CommandArgumentTypeIds137
{
	private function __construct()
	{
		//NOOP
	}

	public const ARG_TYPE_INT = 0x01;
	public const ARG_TYPE_FLOAT = 0x02;
	public const ARG_TYPE_VALUE = 0x03;
	public const ARG_TYPE_TARGET = 0x04;

	public const ARG_TYPE_STRING = 0x0d;

	public const ARG_TYPE_POSITION = 0x0e;

	public const ARG_TYPE_RAWTEXT = 0x11;

	//TODO: ARG_TYPE_TEXT 13

	public const ARG_TYPE_JSON = 0x16;

	public const ARG_TYPE_COMMAND = 0x1d;

}
