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

$ext = ".json";
$extLen = strlen($ext);
foreach(scandir(__DIR__, SCANDIR_SORT_NONE) as $file){
	if(substr($file, -$extLen) === $ext){
		$file = __DIR__ . "/$file";
		file_put_contents($file, json_encode(json_decode(file_get_contents($file)), JSON_PRETTY_PRINT));
		echo "Minified JSON file $file\n";
	}
}
