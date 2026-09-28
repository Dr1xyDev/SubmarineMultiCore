#!/usr/bin/env bash
# Submarine server software for Minecraft: Bedrock Edition
#
# Usage: ./start.sh [-p php binary] [-f server file] [-l] [-- server arguments]
#   -p  PHP binary to use (default: ./bin/php7/bin/php, then php from PATH)
#   -f  server file (default: Submarine.phar / PocketMine-MP.phar, then src/pocketmine/PocketMine.php)
#   -l  restart the server automatically when it stops
DIR="$(cd -P "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$DIR" || exit 1

PHP_BINARY=""
POCKETMINE_FILE=""
DO_LOOP="no"

while getopts "p:f:l" OPTION 2> /dev/null; do
	case ${OPTION} in
		p)
			PHP_BINARY="$OPTARG"
			;;
		f)
			POCKETMINE_FILE="$OPTARG"
			;;
		l)
			DO_LOOP="yes"
			;;
		\?)
			break
			;;
	esac
done
shift $((OPTIND - 1))

if [ "$PHP_BINARY" == "" ]; then
	if [ -f ./bin/php7/bin/php ]; then
		# use the php.ini next to the bundled binary, not one from the PHPRC environment variable
		export PHPRC=""
		PHP_BINARY="./bin/php7/bin/php"
	elif [[ -n $(command -v php) ]]; then
		PHP_BINARY=$(command -v php)
	else
		echo "Couldn't find a PHP binary in ./bin/php7/bin or in PATH."
		echo "Download the Linux build for PocketMine-MP 5 from https://github.com/pmmp/PHP-Binaries/releases"
		echo "and unpack it into $DIR so that ./bin/php7/bin/php exists."
		exit 1
	fi
fi

if [ "$POCKETMINE_FILE" == "" ]; then
	if [ -f ./Submarine.phar ]; then
		POCKETMINE_FILE="./Submarine.phar"
	elif [ -f ./PocketMine-MP.phar ]; then
		POCKETMINE_FILE="./PocketMine-MP.phar"
	elif [ -f ./src/pocketmine/PocketMine.php ]; then
		if [ ! -f ./vendor/autoload.php ]; then
			echo "Composer dependencies are missing: vendor/autoload.php not found."
			echo "Run \"composer install --no-dev\" in $DIR first."
			exit 1
		fi
		POCKETMINE_FILE="./src/pocketmine/PocketMine.php"
	else
		echo "Couldn't find Submarine.phar or src/pocketmine/PocketMine.php in $DIR"
		exit 1
	fi
fi

if [ "$DO_LOOP" == "yes" ]; then
	LOOPS=0
	while true; do
		if [ ${LOOPS} -gt 0 ]; then
			echo "Restarted $LOOPS times"
		fi
		"$PHP_BINARY" "$POCKETMINE_FILE" "$@"
		echo "To escape the loop, press CTRL+C now. Otherwise, wait 5 seconds for the server to restart."
		echo ""
		sleep 5
		((LOOPS++))
	done
else
	exec "$PHP_BINARY" "$POCKETMINE_FILE" "$@"
fi
