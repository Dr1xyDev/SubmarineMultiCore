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

namespace pocketmine\network\mcpe\protocol;

use pocketmine\network\mcpe\NetworkSession;

class ServerboundDiagnosticsPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::SERVERBOUND_DIAGNOSTICS_PACKET;

	private float $avgFps;
	private float $avgServerSimTickTimeMS;
	private float $avgClientSimTickTimeMS;
	private float $avgBeginFrameTimeMS;
	private float $avgInputTimeMS;
	private float $avgRenderTimeMS;
	private float $avgEndFrameTimeMS;
	private float $avgRemainderTimePercent;
	private float $avgUnaccountedTimePercent;

	/**
	 * @generate-create-func
	 */
	public static function create(
		float $avgFps,
		float $avgServerSimTickTimeMS,
		float $avgClientSimTickTimeMS,
		float $avgBeginFrameTimeMS,
		float $avgInputTimeMS,
		float $avgRenderTimeMS,
		float $avgEndFrameTimeMS,
		float $avgRemainderTimePercent,
		float $avgUnaccountedTimePercent,
	) : self {
		$result = new self();
		$result->avgFps = $avgFps;
		$result->avgServerSimTickTimeMS = $avgServerSimTickTimeMS;
		$result->avgClientSimTickTimeMS = $avgClientSimTickTimeMS;
		$result->avgBeginFrameTimeMS = $avgBeginFrameTimeMS;
		$result->avgInputTimeMS = $avgInputTimeMS;
		$result->avgRenderTimeMS = $avgRenderTimeMS;
		$result->avgEndFrameTimeMS = $avgEndFrameTimeMS;
		$result->avgRemainderTimePercent = $avgRemainderTimePercent;
		$result->avgUnaccountedTimePercent = $avgUnaccountedTimePercent;
		return $result;
	}

	public function getAvgFps() : float
	{
		return $this->avgFps;
	}

	public function getAvgServerSimTickTimeMS() : float
	{
		return $this->avgServerSimTickTimeMS;
	}

	public function getAvgClientSimTickTimeMS() : float
	{
		return $this->avgClientSimTickTimeMS;
	}

	public function getAvgBeginFrameTimeMS() : float
	{
		return $this->avgBeginFrameTimeMS;
	}

	public function getAvgInputTimeMS() : float
	{
		return $this->avgInputTimeMS;
	}

	public function getAvgRenderTimeMS() : float
	{
		return $this->avgRenderTimeMS;
	}

	public function getAvgEndFrameTimeMS() : float
	{
		return $this->avgEndFrameTimeMS;
	}

	public function getAvgRemainderTimePercent() : float
	{
		return $this->avgRemainderTimePercent;
	}

	public function getAvgUnaccountedTimePercent() : float
	{
		return $this->avgUnaccountedTimePercent;
	}

	protected function decodePayload() : void
	{
		$this->avgFps = $this->getLFloat();
		$this->avgServerSimTickTimeMS = $this->getLFloat();
		$this->avgClientSimTickTimeMS = $this->getLFloat();
		$this->avgBeginFrameTimeMS = $this->getLFloat();
		$this->avgInputTimeMS = $this->getLFloat();
		$this->avgRenderTimeMS = $this->getLFloat();
		$this->avgEndFrameTimeMS = $this->getLFloat();
		$this->avgRemainderTimePercent = $this->getLFloat();
		$this->avgUnaccountedTimePercent = $this->getLFloat();
	}

	protected function encodePayload() : void
	{
		$this->putLFloat($this->avgFps);
		$this->putLFloat($this->avgServerSimTickTimeMS);
		$this->putLFloat($this->avgClientSimTickTimeMS);
		$this->putLFloat($this->avgBeginFrameTimeMS);
		$this->putLFloat($this->avgInputTimeMS);
		$this->putLFloat($this->avgRenderTimeMS);
		$this->putLFloat($this->avgEndFrameTimeMS);
		$this->putLFloat($this->avgRemainderTimePercent);
		$this->putLFloat($this->avgUnaccountedTimePercent);
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleServerboundDiagnostics($this);
	}
}
