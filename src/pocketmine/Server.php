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

use pmmp\thread\Thread as NativeThread;
use pmmp\thread\ThreadSafeArray;
use pocketmine\block\BlockFactory;
use pocketmine\command\CommandReader;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\command\SimpleCommandMap;
use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\event\HandlerList;
use pocketmine\event\level\LevelInitEvent;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\player\PlayerDataSaveEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\event\server\DataPacketBroadcastEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\event\server\ServerCommandEvent;
use pocketmine\inventory\CraftingManager;
use pocketmine\inventory\CraftingManagerFromDataHelper;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\ItemFactory;
use pocketmine\lang\BaseLang;
use pocketmine\lang\TextContainer;
use pocketmine\level\biome\Biome;
use pocketmine\level\format\Chunk;
use pocketmine\level\format\io\FormatConverter;
use pocketmine\level\format\io\LevelProvider;
use pocketmine\level\format\io\LevelProviderManager;
use pocketmine\level\format\io\WritableLevelProvider;
use pocketmine\level\format\io\WritableLevelProviderManagerEntry;
use pocketmine\level\generator\end\End;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\GeneratorManager;
use pocketmine\level\generator\hell\Nether;
use pocketmine\level\Level;
use pocketmine\level\LevelCreationOptions;
use pocketmine\level\LevelException;
use pocketmine\maps\MapManager;
use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\compression\CompressBatchPromise;
use pocketmine\network\mcpe\compression\CompressBatchTask;
use pocketmine\network\mcpe\compression\NetworkCompression;
use pocketmine\network\mcpe\convert\PacketIdTranslator;
use pocketmine\network\mcpe\encryption\EncryptionContext;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\serializer\PacketBatch;
use pocketmine\network\mcpe\protocol\types\DisconnectFailReason;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\raklib\RakLibInterface;
use pocketmine\network\Network;
use pocketmine\network\query\DedicatedQueryNetworkInterface;
use pocketmine\network\query\QueryHandler;
use pocketmine\network\rcon\RCON;
use pocketmine\network\upnp\UPnPNetworkInterface;
use pocketmine\permission\BanList;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\FolderPluginLoader;
use pocketmine\plugin\PharPluginLoader;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginLoadOrder;
use pocketmine\plugin\PluginManager;
use pocketmine\plugin\ScriptPluginLoader;
use pocketmine\resourcepacks\ResourcePackManager;
use pocketmine\scheduler\AsyncPool;
use pocketmine\snooze\SleeperHandler;
use pocketmine\snooze\SleeperNotifier;
use pocketmine\thread\log\AttachableThreadSafeLogger;
use pocketmine\thread\ThreadCrashException;
use pocketmine\thread\ThreadSafeClassLoader;
use pocketmine\tile\Tile;
use pocketmine\timings\Timings;
use pocketmine\timings\TimingsHandler;
use pocketmine\utils\BinaryStream;
use pocketmine\utils\Color;
use pocketmine\utils\Config;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Internet;
use pocketmine\utils\MainLogger;
use pocketmine\utils\Process;
use pocketmine\utils\SignalHandler;
use pocketmine\utils\Terminal;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use pocketmine\utils\UUID;
use Symfony\Component\Filesystem\Path;

use function array_fill;
use function array_filter;
use function array_key_exists;
use function array_shift;
use function array_sum;
use function asort;
use function assert;
use function base64_encode;
use function cli_set_process_title;
use function count;
use function explode;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function get_class;
use function getopt;
use function gettype;
use function ini_set;
use function is_array;
use function is_bool;
use function is_dir;
use function is_int;
use function is_object;
use function is_string;
use function json_decode;
use function log;
use function max;
use function microtime;
use function min;
use function mkdir;
use function ob_end_flush;
use function preg_replace;
use function random_bytes;
use function realpath;
use function register_shutdown_function;
use function rename;
use function round;
use function scandir;
use function sleep;
use function spl_object_hash;
use function sprintf;
use function str_repeat;
use function str_replace;
use function stripos;
use function strlen;
use function strrpos;
use function strtolower;
use function substr;
use function time;
use function touch;
use function trim;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;
use const PHP_INT_MAX;
use const SCANDIR_SORT_NONE;

/**
 * The class that manages everything
 */
class Server
{
	public const BROADCAST_CHANNEL_ADMINISTRATIVE = "pocketmine.broadcast.admin";
	public const BROADCAST_CHANNEL_USERS = "pocketmine.broadcast.user";

	public const DEFAULT_SERVER_NAME = VersionInfo::NAME . " Server";
	public const DEFAULT_MAX_PLAYERS = 20;
	public const DEFAULT_PORT_IPV4 = 19132;
	public const DEFAULT_PORT_IPV6 = 19133;
	public const DEFAULT_MAX_VIEW_DISTANCE = 16;

	/**
	 * Worlds, network, commands and most other things are polled this many times per second on average.
	 * Between ticks, the server will sleep to ensure that the average tick rate is maintained.
	 * It may wake up between ticks if a Snooze notification source is triggered (e.g. to process network packets).
	 */
	public const TARGET_TICKS_PER_SECOND = 20;
	/**
	 * The average time between ticks, in seconds.
	 */
	public const TARGET_SECONDS_PER_TICK = 1 / self::TARGET_TICKS_PER_SECOND;
	public const TARGET_NANOSECONDS_PER_TICK = 1_000_000_000 / self::TARGET_TICKS_PER_SECOND;

	/**
	 * The TPS threshold below which the server will generate log warnings.
	 */
	private const TPS_OVERLOAD_WARNING_THRESHOLD = self::TARGET_TICKS_PER_SECOND * 0.6;

	private const TICKS_PER_WORLD_CACHE_CLEAR = 5 * self::TARGET_TICKS_PER_SECOND;
	private const TICKS_PER_TPS_OVERLOAD_WARNING = 5 * self::TARGET_TICKS_PER_SECOND;

	/** @var Server */
	private static $instance = null;

	/** @var SleeperHandler */
	private $tickSleeper;

	private static ?ThreadSafeArray $sleeper = null;

	/** @var BanList */
	private $banByName = null;

	/** @var BanList */
	private $banByIP = null;

	/** @var Config */
	private $operators = null;

	/** @var Config */
	private $whitelist = null;

	/** @var bool */
	private $isRunning = true;

	/** @var bool */
	private $hasStopped = false;

	/** @var PluginManager */
	private $pluginManager = null;

	/** @var float */
	private $profilingTickRate = 20;

	/** @var AsyncPool */
	private $asyncPool;

	/** Counts the ticks since the server start */
	private int $tickCounter = 0;
	private float $nextTick = 0;
	/** @var float[] */
	private array $tickAverage;
	/** @var float[] */
	private array $useAverage;
	private float $currentTPS = self::TARGET_TICKS_PER_SECOND;
	private float $currentUse = 0;
	private float $startTime;

	/** @var bool */
	private $doTitleTick = true;

	/** @var MemoryManager */
	private $memoryManager;

	private ?CommandReader $console = null;
	private ConsoleCommandSender $consoleSender;

	private SimpleCommandMap $commandMap;
	private CraftingManager $craftingManager;

	/** @var ResourcePackManager */
	private $pw10ResourcePackManager;
	/** @var ResourcePackManager */
	private $bedrockResourcePackManager;

	/** @var int */
	private $bedrockResourcePacksProtocol = ProtocolInfo::PROTOCOL_370;

	/** @var int */
	private $maxPlayers;

	/** @var bool */
	private $onlineMode = true;

	/** @var bool */
	private $autoSave;

	/** @var RCON */
	private $rcon;

	/** @var Network */
	private $network;
	/** @var bool */
	private $networkCompressionAsync = true;
	/** @var int */
	public $networkCompressionLevel = 7;

	/** @var int */
	private $autoSaveTicker = 0;
	/** @var int */
	private $autoSaveTicks = 6000;

	/** @var BaseLang */
	private $baseLang;
	/** @var bool */
	private $forceLanguage = false;

	/** @var UUID */
	private $serverID;

	/** @var string */
	private $dataPath;
	/** @var string */
	private $pluginPath;

	/** @var string[] */
	private $uniquePlayers = [];

	/** @var QueryHandler */
	private $queryHandler;

	/** @var QueryRegenerateEvent */
	private $queryRegenerateTask = null;

	/** @var Config */
	private $properties;
	/** @var mixed[] */
	private $propertyCache = [];

	/** @var mixed[] */
	private $submarinePropertyCache = [];

	/** @var Config */
	private $config;

	/** @var Config */
	private $submarineConfig;

	/** @var Player[] */
	private $players = [];

	/** @var Player[] */
	private $loggedInPlayers = [];

	/** @var Player[] */
	private $playerList = [];

	private SignalHandler $signalHandler;

	/** @var Level[] */
	private $levels = [];

	/** @var Level */
	private $levelDefault = null;

	/** @var Level */
	private $levelNether = null;
	/** @var Level */
	private $levelTheEnd = null;

	/** @var bool */
	public $keepInventory = false;
	/** @var bool */
	public $keepExperience = false;
	/** @var bool */
	public $folderPluginLoader = true;
	/** @var bool */
	public $mobAiEnabled = true;
	/** @var bool */
	public $internalErrorKick = false;
	/** @var bool */
	public $commandFix = false;
	/** @var bool */
	private $customUnknownCommandMessage = false;

	/** @var bool */
	public $deleteSpacesForNickname = true;
	/** @var string */
	public $replaceSpacesNickname = "_";

	public function loadSubmarineConfig() : void
	{
		$this->deleteSpacesForNickname = (bool) $this->getSubmarineProperty("player.nickname.delete-spaces", true);
		$this->replaceSpacesNickname = $this->getSubmarineProperty("player.nickname.replace-spaces", "_");
		$this->internalErrorKick = $this->getSubmarineProperty("developer.internal-server-error-kick", false);
		$this->customUnknownCommandMessage = $this->getSubmarineProperty("general.custom-unknown-command-message.enabled", false);
		$this->commandFix = $this->getSubmarineProperty("general.command-fix", false);
		$this->keepInventory = $this->getSubmarineProperty("player.keep-inventory", false);
		$this->keepExperience = $this->getSubmarineProperty("player.keep-experience", false);
		$this->folderPluginLoader = $this->getSubmarineProperty("developer.folder-plugin-loader", true);
		$this->mobAiEnabled = $this->getSubmarineProperty("level.enable-mob-ai", false);
	}

	public function getName() : string
	{
		return VersionInfo::NAME;
	}

	public function isRunning() : bool
	{
		return $this->isRunning;
	}

	public function getPocketMineVersion() : string
	{
		return VersionInfo::VERSION()->getFullVersion(true);
	}

	public function getSubmarineVersion() : string
	{
		return VersionInfo::FORK_VERSION;
	}

	public function getVersion() : string
	{
		return ProtocolInfo::MINECRAFT_VERSION;
	}

	public function getApiVersion() : string
	{
		return VersionInfo::BASE_VERSION;
	}

	public function getFilePath() : string
	{
		return \pocketmine\PATH;
	}

	public function getResourcePath() : string
	{
		return \pocketmine\RESOURCE_PATH;
	}

	public function getDataPath() : string
	{
		return $this->dataPath;
	}

	public function getPluginPath() : string
	{
		return $this->pluginPath;
	}

	public function getMaxPlayers() : int
	{
		return $this->maxPlayers;
	}

	/**
	 * Returns whether the server requires that players be authenticated to Xbox Live. If true, connecting players who
	 * are not logged into Xbox Live will be disconnected.
	 */
	public function getOnlineMode() : bool
	{
		return $this->onlineMode;
	}

	/**
	 * Alias of {@link #getOnlineMode()}.
	 */
	public function requiresAuthentication() : bool
	{
		return $this->getOnlineMode();
	}

	public function getPort() : int
	{
		return $this->getConfigInt("server-port", 19132);
	}

	public function getViewDistance() : int
	{
		return max(2, $this->getConfigInt("view-distance", 8));
	}

	/**
	 * Returns a view distance up to the currently-allowed limit.
	 */
	public function getAllowedViewDistance(int $distance) : int
	{
		return max(2, min($distance, $this->memoryManager->getViewDistance($this->getViewDistance())));
	}

	public function getIp() : string
	{
		$str = $this->getConfigString("server-ip");
		return $str !== "" ? $str : "0.0.0.0";
	}

	/**
	 * @return UUID
	 */
	public function getServerUniqueId()
	{
		return $this->serverID;
	}

	public function getAutoSave() : bool
	{
		return $this->autoSave;
	}

	/**
	 * @return void
	 */
	public function setAutoSave(bool $value)
	{
		$this->autoSave = $value;
		foreach ($this->getLevels() as $level) {
			$level->setAutoSave($this->autoSave);
		}
	}

	public function getLevelType() : string
	{
		return $this->getConfigString("level-type", "DEFAULT");
	}

	public function getGenerateStructures() : bool
	{
		return $this->getConfigBool("generate-structures", true);
	}

	public function getGamemode() : int
	{
		return $this->getConfigInt("gamemode", 0) & 0b11;
	}

	public function getForceGamemode() : bool
	{
		return $this->getConfigBool("force-gamemode", false);
	}

	/**
	 * Returns the gamemode text name
	 */
	public static function getGamemodeString(int $mode) : string
	{
		switch ($mode) {
			case Player::SURVIVAL:
				return "%gameMode.survival";
			case Player::CREATIVE:
				return "%gameMode.creative";
			case Player::ADVENTURE:
				return "%gameMode.adventure";
			case Player::SPECTATOR:
				return "%gameMode.spectator";
		}

		return "UNKNOWN";
	}

	public static function getGamemodeName(int $mode) : string
	{
		switch ($mode) {
			case Player::SURVIVAL:
				return "Survival";
			case Player::CREATIVE:
				return "Creative";
			case Player::ADVENTURE:
				return "Adventure";
			case Player::SPECTATOR:
				return "Spectator";
			default:
				throw new \InvalidArgumentException("Invalid gamemode $mode");
		}
	}

	/**
	 * Parses a string and returns a gamemode integer, -1 if not found
	 */
	public static function getGamemodeFromString(string $str) : int
	{
		switch (strtolower(trim($str))) {
			case (string) Player::SURVIVAL:
			case "survival":
			case "s":
				return Player::SURVIVAL;

			case (string) Player::CREATIVE:
			case "creative":
			case "c":
				return Player::CREATIVE;

			case (string) Player::ADVENTURE:
			case "adventure":
			case "a":
				return Player::ADVENTURE;

			case (string) Player::SPECTATOR:
			case "spectator":
			case "view":
			case "v":
				return Player::SPECTATOR;
		}
		return -1;
	}

	/**
	 * Returns Server global difficulty. Note that this may be overridden in individual Levels.
	 */
	public function getDifficulty() : int
	{
		return $this->getConfigInt("difficulty", Level::DIFFICULTY_NORMAL);
	}

	public function hasWhitelist() : bool
	{
		return $this->getConfigBool("white-list", false);
	}

	/**
	 * @deprecated
	 */
	public function getAllowFlight() : bool
	{
		return true;
	}

	public function isHardcore() : bool
	{
		return $this->getConfigBool("hardcore", false);
	}

	public function getDefaultGamemode() : int
	{
		return $this->getConfigInt("gamemode", 0) & 0b11;
	}

	public function getMotd() : string
	{
		return $this->getConfigString("motd", VersionInfo::NAME . " Server");
	}

	public function getLoader() : ThreadSafeClassLoader
	{
		return $this->autoloader;
	}

	public function getLogger() : AttachableThreadSafeLogger
	{
		return $this->logger;
	}

	public function getPluginManager() : PluginManager{
		return $this->pluginManager;
	}

	public function getCraftingManager() : CraftingManager{
		return $this->craftingManager;
	}

	public function getPw10ResourcePackManager() : ResourcePackManager
	{
		return $this->pw10ResourcePackManager;
	}

	public function getBedrockResourcePackManager() : ResourcePackManager
	{
		return $this->bedrockResourcePackManager;
	}

	public function getResourcePackManager(int $playerProtocol) : ResourcePackManager
	{
		return ($playerProtocol >= $this->bedrockResourcePacksProtocol) ? $this->bedrockResourcePackManager : $this->pw10ResourcePackManager;
	}

	public function getBedrockResourcePacksProtocol() : int
	{
		return $this->bedrockResourcePacksProtocol;
	}

	public function getAsyncPool() : AsyncPool
	{
		return $this->asyncPool;
	}

	public function getTick() : int
	{
		return $this->tickCounter;
	}

	/**
	 * Returns the last server TPS measure
	 */
	public function getTicksPerSecond() : float
	{
		return round($this->currentTPS, 2);
	}

	/**
	 * Returns the last server TPS average measure
	 */
	public function getTicksPerSecondAverage() : float
	{
		return round(array_sum($this->tickAverage) / count($this->tickAverage), 2);
	}

	/**
	 * Returns the TPS usage/load in %
	 */
	public function getTickUsage() : float
	{
		return round($this->currentUse * 100, 2);
	}

	/**
	 * Returns the TPS usage/load average in %
	 */
	public function getTickUsageAverage() : float
	{
		return round((array_sum($this->useAverage) / count($this->useAverage)) * 100, 2);
	}

	public function getStartTime() : float
	{
		return $this->startTime;
	}

	public function getCommandMap() : SimpleCommandMap
	{
		return $this->commandMap;
	}

	/**
	 * @return Player[]
	 */
	public function getLoggedInPlayers() : array
	{
		return $this->loggedInPlayers;
	}

	/**
	 * @return Player[]
	 */
	public function getOnlinePlayers() : array
	{
		return $this->playerList;
	}

	public function shouldSavePlayerData() : bool
	{
		return (bool) $this->getProperty("player.save-player-data", true);
	}

	/**
	 * @return OfflinePlayer|Player
	 */
	public function getOfflinePlayer(string $name)
	{
		$name = strtolower($name);
		$result = $this->getPlayerExact($name);

		if ($result === null) {
			$result = new OfflinePlayer($this, $name);
		}

		return $result;
	}

	private function getPlayerDataPath(string $username) : string
	{
		return $this->getDataPath() . '/players/' . strtolower($username) . '.dat';
	}

	/**
	 * /**
	 * Returns whether the server has stored any saved data for this player.
	 */
	public function hasOfflinePlayerData(string $name) : bool
	{
		return file_exists($this->getPlayerDataPath($name));
	}

	public function getOfflinePlayerData(string $name) : CompoundTag
	{
		return Timings::$syncPlayerDataLoad->time(function () use ($name) : ?CompoundTag {
			$name = strtolower($name);
			$path = $this->getPlayerDataPath($name);
			if ($this->shouldSavePlayerData()) {
				if (file_exists($path)) {
					try {
						$nbt = new BigEndianNBTStream();
						$compound = $nbt->readCompressed(file_get_contents($path));
						if (!($compound instanceof CompoundTag)) {
							throw new \RuntimeException("Invalid data found in \"$name.dat\", expected " . CompoundTag::class . ", got " . (is_object($compound) ? get_class($compound) : gettype($compound)));
						}

						return $compound;
					} catch (\Throwable $e) { //zlib decode error / corrupt data
						rename($path, $path . '.bak');
						$this->logger->notice($this->getLanguage()->translateString("pocketmine.data.playerCorrupted", [$name]));
					}
				} else {
					$this->logger->notice($this->getLanguage()->translateString("pocketmine.data.playerNotFound", [$name]));
				}
			}
			$spawn = $this->getDefaultLevel()->getSpawnLocation();
			$currentTimeMillis = (int) (microtime(true) * 1000);

			return new CompoundTag("", [
				new LongTag("firstPlayed", $currentTimeMillis),
				new LongTag("lastPlayed", $currentTimeMillis),
				new ListTag("Pos", [
					new DoubleTag("", $spawn->x),
					new DoubleTag("", $spawn->y),
					new DoubleTag("", $spawn->z)
				], NBT::TAG_Double),
				new StringTag("Level", $this->getDefaultLevel()->getFolderName()),
				//new StringTag("SpawnLevel", $this->getDefaultLevel()->getFolderName()),
				//new IntTag("SpawnX", $spawn->getFloorX()),
				//new IntTag("SpawnY", $spawn->getFloorY()),
				//new IntTag("SpawnZ", $spawn->getFloorZ()),
				//new ByteTag("SpawnForced", 1), //TODO
				new ListTag("Inventory", [], NBT::TAG_Compound),
				new ListTag("EnderChestInventory", [], NBT::TAG_Compound),
				new CompoundTag("Achievements", []),
				new IntTag("playerGameType", $this->getGamemode()),
				new ListTag("Motion", [
					new DoubleTag("", 0.0),
					new DoubleTag("", 0.0),
					new DoubleTag("", 0.0)
				], NBT::TAG_Double),
				new ListTag("Rotation", [
					new FloatTag("", 0.0),
					new FloatTag("", 0.0)
				], NBT::TAG_Float),
				new FloatTag("FallDistance", 0.0),
				new ShortTag("Fire", 0),
				new ShortTag("Air", 300),
				new ByteTag("OnGround", 1),
				new ByteTag("Invulnerable", 0),
				new StringTag("NameTag", $name)
			]);
		});
	}

	public function saveOfflinePlayerData(string $name, CompoundTag $nbtTag) : void
	{
		$ev = new PlayerDataSaveEvent($nbtTag, $name);
		$ev->setCancelled(!$this->shouldSavePlayerData());

		$ev->call();

		if (!$ev->isCancelled()) {
			Timings::$syncPlayerDataSave->time(function () use ($name, $ev) : void {
				$nbt = new BigEndianNBTStream();
				try {
					file_put_contents($this->getPlayerDataPath($name), $nbt->writeCompressed($ev->getSaveData()));
				} catch (\Throwable $e) {
					$this->logger->critical($this->getLanguage()->translateString("pocketmine.data.saveError", [$name, $e->getMessage()]));
					$this->logger->logException($e);
				}
			});
		}
	}

	/**
	 * Returns an online player whose name begins with or equals the given string (case insensitive).
	 * The closest match will be returned, or null if there are no online matches.
	 *
	 * @return Player|null
	 * @see Server::getPlayerExact()
	 */
	public function getPlayer(string $name)
	{
		$found = null;
		$name = strtolower($name);
		$delta = PHP_INT_MAX;
		foreach ($this->getOnlinePlayers() as $player) {
			if (stripos($player->getName(), $name) === 0) {
				$curDelta = strlen($player->getName()) - strlen($name);
				if ($curDelta < $delta) {
					$found = $player;
					$delta = $curDelta;
				}
				if ($curDelta === 0) {
					break;
				}
			}
		}

		return $found;
	}

	/**
	 * Returns an online player with the given name (case insensitive), or null if not found.
	 *
	 * @return Player|null
	 */
	public function getPlayerExact(string $name)
	{
		$name = strtolower($name);
		foreach ($this->getOnlinePlayers() as $player) {
			if ($player->getLowerCaseName() === $name) {
				return $player;
			}
		}

		return null;
	}

	/**
	 * Returns a list of online players whose names contain with the given string (case insensitive).
	 * If an exact match is found, only that match is returned.
	 *
	 * @return Player[]
	 */
	public function matchPlayer(string $partialName) : array
	{
		$partialName = strtolower($partialName);
		$matchedPlayers = [];
		foreach ($this->getOnlinePlayers() as $player) {
			if ($player->getLowerCaseName() === $partialName) {
				$matchedPlayers = [$player];
				break;
			} elseif (stripos($player->getName(), $partialName) !== false) {
				$matchedPlayers[] = $player;
			}
		}

		return $matchedPlayers;
	}

	/**
	 * Returns the player online with the specified raw UUID, or null if not found
	 */
	public function getPlayerByRawUUID(string $rawUUID) : ?Player
	{
		return $this->playerList[$rawUUID] ?? null;
	}

	/**
	 * Returns the player online with a UUID equivalent to the specified UUID object, or null if not found
	 */
	public function getPlayerByUUID(UUID $uuid) : ?Player
	{
		return $this->getPlayerByRawUUID($uuid->toBinary());
	}

	/**
	 * @return Level[]
	 */
	public function getLevels() : array
	{
		return $this->levels;
	}

	public function getDefaultLevel() : ?Level
	{
		return $this->levelDefault;
	}

	/**
	 * Sets the default level to a different level
	 * This won't change the level-name property,
	 * it only affects the server on runtime
	 */
	public function setDefaultLevel(?Level $level) : void
	{
		if ($level === null || ($this->isLevelLoaded($level->getFolderName()) && $level !== $this->levelDefault)) {
			$this->levelDefault = $level;
		}
	}

	public function getNetherLevel() : ?Level
	{
		return $this->levelNether;
	}

	/**
	 * Sets the nether level to a different level
	 * This won't change the level-name property,
	 * it only affects the server on runtime
	 */
	public function setNetherLevel(?Level $level) : void
	{
		if ($level === null || ($this->isLevelLoaded($level->getFolderName()) && $level !== $this->levelNether)) {
			$this->levelNether = $level;
		}
	}

	public function getTheEndLevel() : ?Level
	{
		return $this->levelTheEnd;
	}

	/**
	 * Sets the the end level to a different level
	 * This won't change the level-name property,
	 * it only affects the server on runtime
	 */
	public function setTheEndLevel(?Level $level) : void
	{
		if ($level === null || ($this->isLevelLoaded($level->getFolderName()) && $level !== $this->levelTheEnd)) {
			$this->levelTheEnd = $level;
		}
	}

	public function isLevelLoaded(string $name) : bool
	{
		return $this->getLevelByName($name) instanceof Level;
	}

	public function getLevel(int $levelId) : ?Level
	{
		return $this->levels[$levelId] ?? null;
	}

	/**
	 * NOTE: This matches levels based on the FOLDER name, NOT the display name.
	 */
	public function getLevelByName(string $name) : ?Level
	{
		foreach ($this->getLevels() as $level) {
			if ($level->getFolderName() === $name) {
				return $level;
			}
		}

		return null;
	}

	/**
	 * @throws \InvalidStateException
	 */
	public function unloadLevel(Level $level, bool $forceUnload = false) : bool
	{
		if ($level === $this->getDefaultLevel() && !$forceUnload) {
			throw new \InvalidStateException("The default world cannot be unloaded while running, please switch worlds.");
		}

		return $level->unload($forceUnload);
	}

	/**
	 * @internal
	 */
	public function removeLevel(Level $level) : void
	{
		unset($this->levels[$level->getId()]);
	}

	/**
	 * Loads a level from the data directory
	 *
	 * @throws LevelException
	 */
	public function loadLevel(string $name) : bool
	{
		if (trim($name) === "") {
			throw new LevelException("Invalid empty world name");
		}
		if ($this->isLevelLoaded($name)) {
			return true;
		} elseif (!$this->isLevelGenerated($name)) {
			$this->logger->notice($this->getLanguage()->translateString("pocketmine.level.notFound", [$name]));

			return false;
		}

		$path = $this->getDataPath() . "worlds/" . $name . "/";

		$providers = LevelProviderManager::getMatchingProviders($path);
		if (count($providers) !== 1) {
			$this->logger->error($this->getLanguage()->translateString("pocketmine.level.loadError", [$name, "Cannot identify format of world"]));
			return false;
		}
		$providerClass = array_shift($providers);

		try {
			$provider = $providerClass->fromPath($path);
		} catch (LevelException $e) {
			$this->logger->error($this->getLanguage()->translateString("pocketmine.level.loadError", [$name, $e->getMessage()]));
			return false;
		}

		if (!($provider instanceof WritableLevelProvider)) {
			$this->logger->notice($this->getLanguage()->translateString("pocketmine.level.conversion.start", [$name]));

			$converter = new FormatConverter($provider, LevelProviderManager::getDefault(), Path::join($this->dataPath, "backups", "worlds"), $this->logger);
			$provider = $converter->execute();

			$this->logger->notice($this->getLanguage()->translateString("pocketmine.level.conversion.finish", [$name, $converter->getBackupPath()]));
		}

		$level = new Level($this, $name, $provider, $this->getAsyncPool());

		$this->levels[$level->getId()] = $level;

		(new LevelLoadEvent($level))->call();

		return true;
	}

	/**
	 * Generates a new level if it does not exist
	 */
	public function generateLevel(string $name, LevelCreationOptions $options, bool $backgroundGeneration = true) : bool
	{
		if (trim($name) === "" || $this->isLevelGenerated($name)) {
			return false;
		}

		$providerEntry = LevelProviderManager::getDefault();

		$path = $this->getDataPath() . "worlds/" . $name . "/";
		$providerEntry->generate($path, $name, $options);

		/** @see LevelProvider::__construct() */
		$level = new Level($this, $name, $providerEntry->fromPath($path), $this->getAsyncPool());
		$this->levels[$level->getId()] = $level;

		(new LevelInitEvent($level))->call();

		(new LevelLoadEvent($level))->call();

		if ($backgroundGeneration) {
			$this->getLogger()->notice($this->getLanguage()->translateString("pocketmine.level.backgroundGeneration", [$name]));

			$spawnLocation = $level->getSpawnLocation();
			$centerX = $spawnLocation->getFloorX() >> Chunk::COORD_BIT_SIZE;
			$centerZ = $spawnLocation->getFloorZ() >> Chunk::COORD_BIT_SIZE;

			$order = [];

			for ($X = -3; $X <= 3; ++$X) {
				for ($Z = -3; $Z <= 3; ++$Z) {
					$distance = $X ** 2 + $Z ** 2;
					$chunkX = $X + $centerX;
					$chunkZ = $Z + $centerZ;
					$index = Level::chunkHash($chunkX, $chunkZ);
					$order[$index] = $distance;
				}
			}

			asort($order);

			foreach ($order as $index => $distance) {
				Level::getXZ($index, $chunkX, $chunkZ);
				$level->populateChunk($chunkX, $chunkZ, true);
			}
		}

		return true;
	}

	public function isLevelGenerated(string $name) : bool
	{
		if (trim($name) === "") {
			return false;
		}
		$path = $this->getDataPath() . "worlds/" . $name . "/";
		if (!($this->getLevelByName($name) instanceof Level)) {
			return is_dir($path) && count(array_filter(scandir($path, SCANDIR_SORT_NONE), function (string $v) : bool {
				return $v !== ".." && $v !== ".";
			})) > 0;
		}

		return true;
	}

	/**
	 * Searches all levels for the entity with the specified ID.
	 * Useful for tracking entities across multiple worlds without needing strong references.
	 *
	 * @param Level|null $expectedLevel @deprecated Level to look in first for the target
	 *
	 * @return Entity|null
	 */
	public function findEntity(int $entityId, Level $expectedLevel = null)
	{
		foreach ($this->levels as $level) {
			assert(!$level->isClosed());
			if (($entity = $level->getEntity($entityId)) instanceof Entity) {
				return $entity;
			}
		}

		return null;
	}

	/**
	 * @param mixed $defaultValue
	 *
	 * @return mixed
	 */
	public function getProperty(string $variable, $defaultValue = null)
	{
		if (!array_key_exists($variable, $this->propertyCache)) {
			$v = getopt("", ["$variable::"]);
			if (isset($v[$variable])) {
				$this->propertyCache[$variable] = $v[$variable];
			} else {
				$this->propertyCache[$variable] = $this->config->getNested($variable);
			}
		}

		return $this->propertyCache[$variable] ?? $defaultValue;
	}

	public function getSubmarineProperty(string $variable, $defaultValue = null)
	{
		if (!array_key_exists($variable, $this->submarinePropertyCache)) {
			$this->submarinePropertyCache[$variable] = $this->submarineConfig->getNested($variable);
		}

		return $this->submarinePropertyCache[$variable] ?? $defaultValue;
	}

	public function getConfigString(string $variable, string $defaultValue = "") : string
	{
		$v = getopt("", ["$variable::"]);
		if (isset($v[$variable])) {
			return (string) $v[$variable];
		}

		return $this->properties->exists($variable) ? (string) $this->properties->get($variable) : $defaultValue;
	}

	/**
	 * @return void
	 */
	public function setConfigString(string $variable, string $value)
	{
		$this->properties->set($variable, $value);
	}

	public function getConfigInt(string $variable, int $defaultValue = 0) : int
	{
		$v = getopt("", ["$variable::"]);
		if (isset($v[$variable])) {
			return (int) $v[$variable];
		}

		return $this->properties->exists($variable) ? (int) $this->properties->get($variable) : $defaultValue;
	}

	/**
	 * @return void
	 */
	public function setConfigInt(string $variable, int $value)
	{
		$this->properties->set($variable, $value);
	}

	public function getConfigBool(string $variable, bool $defaultValue = false) : bool
	{
		$v = getopt("", ["$variable::"]);
		if (isset($v[$variable])) {
			$value = $v[$variable];
		} else {
			$value = $this->properties->exists($variable) ? $this->properties->get($variable) : $defaultValue;
		}

		if (is_bool($value)) {
			return $value;
		}
		switch (strtolower($value)) {
			case "on":
			case "true":
			case "1":
			case "yes":
				return true;
		}

		return false;
	}

	/**
	 * @return void
	 */
	public function setConfigBool(string $variable, bool $value)
	{
		$this->properties->set($variable, $value ? "1" : "0");
	}

	/**
	 * @return PluginIdentifiableCommand|null
	 */
	public function getPluginCommand(string $name)
	{
		if (($command = $this->commandMap->getCommand($name)) instanceof PluginIdentifiableCommand) {
			return $command;
		} else {
			return null;
		}
	}

	/**
	 * @return BanList
	 */
	public function getNameBans()
	{
		return $this->banByName;
	}

	/**
	 * @return BanList
	 */
	public function getIPBans()
	{
		return $this->banByIP;
	}

	/**
	 * @return void
	 */
	public function addOp(string $name)
	{
		$this->operators->set(strtolower($name), true);

		if (($player = $this->getPlayerExact($name)) !== null) {
			$player->recalculatePermissions();
		}
		$this->operators->save();
	}

	/**
	 * @return void
	 */
	public function removeOp(string $name)
	{
		$this->operators->remove(strtolower($name));

		if (($player = $this->getPlayerExact($name)) !== null) {
			$player->recalculatePermissions();
		}
		$this->operators->save();
	}

	/**
	 * @return void
	 */
	public function addWhitelist(string $name)
	{
		$this->whitelist->set(strtolower($name), true);
		$this->whitelist->save();
	}

	public function removeWhitelist(string $name)
	{
		$this->whitelist->remove(strtolower($name));
		$this->whitelist->save();
	}

	public function isWhitelisted(string $name) : bool
	{
		return !$this->hasWhitelist() || $this->operators->exists($name, true) || $this->whitelist->exists($name, true);
	}

	public function isOp(string $name) : bool
	{
		return $this->operators->exists($name, true);
	}

	/**
	 * @return Config
	 */
	public function getWhitelisted()
	{
		return $this->whitelist;
	}

	/**
	 * @return Config
	 */
	public function getOps()
	{
		return $this->operators;
	}

	/**
	 * @return void
	 */
	public function reloadWhitelist()
	{
		$this->whitelist->reload();
	}

	/**
	 * @return string[][]
	 */
	public function getCommandAliases() : array
	{
		$section = $this->getProperty("aliases");
		$result = [];
		if (is_array($section)) {
			foreach ($section as $key => $value) {
				$commands = [];
				if (is_array($value)) {
					$commands = $value;
				} else {
					$commands[] = (string) $value;
				}

				$result[$key] = $commands;
			}
		}

		return $result;
	}

	public static function microSleep(int $microseconds) : void
	{
		if (self::$sleeper === null) {
			self::$sleeper = new ThreadSafeArray();
		}
		self::$sleeper->synchronized(function (int $ms) : void {
			Server::$sleeper->wait($ms);
		}, $microseconds);
	}

	public static function getInstance() : Server
	{
		if (self::$instance === null) {
			throw new \RuntimeException("Attempt to retrieve Server instance outside server thread");
		}
		return self::$instance;
	}

	public function __construct(
		private ThreadSafeClassLoader $autoloader,
		private AttachableThreadSafeLogger $logger,
		string $dataPath,
		string $pluginPath
	) {
		if (self::$instance !== null) {
			throw new \LogicException("Only one server instance can exist at once");
		}
		self::$instance = $this;
		$this->startTime = microtime(true);
		$this->tickAverage = array_fill(0, self::TARGET_TICKS_PER_SECOND, self::TARGET_TICKS_PER_SECOND);
		$this->useAverage = array_fill(0, self::TARGET_TICKS_PER_SECOND, 0);

		Timings::init();

		self::$sleeper = new ThreadSafeArray();
		$this->tickSleeper = new SleeperHandler();

		$this->signalHandler = new SignalHandler(function () : void {
			$this->logger->info("Received signal interrupt, stopping the server");
			$this->shutdown();
		});

		try {
			foreach ([
				$dataPath,
				$pluginPath,
				Path::join($dataPath, "worlds"),
				Path::join($dataPath, "players")
			] as $neededPath) {
				if (!file_exists($neededPath)) {
					mkdir($neededPath, 0777);
				}
			}

			$this->dataPath = realpath($dataPath) . DIRECTORY_SEPARATOR;
			$this->pluginPath = realpath($pluginPath) . DIRECTORY_SEPARATOR;

			$consoleNotifier = new SleeperNotifier();
			$this->console = new CommandReader($consoleNotifier);
			$this->tickSleeper->addNotifier($consoleNotifier, function () : void {
				$this->checkConsole();
			});
			$this->console->start(NativeThread::INHERIT_CONSTANTS);

			$this->logger->info("Loading server configuration");
			$pocketmineYmlPath = Path::join($this->dataPath, "pocketmine.yml");
			if (!file_exists($pocketmineYmlPath)) {
				$content = Filesystem::fileGetContents(Path::join(\pocketmine\RESOURCE_PATH, "pocketmine.yml"));
				if (VersionInfo::IS_DEVELOPMENT_BUILD) {
					$content = str_replace("preferred-channel: stable", "preferred-channel: beta", $content);
				}
				@file_put_contents($pocketmineYmlPath, $content);
			}
			$this->config = new Config($this->dataPath . "pocketmine.yml", Config::YAML, []);

			$submarineYmlPath = Path::join($this->dataPath, "submarine.yml");
			if (!file_exists($submarineYmlPath)) {
				$content = Filesystem::fileGetContents(Path::join(\pocketmine\RESOURCE_PATH, "submarine.yml"));
				@file_put_contents($submarineYmlPath, $content);
			}
			$this->submarineConfig = new Config($this->dataPath . "submarine.yml", Config::YAML, []);
			$this->loadSubmarineConfig();

			$this->properties = new Config($this->dataPath . "server.properties", Config::PROPERTIES, [
				"motd" => VersionInfo::NAME . " Server",
				"server-port" => 19132,
				"white-list" => false,
				"max-players" => self::DEFAULT_MAX_PLAYERS,
				"gamemode" => 0,
				"force-gamemode" => false,
				"hardcore" => false,
				"pvp" => true,
				"difficulty" => Level::DIFFICULTY_NORMAL,
				"generator-settings" => "",
				"level-name" => "world",
				"level-seed" => "",
				"level-type" => "DEFAULT",
				"enable-query" => true,
				"enable-rcon" => false,
				"rcon.password" => substr(base64_encode(random_bytes(20)), 3, 10),
				"auto-save" => true,
				"view-distance" => 8,
				"xbox-auth" => true,
				"language" => "eng"
			]);

			$debugLogLevel = (int) $this->getProperty("debug.level", 1);
			if ($this->logger instanceof MainLogger) {
				$this->logger->setLogDebug($debugLogLevel > 1);
			}

			$this->forceLanguage = (bool) $this->getProperty("settings.force-language", false);
			$this->baseLang = new BaseLang($this->getConfigString("language", $this->getProperty("settings.language", BaseLang::FALLBACK_LANGUAGE)));
			$this->logger->info($this->getLanguage()->translateString("language.selected", [$this->getLanguage()->getName(), $this->getLanguage()->getLang()]));

			if (VersionInfo::IS_DEVELOPMENT_BUILD) {
				if (!$this->getProperty("settings.enable-dev-builds", false)) {
					$this->logger->emergency($this->baseLang->translateString("pocketmine.server.devBuild.error1", [VersionInfo::NAME]));
					$this->logger->emergency($this->baseLang->translateString("pocketmine.server.devBuild.error2"));
					$this->logger->emergency($this->baseLang->translateString("pocketmine.server.devBuild.error3"));
					$this->logger->emergency($this->baseLang->translateString("pocketmine.server.devBuild.error4", ["settings.enable-dev-builds"]));
					$this->logger->emergency($this->baseLang->translateString("pocketmine.server.devBuild.error5", ["https://github.com/SubmarineTeam/Submarine"]));
					$this->forceShutdownExit();

					return;
				}

				$this->logger->warning(str_repeat("-", 40));
				$this->logger->warning($this->baseLang->translateString("pocketmine.server.devBuild.warning1", [VersionInfo::NAME]));
				$this->logger->warning($this->baseLang->translateString("pocketmine.server.devBuild.warning2"));
				$this->logger->warning($this->baseLang->translateString("pocketmine.server.devBuild.warning3"));
				$this->logger->warning(str_repeat("-", 40));
			}

			$this->memoryManager = new MemoryManager($this);

			$this->logger->info($this->getLanguage()->translateString("pocketmine.server.start", [TextFormat::AQUA . $this->getVersion() . TextFormat::RESET]));

			if (($poolSize = $this->getProperty("settings.async-workers", "auto")) === "auto") {
				$poolSize = 2;
				$processors = Utils::getCoreCount() - 2;

				if ($processors > 0) {
					$poolSize = max(1, $processors);
				}
			} else {
				$poolSize = max(1, (int) $poolSize);
			}

			$this->asyncPool = new AsyncPool($this, "Asynchronous", $poolSize, $this->autoloader, $this->logger);

			if ($this->getProperty("network.batch-threshold", 256) >= 0) {
				NetworkCompression::$THRESHOLD = (int) $this->getProperty("network.batch-threshold", 256);
			} else {
				NetworkCompression::$THRESHOLD = -1;
			}

			$this->networkCompressionLevel = (int) $this->getProperty("network.compression-level", 6);
			if ($this->networkCompressionLevel < 1 || $this->networkCompressionLevel > 9) {
				$this->logger->warning("Invalid network compression level $this->networkCompressionLevel set, setting to default 6");
				$this->networkCompressionLevel = 6;
			}
			$this->networkCompressionAsync = (bool) $this->getProperty("network.async-compression", true);

			EncryptionContext::$ENABLED = (bool) $this->getProperty("network.enable-encryption", true);

			$this->doTitleTick = ((bool) $this->getProperty("console.title-tick", true)) && Terminal::hasFormattingCodes();

			$this->operators = new Config($this->dataPath . "ops.txt", Config::ENUM);
			$this->whitelist = new Config($this->dataPath . "white-list.txt", Config::ENUM);

			$bannedTxt = Path::join($this->dataPath, "banned.txt");
			$bannedPlayersTxt = Path::join($this->dataPath, "banned-players.txt");
			if (file_exists($bannedTxt) && !file_exists($bannedPlayersTxt)) {
				@rename($bannedTxt, $bannedPlayersTxt);
			}
			@touch($bannedPlayersTxt);
			$this->banByName = new BanList($bannedPlayersTxt);
			$this->banByName->load();
			$bannedIpsTxt = Path::join($this->dataPath, "banned-ips.txt");
			@touch($bannedIpsTxt);
			$this->banByIP = new BanList($bannedIpsTxt);
			$this->banByIP->load();

			$this->maxPlayers = $this->getConfigInt("max-players", self::DEFAULT_MAX_PLAYERS);

			$this->onlineMode = $this->getConfigBool("xbox-auth", true);
			if ($this->onlineMode) {
				$this->logger->info($this->getLanguage()->translateString("pocketmine.server.auth.enabled"));
			} else {
				$this->logger->warning($this->getLanguage()->translateString("pocketmine.server.auth.disabled"));
				$this->logger->warning($this->getLanguage()->translateString("pocketmine.server.authWarning"));
				$this->logger->warning($this->getLanguage()->translateString("pocketmine.server.authProperty.disabled"));
			}

			if ($this->getConfigBool("hardcore", false) && $this->getDifficulty() < Level::DIFFICULTY_HARD) {
				$this->setConfigInt("difficulty", Level::DIFFICULTY_HARD);
			}

			@cli_set_process_title($this->getName() . " " . $this->getSubmarineVersion());

			$this->serverID = Utils::getMachineUniqueId($this->getIp() . $this->getPort());

			$this->logger->debug("Server unique id: " . $this->getServerUniqueId());
			$this->logger->debug("Machine unique id: " . Utils::getMachineUniqueId());

			$this->network = new Network($this->logger);
			$this->network->setName($this->getMotd());

			$this->logger->info($this->getLanguage()->translateString("pocketmine.server.info", [
				$this->getName(),
				(VersionInfo::IS_DEVELOPMENT_BUILD ? TextFormat::YELLOW : "") . $this->getSubmarineVersion() . TextFormat::RESET
			]));
			$this->logger->info($this->getName() . " is based on PocketMine-MP " . $this->getPocketMineVersion());
			$this->logger->info($this->getLanguage()->translateString("pocketmine.server.license", [$this->getName()]));

			TimingsHandler::setEnabled((bool) $this->getProperty("settings.enable-profiling", false));

			if ($this->getConfigBool("enable-rcon", false)) {
				try {
					$this->rcon = new RCON(
						$this,
						$this->getConfigString("rcon.password", ""),
						$this->getConfigInt("rcon.port", $this->getPort()),
						$this->getIp(),
						$this->getConfigInt("rcon.max-clients", 50)
					);
				} catch (\Exception $e) {
					$this->getLogger()->critical("RCON can't be started: " . $e->getMessage());
				}
			}

			$this->consoleSender = new ConsoleCommandSender();
			PermissionManager::getInstance()->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this->consoleSender);

			Entity::init();
			Tile::init();
			BlockFactory::init();
			Enchantment::init();
			ItemFactory::init();
			Biome::init();
			MapManager::loadIdCounts();
			Color::initDyeColors();

			$this->commandMap = new SimpleCommandMap($this);

			$this->setAutoSave($this->getConfigBool("auto-save", true));

			LevelProviderManager::init();
			if (
				($format = LevelProviderManager::getProviderByName($formatName = $this->getProperty("level-settings.default-format", ""))) !== null &&
				$format instanceof WritableLevelProviderManagerEntry
			) {
				LevelProviderManager::setDefault($format);
			} elseif ($formatName !== "") {
				$this->logger->warning($this->getLanguage()->translateString("pocketmine.level.badDefaultFormat", [$formatName]));
			}

			GeneratorManager::registerDefaultGenerators();

			$this->craftingManager = CraftingManagerFromDataHelper::make(Path::join(BEDROCK_DATA_PATH, "legacy_recipes.json"));

			$this->logger->info("Loading PW10 resource packs...");
			$this->pw10ResourcePackManager = new ResourcePackManager($this->getDataPath() . "pw10_packs" . DIRECTORY_SEPARATOR, $this->logger);
			$this->logger->debug("Successfully loaded " . count($this->pw10ResourcePackManager->getResourceStack()) . " resource packs");

			$this->logger->info("Loading bedrock resource packs...");
			$this->bedrockResourcePackManager = new ResourcePackManager($this->getDataPath() . "bedrock_packs" . DIRECTORY_SEPARATOR, $this->logger);
			$this->logger->debug("Successfully loaded " . count($this->bedrockResourcePackManager->getResourceStack()) . " resource packs");

			$this->bedrockResourcePacksProtocol = (int) $this->getSubmarineProperty("resource-pack.minimal-bedrock-protocol", ProtocolInfo::PROTOCOL_370);

			$this->pluginManager = new PluginManager($this, $this->commandMap, ((bool) $this->getProperty("plugins.legacy-data-dir", true)) ? null : $this->getDataPath() . "plugin_data" . DIRECTORY_SEPARATOR);
			$this->profilingTickRate = (float) $this->getProperty("settings.profile-report-trigger", 20);
			if ($this->folderPluginLoader) {
				$this->pluginManager->registerInterface(new FolderPluginLoader($this->autoloader));
			}
			$this->pluginManager->registerInterface(new PharPluginLoader($this->autoloader));
			$this->pluginManager->registerInterface(new ScriptPluginLoader());

			register_shutdown_function([$this, "crashDump"]);

			$this->queryRegenerateTask = new QueryRegenerateEvent($this);

			foreach ($this->getAdditionalPluginDirs() as $path) {
				$this->pluginManager->loadPlugins($path);
			}

			$this->pluginManager->loadPlugins($this->pluginPath);
			$this->enablePlugins(PluginLoadOrder::STARTUP);

			foreach ((array) $this->getProperty("worlds", []) as $name => $options) {
				if ($options === null) {
					$options = [];
				} elseif (!is_array($options)) {
					continue;
				}
				if (!$this->loadLevel($name)) {
					$creationOptions = LevelCreationOptions::create();
					//TODO: error checking

					$generatorName = $options["generator"] ?? "default";
					$generatorOptions = isset($options["preset"]) && is_string($options["preset"]) ? $options["preset"] : "";

					$generatorClass = GeneratorManager::getGenerator($generatorName);
					$creationOptions->setGeneratorClass($generatorClass);
					$creationOptions->setGeneratorOptions($generatorOptions);

					$creationOptions->setDifficulty($this->getDifficulty());
					if (isset($options["difficulty"]) && is_string($options["difficulty"])) {
						$creationOptions->setDifficulty(Level::getDifficultyFromString($options["difficulty"]));
					}

					if (isset($options["seed"])) {
						$convertedSeed = Generator::convertSeed((string) ($options["seed"] ?? ""));
						if ($convertedSeed !== null) {
							$creationOptions->setSeed($convertedSeed);
						}
					}

					$this->generateLevel($name, $creationOptions);
				}
			}

			if ($this->getDefaultLevel() === null) {
				$default = $this->getConfigString("level-name", "world");
				if (trim($default) == "") {
					$this->getLogger()->warning("level-name cannot be null, using default");
					$default = "world";
					$this->setConfigString("level-name", "world");
				}
				if (!$this->loadLevel($default)) {
					$generatorName = $this->getConfigString("level-type");
					$generatorOptions = $this->getConfigString("generator-settings");
					$generatorClass = GeneratorManager::getGenerator($generatorName);

					$creationOptions = LevelCreationOptions::create()
						->setGeneratorClass($generatorClass)
						->setGeneratorOptions($generatorOptions);
					$convertedSeed = Generator::convertSeed($this->getConfigString("level-seed"));
					if ($convertedSeed !== null) {
						$creationOptions->setSeed($convertedSeed);
					}
					$creationOptions->setDifficulty($this->getDifficulty());
					$this->generateLevel($default, $creationOptions);
				}

				$this->setDefaultLevel($this->getLevelByName($default));
			}

			if ($this->isAllowNether() && $this->getNetherLevel() === null) {
				/** @var string $netherLevelName */
				$netherLevelName = $this->getSubmarineProperty("dimensions.nether.level-name", "nether");
				if (trim($netherLevelName) == "") {
					$netherLevelName = "nether";
				}
				if (!$this->loadLevel($netherLevelName)) {
					$creationOptions = LevelCreationOptions::create()->setGeneratorClass(Nether::class);
					$creationOptions->setSeed(time());
					$creationOptions->setDifficulty($this->getDifficulty());
					$this->generateLevel($netherLevelName, $creationOptions);
				}

				$this->setNetherLevel($this->getLevelByName($netherLevelName));
			}

			if ($this->isAllowTheEnd() && $this->getTheEndLevel() === null) {
				/** @var string $endLevelName */
				$endLevelName = $this->getSubmarineProperty("dimensions.the-end.level-name", "end");
				if (trim($endLevelName) == "") {
					$endLevelName = "end";
				}
				if (!$this->loadLevel($endLevelName)) {
					$creationOptions = LevelCreationOptions::create()->setGeneratorClass(End::class);
					$creationOptions->setSeed(time());
					$creationOptions->setDifficulty($this->getDifficulty());
					$this->generateLevel($endLevelName, $creationOptions);
				}

				$this->setTheEndLevel($this->getLevelByName($endLevelName));
			}

			if ($this->properties->hasChanged()) {
				$this->properties->save();
			}

			if (!($this->getDefaultLevel() instanceof Level)) {
				$this->getLogger()->emergency($this->getLanguage()->translateString("pocketmine.level.defaultError"));
				$this->forceShutdown();
				return;
			}

			if ($this->getProperty("ticks-per.autosave", 6000) > 0) {
				$this->autoSaveTicks = (int) $this->getProperty("ticks-per.autosave", 6000);
			}

			$this->enablePlugins(PluginLoadOrder::POSTWORLD);

			$rakLibRegistered = $this->network->registerInterface(new RakLibInterface($this, $this->getIp(), $this->getPort()));
			if ($rakLibRegistered) {
				$this->logger->info($this->getLanguage()->translateString("pocketmine.server.networkStart", [$this->getIp(), $this->getPort()]));
			}
			if ($this->getConfigBool("enable-query", true)) {
				if (!$rakLibRegistered) {
					//RakLib would normally handle the transport for Query packets
					//if it's not registered we need to make sure Query still works
					$this->network->registerInterface(new DedicatedQueryNetworkInterface($this->getIp(), $this->getPort(), new \PrefixedLogger($this->logger, "Dedicated Query Interface")));
				}
				$this->logger->info($this->getLanguage()->translateString("pocketmine.server.query.running", [$this->getIp(), $this->getPort()]));

				$this->network->registerRawPacketHandler(new QueryHandler($this));
			}

			foreach ($this->getIPBans()->getEntries() as $entry) {
				$this->network->blockAddress($entry->getName(), -1);
			}

			if ((bool) $this->getProperty("network.upnp-forwarding", false)) {
				$this->network->registerInterface(new UPnPNetworkInterface($this->logger, Internet::getInternalIP(), $this->getPort()));
			}

			$this->tickCounter = 0;

			$this->logger->info($this->getLanguage()->translateString("pocketmine.server.defaultGameMode", [self::getGamemodeString($this->getGamemode())]));

			$this->logger->info($this->getLanguage()->translateString("pocketmine.server.startFinished", [round(microtime(true) - $this->getStartTime(), 3)]));

			$this->tickProcessor();
			$this->forceShutdown();
		} catch (\Throwable $e) {
			$this->exceptionHandler($e);
		}
	}

	public function isAllowNether() : bool
	{
		return (bool) $this->getSubmarineProperty("dimensions.nether.active", true);
	}

	public function isAllowTheEnd() : bool
	{
		return (bool) $this->getSubmarineProperty("dimensions.the-end.active", true);
	}

	public function getConsoleSender() : ConsoleCommandSender
	{
		return $this->consoleSender;
	}

	/**
	 * @param TextContainer|string $message
	 * @param CommandSender[]|null $recipients
	 */
	public function broadcastMessage($message, array $recipients = null) : int
	{
		if (!is_array($recipients)) {
			return $this->broadcast($message, self::BROADCAST_CHANNEL_USERS);
		}

		foreach ($recipients as $recipient) {
			$recipient->sendMessage($message);
		}

		return count($recipients);
	}

	/**
	 * @param Player[]|null $recipients
	 */
	public function broadcastTip(string $tip, array $recipients = null) : int
	{
		if (!is_array($recipients)) {
			/** @var Player[] $recipients */
			$recipients = [];
			foreach (PermissionManager::getInstance()->getPermissionSubscriptions(self::BROADCAST_CHANNEL_USERS) as $permissible) {
				if ($permissible instanceof Player && $permissible->hasPermission(self::BROADCAST_CHANNEL_USERS)) {
					$recipients[spl_object_hash($permissible)] = $permissible; // do not send messages directly, or some might be repeated
				}
			}
		}

		foreach ($recipients as $recipient) {
			$recipient->sendTip($tip);
		}

		return count($recipients);
	}

	/**
	 * @param Player[]|null $recipients
	 */
	public function broadcastPopup(string $popup, array $recipients = null) : int
	{
		if (!is_array($recipients)) {
			/** @var Player[] $recipients */
			$recipients = [];

			foreach (PermissionManager::getInstance()->getPermissionSubscriptions(self::BROADCAST_CHANNEL_USERS) as $permissible) {
				if ($permissible instanceof Player && $permissible->hasPermission(self::BROADCAST_CHANNEL_USERS)) {
					$recipients[spl_object_hash($permissible)] = $permissible; // do not send messages directly, or some might be repeated
				}
			}
		}

		foreach ($recipients as $recipient) {
			$recipient->sendPopup($popup);
		}

		return count($recipients);
	}

	/**
	 * @param int           $fadeIn     Duration in ticks for fade-in. If -1 is given, client-sided defaults will be used.
	 * @param int           $stay       Duration in ticks to stay on screen for
	 * @param int           $fadeOut    Duration in ticks for fade-out.
	 * @param Player[]|null $recipients
	 */
	public function broadcastTitle(string $title, string $subtitle = "", int $fadeIn = -1, int $stay = -1, int $fadeOut = -1, array $recipients = null) : int
	{
		if (!is_array($recipients)) {
			/** @var Player[] $recipients */
			$recipients = [];

			foreach (PermissionManager::getInstance()->getPermissionSubscriptions(self::BROADCAST_CHANNEL_USERS) as $permissible) {
				if ($permissible instanceof Player && $permissible->hasPermission(self::BROADCAST_CHANNEL_USERS)) {
					$recipients[spl_object_hash($permissible)] = $permissible; // do not send messages directly, or some might be repeated
				}
			}
		}

		foreach ($recipients as $recipient) {
			$recipient->sendTitle($title, $subtitle, $fadeIn, $stay, $fadeOut);
		}

		return count($recipients);
	}

	/**
	 * @param TextContainer|string $message
	 */
	public function broadcast($message, string $permissions) : int
	{
		/** @var CommandSender[] $recipients */
		$recipients = [];
		foreach (explode(";", $permissions) as $permission) {
			foreach (PermissionManager::getInstance()->getPermissionSubscriptions($permission) as $permissible) {
				if ($permissible instanceof CommandSender && $permissible->hasPermission($permission)) {
					$recipients[spl_object_hash($permissible)] = $permissible; // do not send messages directly, or some might be repeated
				}
			}
		}

		foreach ($recipients as $recipient) {
			$recipient->sendMessage($message);
		}

		return count($recipients);
	}

	/**
	 * Broadcasts a Minecraft packet to a list of players
	 *
	 * @param Player[] $players
	 *
	 * @return void
	 */
	public function broadcastPacket(array $players, DataPacket $packet)
	{
		$this->batchPackets($players, [$packet], false);
	}

	/**
	 * Broadcasts a Minecraft packet to all online players
	 */
	public function broadcastPacketToAll(DataPacket $packet) : void
	{
		$this->broadcastPacket($this->getOnlinePlayers(), $packet);
	}

	/**
	 * Broadcasts a list of packets in a batch to a list of players
	 *
	 * @param Player[]     $players
	 * @param DataPacket[] $packets
	 */
	public function batchPackets(array $players, array $packets, bool $forceSync = false, bool $immediate = false) : void
	{
		$ev = new DataPacketBroadcastEvent($players, $packets);
		$ev->call();
		if ($ev->isCancelled()) {
			return;
		}
		$players = $ev->getPlayers();
		$packets = $ev->getPackets();
		if (count($players) === 0 || count($packets) === 0) {
			return;
		}

		$targets = [];
		foreach ($players as $player) {
			if ($player->isConnected()) {
				$targets[$player->getProtocolVersion()][] = $player;
			}
		}

		if (count($targets) > 0) {
			/** @var Player[][] $targets */
			foreach ($targets as $protocol => $receivers) {
				$totalLength = 0;
				$packetBuffers = [];
				foreach ($packets as $packet) {
					if (PacketIdTranslator::getInstance()->toNetworkId($protocol, $packet->pid()) === null) {
						continue;
					}

					$packet->setProtocol($protocol);
					$buffer = Player::encodePacketTimed($packet);
					//varint length prefix + packet buffer
					$totalLength += (((int) log(strlen($buffer), 128)) + 1) + strlen($buffer);
					$packetBuffers[] = $buffer;
				}

				$threshold = NetworkCompression::$THRESHOLD;
				if (count($receivers) > 1 && $totalLength >= $threshold) {
					//do not prepare shared batch unless we're sure it will be compressed
					$stream = new BinaryStream();
					PacketBatch::encodeRaw($stream, $packetBuffers);
					$batchBuffer = $stream->getBuffer();

					$batch = $this->prepareBatch($batchBuffer, $protocol);
					foreach ($receivers as $target) {
						$target->queueCompressed($batch);
					}
				} else {
					foreach ($receivers as $target) {
						foreach ($packetBuffers as $packetBuffer) {
							$target->addToSendBuffer($packetBuffer);
						}
					}
				}
			}
		}
	}

	public function prepareBatch(string $buffer, int $protocol, ?bool $sync = null) : CompressBatchPromise|string
	{
		try {
			Timings::$playerNetworkSendCompress->startTiming();
			if ($sync === null) {
				$sync = !($this->networkCompressionAsync && NetworkCompression::$THRESHOLD >= 0 && strlen($buffer) >= NetworkCompression::$THRESHOLD);
			}
			if (NetworkCompression::$THRESHOLD >= 0 && strlen($buffer) >= NetworkCompression::$THRESHOLD) {
				$compressionLevel = NetworkCompression::$LEVEL;
			} else {
				$compressionLevel = 0;
			}

			if (!$sync) {
				$promise = new CompressBatchPromise();
				$task = new CompressBatchTask($buffer, $protocol, $compressionLevel, $promise);
				$this->getAsyncPool()->submitTask($task);
				return $promise;
			}

			return NetworkCompression::compress($buffer, $protocol, $compressionLevel);
		} finally {
			Timings::$playerNetworkSendCompress->stopTiming();
		}
	}

	/**
	 * @return void
	 */
	public function enablePlugins(int $type)
	{
		foreach ($this->pluginManager->getPlugins() as $plugin) {
			if (!$plugin->isEnabled() && $plugin->getDescription()->getOrder() === $type) {
				$this->enablePlugin($plugin);
			}
		}

		if ($type === PluginLoadOrder::POSTWORLD) {
			$this->commandMap->registerServerAliases();
			DefaultPermissions::registerCorePermissions();
		}
	}

	public function enablePlugin(Plugin $plugin) : void
	{
		$this->pluginManager->enablePlugin($plugin);
	}

	public function disablePlugins() : void
	{
		$this->pluginManager->disablePlugins();
	}

	public function checkConsole() : void
	{
		Timings::$serverCommand->startTiming();
		while (($line = $this->console->getLine()) !== null) {
			$ev = new ServerCommandEvent($this->consoleSender, $line);
			$ev->call();
			if (!$ev->isCancelled()) {
				$this->dispatchCommand($ev->getSender(), $ev->getCommand());
			}
		}
		Timings::$serverCommand->stopTiming();
	}

	/**
	 * Executes a command from a CommandSender
	 */
	public function dispatchCommand(CommandSender $sender, string $commandLine, bool $internal = false) : bool
	{
		if (!$internal) {
			$ev = new CommandEvent($sender, $commandLine);
			$ev->call();
			if ($ev->isCancelled()) {
				return false;
			}

			$commandLine = $ev->getCommand();
		}

		if ($this->commandMap->dispatch($sender, $commandLine)) {
			return true;
		}

		$sender->sendMessage($this->customUnknownCommandMessage ? (string) $this->getSubmarineProperty("general.custom-unknown-command-message.message") : $this->getLanguage()->translateString(TextFormat::RED . "%commands.generic.notFound"));

		return false;
	}

	/**
	 * @return void
	 */
	public function reload()
	{
		$this->logger->info("Saving worlds...");

		foreach ($this->levels as $level) {
			$level->save();
		}

		$this->pluginManager->disablePlugins();
		$this->pluginManager->clearPlugins();
		PermissionManager::getInstance()->clearPermissions();
		$this->commandMap->clearCommands();

		$this->logger->info("Reloading properties...");
		$this->properties->reload();
		$this->maxPlayers = $this->getConfigInt("max-players", 20);

		if ($this->getConfigBool("hardcore", false) && $this->getDifficulty() < Level::DIFFICULTY_HARD) {
			$this->setConfigInt("difficulty", Level::DIFFICULTY_HARD);
		}

		$this->banByIP->load();
		$this->banByName->load();
		$this->reloadWhitelist();
		$this->operators->reload();

		foreach ($this->getIPBans()->getEntries() as $entry) {
			$this->getNetwork()->blockAddress($entry->getName(), -1);
		}

		if ($this->folderPluginLoader) {
			$this->pluginManager->registerInterface(new FolderPluginLoader($this->autoloader));
		}
		$this->pluginManager->registerInterface(new PharPluginLoader($this->autoloader));
		$this->pluginManager->registerInterface(new ScriptPluginLoader());
		$this->pluginManager->loadPlugins($this->pluginPath);

		$this->enablePlugins(PluginLoadOrder::STARTUP);
		$this->enablePlugins(PluginLoadOrder::POSTWORLD);
		TimingsHandler::reload();
	}

	/**
	 * Shuts the server down correctly
	 */
	public function shutdown() : void
	{
		if ($this->isRunning) {
			$this->isRunning = false;
			$this->signalHandler->unregister();
		}
	}

	private function forceShutdownExit() : void
	{
		$this->forceShutdown();
		Process::kill(Process::pid());
	}

	public function forceShutdown() : void
	{
		if ($this->hasStopped) {
			return;
		}

		if ($this->doTitleTick) {
			echo "\x1b]0;\x07";
		}

		if ($this->isRunning) {
			$this->logger->emergency($this->getLanguage()->translateString("pocketmine.server.forcingShutdown"));
		}
		try {
			$this->hasStopped = true;

			$this->shutdown();
			if ($this->rcon instanceof RCON) {
				$this->rcon->stop();
			}

			if ($this->pluginManager instanceof PluginManager) {
				$this->getLogger()->debug("Disabling all plugins");
				$this->pluginManager->disablePlugins();
			}

			foreach ($this->players as $player) {
				$player->close($player->getLeaveMessage(), $this->getProperty("settings.shutdown-message", "Server closed"), true, DisconnectFailReason::SHUTDOWN);
			}

			$this->getLogger()->debug("Unloading all worlds");
			foreach ($this->getLevels() as $level) {
				$this->unloadLevel($level, true);
			}

			$this->getLogger()->debug("Saving all maps");
			MapManager::saveMaps();

			$this->getLogger()->debug("Removing event handlers");
			HandlerList::unregisterAll();

			if ($this->asyncPool instanceof AsyncPool) {
				$this->getLogger()->debug("Shutting down async task worker pool");
				$this->asyncPool->shutdown();
			}

			if ($this->properties !== null && $this->properties->hasChanged()) {
				$this->getLogger()->debug("Saving properties");
				$this->properties->save();
			}

			if ($this->console !== null) {
				$this->logger->debug("Closing console");
				$this->console->shutdown();
				$this->console->notify();
			}

			if ($this->network instanceof Network) {
				$this->getLogger()->debug("Stopping network interfaces");
				foreach ($this->network->getInterfaces() as $interface) {
					$this->getLogger()->debug("Stopping network interface " . get_class($interface));
					$this->network->unregisterInterface($interface);
				}
			}
		} catch (\Throwable $e) {
			$this->logger->logException($e);
			$this->logger->emergency("Crashed while crashing, killing process");
			@Process::kill(Process::pid());
		}

	}

	/**
	 * @return QueryRegenerateEvent
	 */
	public function getQueryInformation()
	{
		return $this->queryRegenerateTask;
	}

	/**
	 * @param mixed[][]|null $trace
	 *
	 * @phpstan-param list<array<string, mixed>>|null $trace
	 *
	 * @return void
	 */
	public function exceptionHandler(\Throwable $e, $trace = null)
	{
		while (@ob_end_flush()) {
		}
		global $lastError;

		if ($trace === null) {
			$trace = $e->getTrace();
		}

		//If this is a thread crash, this logs where the exception came from on the main thread, as opposed to the
		//crashed thread. This is intentional, and might be useful for debugging
		//Assume that the thread already logged the original exception with the correct stack trace
		$this->logger->logException($e, $trace);

		if ($e instanceof ThreadCrashException) {
			$info = $e->getCrashInfo();
			$type = $info->getType();
			$errstr = $info->getMessage();
			$errfile = $info->getFile();
			$errline = $info->getLine();
			$printableTrace = $info->getTrace();
			$thread = $info->getThreadName();
		} else {
			$type = get_class($e);
			$errstr = $e->getMessage();
			$errfile = $e->getFile();
			$errline = $e->getLine();
			$printableTrace = Utils::printableTraceWithMetadata($trace);
			$thread = "Main";
		}

		$errstr = preg_replace('/\s+/', ' ', trim($errstr));

		$lastError = [
			"type" => $type,
			"message" => $errstr,
			"fullFile" => $errfile,
			"file" => Filesystem::cleanPath($errfile),
			"line" => $errline,
			"trace" => $printableTrace,
			"thread" => $thread
		];

		global $lastExceptionError, $lastError;
		$lastExceptionError = $lastError;
		$this->crashDump();
	}

	public function crashDump() : void
	{
		while (@ob_end_flush()) {
		}
		if (!$this->isRunning) {
			return;
		}
		$this->hasStopped = false;

		ini_set("error_reporting", '0');
		ini_set("memory_limit", '-1'); //Fix error dump not dumped on memory problems
		try {
			$this->logger->emergency($this->getLanguage()->translateString("pocketmine.crash.create"));
			$dump = new CrashDump($this);

			$this->logger->emergency($this->getLanguage()->translateString("pocketmine.crash.submit", [$dump->getPath()]));

			if ($this->getProperty("auto-report.enabled", true) !== false) {
				$report = true;

				$stamp = $this->getDataPath() . "crashdumps/.last_crash";
				$crashInterval = 120; //2 minutes
				if (file_exists($stamp) && !($report = (filemtime($stamp) + $crashInterval < time()))) {
					$this->logger->debug("Not sending crashdump due to last crash less than $crashInterval seconds ago");
				}
				@touch($stamp); //update file timestamp

				$plugin = $dump->getData()["plugin"];
				if (is_string($plugin)) {
					$p = $this->pluginManager->getPlugin($plugin);
					if ($p instanceof Plugin && !($p->getPluginLoader() instanceof PharPluginLoader)) {
						$this->logger->debug("Not sending crashdump due to caused by non-phar plugin");
						$report = false;
					}
				}

				if ($dump->getData()["error"]["type"] === \ParseError::class) {
					$report = false;
				}

				if (strrpos(VersionInfo::GIT_HASH(), "-dirty") !== false || VersionInfo::GIT_HASH() === str_repeat("00", 20)) {
					$this->logger->debug("Not sending crashdump due to locally modified");
					$report = false; //Don't send crashdumps for locally modified builds
				}

				if ($report) {
					$url = ((bool) $this->getProperty("auto-report.use-https", true) ? "https" : "http") . "://" . $this->getProperty("auto-report.host", "crash.pmmp.io") . "/submit/api";
					$postUrlError = "Unknown error";
					$reply = Internet::postURL($url, [
						"report" => "yes",
						"name" => $this->getName() . " " . $this->getPocketMineVersion(),
						"email" => "crash@pocketmine.net",
						"reportPaste" => base64_encode($dump->getEncodedData())
					], 10, [], $postUrlError);

					if ($reply !== null && is_object($data = json_decode($reply->getBody()))) {
						if (isset($data->crashId) && is_int($data->crashId) && isset($data->crashUrl) && is_string($data->crashUrl)) {
							$reportId = $data->crashId;
							$reportUrl = $data->crashUrl;
							$this->logger->emergency($this->getLanguage()->translateString("pocketmine.crash.archive", [$reportUrl, $reportId]));
						} elseif (isset($data->error) && is_string($data->error)) {
							$this->logger->emergency("Automatic crash report submission failed: $data->error");
						}
					} else {
						$this->logger->emergency("Failed to communicate with crash archive: $postUrlError");
					}
				}
			}
		} catch (\Throwable $e) {
			$this->logger->logException($e);
			try {
				$this->logger->critical($this->getLanguage()->translateString("pocketmine.crash.error", [$e->getMessage()]));
			} catch (\Throwable $e) {
			}
		}

		$this->forceShutdown();
		$this->isRunning = false;

		//Force minimum uptime to be >= 120 seconds, to reduce the impact of spammy crash loops
		$uptime = time() - ((int) $this->startTime);
		$minUptime = 120;
		$spacing = $minUptime - $uptime;
		if ($spacing > 0) {
			echo "--- Uptime {$uptime}s - waiting {$spacing}s to throttle automatic restart (you can kill the process safely now) ---" . PHP_EOL;
			sleep($spacing);
		}
		@Process::kill(Process::pid());
		exit(1);
	}

	/**
	 * @return mixed[]
	 */
	public function __debugInfo() : array
	{
		return [];
	}

	public function getTickSleeper() : SleeperHandler
	{
		return $this->tickSleeper;
	}

	private function tickProcessor() : void
	{
		$this->nextTick = microtime(true);

		while ($this->isRunning) {
			$this->tick();

			//sleeps are self-correcting - if we undersleep 1ms on this tick, we'll sleep an extra ms on the next tick
			$this->tickSleeper->sleepUntil($this->nextTick);
		}
	}

	public function onPlayerLogin(Player $player) : void
	{
		$this->loggedInPlayers[$player->getRawUniqueId()] = $player;
	}

	public function onPlayerLogout(Player $player) : void
	{
		unset($this->loggedInPlayers[$player->getRawUniqueId()]);
	}

	public function addPlayer(Player $player) : void
	{
		$this->players[spl_object_hash($player)] = $player;
	}

	public function removePlayer(Player $player) : void
	{
		unset($this->players[spl_object_hash($player)]);
	}

	public function addOnlinePlayer(Player $player) : void
	{
		$this->updatePlayerListData($player->getUniqueId(), $player->getId(), $player->getDisplayName(), $player->getSkin(), $player->getXuid());

		$this->playerList[$player->getRawUniqueId()] = $player;
	}

	public function removeOnlinePlayer(Player $player) : void
	{
		if (isset($this->playerList[$player->getRawUniqueId()])) {
			unset($this->playerList[$player->getRawUniqueId()]);

			$this->removePlayerListData($player->getUniqueId());
		}
	}

	/**
	 * @param Player[]|null $players
	 */
	public function updatePlayerListData(UUID $uuid, int $entityId, string $name, Skin $skin, string $xboxUserId = "", array $players = null) : void
	{
		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_ADD;

		$pk->entries[] = PlayerListEntry::createAdditionEntry($uuid, $entityId, $name, $skin, $xboxUserId);

		$this->broadcastPacket($players ?? $this->playerList, $pk);
	}

	/**
	 * @param Player[]|null $players
	 */
	public function removePlayerListData(UUID $uuid, array $players = null) : void
	{
		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_REMOVE;
		$pk->entries[] = PlayerListEntry::createRemovalEntry($uuid);
		$this->broadcastPacket($players ?? $this->playerList, $pk);
	}

	public function sendFullPlayerListData(Player $p) : void
	{
		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_ADD;
		foreach ($this->playerList as $player) {
			$pk->entries[] = PlayerListEntry::createAdditionEntry($player->getUniqueId(), $player->getId(), $player->getDisplayName(), $player->getSkin(), $player->getXuid());
		}

		$p->sendDataPacket($pk);
	}

	private function checkTickUpdates(int $currentTick) : void
	{
		foreach ($this->players as $player) {
			if (!$player->loggedIn && (time() >= $player->connectTime + 30)) {
				$player->close("", "Login timeout", true, DisconnectFailReason::TIMEOUT);
			}
		}

		//Do level ticks
		foreach ($this->levels as $k => $level) {
			if (!isset($this->levels[$k])) {
				// Level unloaded during the tick of a level earlier in this loop, perhaps by plugin
				continue;
			}

			try {
				$worldTime = microtime(true);
				$level->doTick($currentTick);
				$tickMs = (microtime(true) - $worldTime) * 1000;
				$level->tickRateTime = $tickMs;
				if ($tickMs >= Server::TARGET_SECONDS_PER_TICK * 1000) {
					$level->getLogger()->debug(sprintf("Tick took too long: %gms (%g ticks)", $tickMs, round($tickMs / (Server::TARGET_SECONDS_PER_TICK * 1000), 2)));
				}
			} catch (\Throwable $e) {
				$this->logger->critical($this->getLanguage()->translateString("pocketmine.level.tickError", [$level->getName(), $e->getMessage()]));
				$this->logger->logException($e);
			}
		}
	}

	public function doAutoSave() : void
	{
		foreach ($this->levels as $level) {
			foreach ($level->getPlayers() as $player) {
				if ($player->joined) {
					$player->save();
				} elseif (!$player->isConnected()) {
					$this->removePlayer($player);
				}
			}
			$level->save(false);
		}
	}

	/**
	 * @return BaseLang
	 */
	public function getLanguage()
	{
		return $this->baseLang;
	}

	public function isLanguageForced() : bool
	{
		return $this->forceLanguage;
	}

	/**
	 * @return Network
	 */
	public function getNetwork()
	{
		return $this->network;
	}

	/**
	 * @return MemoryManager
	 */
	public function getMemoryManager()
	{
		return $this->memoryManager;
	}

	public function isNetworkCompressionAsync() : bool
	{
		return $this->networkCompressionAsync;
	}

	private function titleTick() : void
	{
		Timings::$titleTick->startTiming();
		$d = Process::getRealMemoryUsage();

		$u = Process::getAdvancedMemoryUsage();
		$usage = sprintf("%g/%g/%g/%g MB @ %d threads", round(($u[0] / 1024) / 1024, 2), round(($d[0] / 1024) / 1024, 2), round(($u[1] / 1024) / 1024, 2), round(($u[2] / 1024) / 1024, 2), Process::getThreadCount());

		echo "\x1b]0;" . $this->getName() . " " .
			$this->getSubmarineVersion() .
			" | Online " . count($this->players) . "/" . $this->getMaxPlayers() .
			" | Memory " . $usage .
			" | U " . round($this->network->getUpload() / 1024, 2) .
			" D " . round($this->network->getDownload() / 1024, 2) .
			" kB/s | TPS " . $this->getTicksPerSecondAverage() .
			" | Load " . $this->getTickUsageAverage() . "%\x07";

		Timings::$titleTick->stopTiming();
	}

	private function getAdditionalPluginDirs() : array
	{
		return $this->getSubmarineProperty("additional-plugin-dirs", []);
	}

	/**
	 * Tries to execute a server tick
	 */
	private function tick() : void
	{
		$tickTime = microtime(true);
		if (($tickTime - $this->nextTick) < -0.025) { //Allow half a tick of diff
			return;
		}

		Timings::$serverTick->startTiming();

		++$this->tickCounter;

		Timings::$connection->startTiming();
		$this->network->tick();
		Timings::$connection->stopTiming();

		Timings::$scheduler->startTiming();
		$this->pluginManager->tickSchedulers($this->tickCounter);
		Timings::$scheduler->stopTiming();

		Timings::$schedulerAsync->startTiming();
		$this->asyncPool->collectTasks();
		Timings::$schedulerAsync->stopTiming();

		$this->checkTickUpdates($this->tickCounter);

		foreach ($this->players as $player) {
			$player->checkNetwork();
		}

		if (($this->tickCounter % self::TARGET_TICKS_PER_SECOND) === 0) {
			if ($this->doTitleTick) {
				$this->titleTick();
			}
			$this->currentTPS = self::TARGET_TICKS_PER_SECOND;
			$this->currentUse = 0;

			($this->queryRegenerateTask = new QueryRegenerateEvent($this))->call();

			$this->network->updateName();
			$this->network->resetStatistics();
		}

		if ($this->autoSave && ++$this->autoSaveTicker >= $this->autoSaveTicks) {
			$this->autoSaveTicker = 0;
			$this->getLogger()->debug("[Auto Save] Saving worlds...");
			$start = microtime(true);
			$this->doAutoSave();
			$time = (microtime(true) - $start);
			$this->getLogger()->debug("[Auto Save] Save completed in " . ($time >= 1 ? round($time, 3) . "s" : round($time * 1000) . "ms"));
		}

		if (($this->tickCounter % self::TICKS_PER_WORLD_CACHE_CLEAR) === 0) {
			foreach ($this->levels as $world) {
				$world->clearCache();
			}
		}

		if (($this->tickCounter % self::TICKS_PER_TPS_OVERLOAD_WARNING) === 0 && $this->getTicksPerSecondAverage() < self::TPS_OVERLOAD_WARNING_THRESHOLD) {
			$this->logger->warning($this->getLanguage()->translateString("pocketmine.server.tickOverload"));
		}

		$this->getMemoryManager()->check();

		Timings::$serverTick->stopTiming();

		$now = microtime(true);
		$this->currentTPS = min(self::TARGET_TICKS_PER_SECOND, 1 / max(0.001, $now - $tickTime));
		$this->currentUse = min(1, ($now - $tickTime) / self::TARGET_SECONDS_PER_TICK);

		TimingsHandler::tick($this->currentTPS <= $this->profilingTickRate);

		$idx = $this->tickCounter % self::TARGET_TICKS_PER_SECOND;
		$this->tickAverage[$idx] = $this->currentTPS;
		$this->useAverage[$idx] = $this->currentUse;

		if (($this->nextTick - $tickTime) < -1) {
			$this->nextTick = $tickTime;
		} else {
			$this->nextTick += self::TARGET_SECONDS_PER_TICK;
		}
	}

	/**
	 * Called when something attempts to serialize the server instance.
	 *
	 * @throws \BadMethodCallException because Server instances cannot be serialized
	 */
	public function __sleep()
	{
		throw new \BadMethodCallException("Cannot serialize Server instance");
	}
}
