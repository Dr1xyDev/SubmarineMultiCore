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

use function define;
use function defined;
use function dirname;

// composer autoload doesn't use require_once and also pmmpthread can inherit things
if (defined('pocketmine\_CORE_CONSTANTS_INCLUDED')) {
	return;
}
define('pocketmine\_CORE_CONSTANTS_INCLUDED', true);

define('pocketmine\PATH', dirname(__DIR__, 2) . '/');
define('pocketmine\RESOURCE_PATH', __DIR__ . '/resources/');
define('pocketmine\BEDROCK_DATA_PATH', __DIR__ . '/resources/vanilla/');
define('pocketmine\COMPOSER_AUTOLOADER_PATH', dirname(__DIR__, 2) . '/vendor/autoload.php');
define('pocketmine\BEDROCK_BLOCK_UPGRADE_SCHEMA_PATH', dirname(__DIR__, 2) . '/vendor/pocketmine/bedrock-block-upgrade-schema/');
