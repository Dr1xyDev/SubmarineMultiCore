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

namespace raklib\generic;

class PacketHandlingException extends \RuntimeException
{
	/** @phpstan-var DisconnectReason::* */
	private int $disconnectReason;
	/**
	 * @phpstan-param DisconnectReason::* $disconnectReason
	 */
	public function __construct(string $message, int $disconnectReason, int $code = 0, ?\Throwable $previous = null)
	{
		$this->disconnectReason = $disconnectReason;
		parent::__construct($message, $code, $previous);
	}
	/**
	 * @phpstan-return DisconnectReason::*
	 */
	public function getDisconnectReason() : int
	{
		return $this->disconnectReason;
	}
}
