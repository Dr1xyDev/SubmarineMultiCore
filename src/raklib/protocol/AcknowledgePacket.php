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

use pocketmine\utils\Binary;

use function chr;
use function count;
use function sort;

use const SORT_NUMERIC;

abstract class AcknowledgePacket extends Packet
{
	private const RECORD_TYPE_RANGE = 0;
	private const RECORD_TYPE_SINGLE = 1;

	/** @var int[] */
	public array $packets = [];

	protected function encodePayload(PacketSerializer $out) : void
	{
		$payload = "";
		sort($this->packets, SORT_NUMERIC);
		$count = count($this->packets);
		$records = 0;

		if ($count > 0) {
			$pointer = 1;
			$start = $this->packets[0];
			$last = $this->packets[0];

			while ($pointer < $count) {
				$current = $this->packets[$pointer++];
				$diff = $current - $last;
				if ($diff === 1) {
					$last = $current;
				} elseif ($diff > 1) { //Forget about duplicated packets (bad queues?)
					if ($start === $last) {
						$payload .= chr(self::RECORD_TYPE_SINGLE);
						$payload .= Binary::writeLTriad($start);
						$start = $last = $current;
					} else {
						$payload .= chr(self::RECORD_TYPE_RANGE);
						$payload .= Binary::writeLTriad($start);
						$payload .= Binary::writeLTriad($last);
						$start = $last = $current;
					}
					++$records;
				}
			}

			if ($start === $last) {
				$payload .= chr(self::RECORD_TYPE_SINGLE);
				$payload .= Binary::writeLTriad($start);
			} else {
				$payload .= chr(self::RECORD_TYPE_RANGE);
				$payload .= Binary::writeLTriad($start);
				$payload .= Binary::writeLTriad($last);
			}
			++$records;
		}

		$out->putShort($records);
		$out->put($payload);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$count = $in->getShort();
		$this->packets = [];
		$cnt = 0;
		for ($i = 0; $i < $count && !$in->feof() && $cnt < 4096; ++$i) {
			if ($in->getByte() === self::RECORD_TYPE_RANGE) {
				$start = $in->getLTriad();
				$end = $in->getLTriad();
				if (($end - $start) > 512) {
					$end = $start + 512;
				}
				for ($c = $start; $c <= $end; ++$c) {
					$this->packets[$cnt++] = $c;
				}
			} else {
				$this->packets[$cnt++] = $in->getLTriad();
			}
		}
	}
}
