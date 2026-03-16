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

namespace pocketmine;

use pocketmine\nbt\tag\CompoundTag;

class OfflinePlayer implements IPlayer
{
	private string $name;
	private Server $server;
	private ?CompoundTag $namedtag = null;

	public function __construct(Server $server, string $name)
	{
		$this->server = $server;
		$this->name = $name;
		if ($this->server->hasOfflinePlayerData($this->name)) {
			$this->namedtag = $this->server->getOfflinePlayerData($this->name);
		}
	}

	public function isOnline() : bool
	{
		return $this->getPlayer() !== null;
	}

	public function getName() : string
	{
		return $this->name;
	}

	/**
	 * @return Server
	 */
	public function getServer()
	{
		return $this->server;
	}

	public function isOp() : bool
	{
		return $this->server->isOp($this->name);
	}

	public function setOp(bool $value)
	{
		if ($value === $this->isOp()) {
			return;
		}

		if ($value) {
			$this->server->addOp($this->name);
		} else {
			$this->server->removeOp($this->name);
		}
	}

	public function isBanned() : bool
	{
		return $this->server->getNameBans()->isBanned($this->name);
	}

	public function setBanned(bool $value)
	{
		if ($value) {
			$this->server->getNameBans()->addBan($this->name, null, null, null);
		} else {
			$this->server->getNameBans()->remove($this->name);
		}
	}

	public function isWhitelisted() : bool
	{
		return $this->server->isWhitelisted($this->name);
	}

	public function setWhitelisted(bool $value)
	{
		if ($value) {
			$this->server->addWhitelist($this->name);
		} else {
			$this->server->removeWhitelist($this->name);
		}
	}

	public function getPlayer()
	{
		return $this->server->getPlayerExact($this->name);
	}

	public function getFirstPlayed() : int
	{
		return $this->namedtag instanceof CompoundTag ? $this->namedtag->getLong("firstPlayed", 0, true) : 0;
	}

	public function getLastPlayed() : int
	{
		return $this->namedtag instanceof CompoundTag ? $this->namedtag->getLong("lastPlayed", 0, true) : 0;
	}

	public function hasPlayedBefore() : bool
	{
		return $this->namedtag instanceof CompoundTag;
	}
}
