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

namespace raklib\server;

use raklib\protocol\EncapsulatedPacket;

interface ServerInterface
{
	public function sendEncapsulated(int $sessionId, EncapsulatedPacket $packet, bool $immediate = false) : void;

	public function sendRaw(string $address, int $port, string $payload) : void;

	public function closeSession(int $sessionId) : void;

	public function setName(string $name) : void;

	public function setPortCheck(bool $value) : void;

	public function setPacketsPerTickLimit(int $limit) : void;

	public function blockAddress(string $address, int $timeout) : void;

	public function unblockAddress(string $address) : void;

	public function addRawPacketFilter(string $regex) : void;
}
