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

namespace pocketmine\network\mcpe\auth;

use pocketmine\network\mcpe\JwtException;
use pocketmine\network\mcpe\JwtUtils;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\thread\NonThreadSafeValue;

use function base64_decode;
use function time;

class ProcessLoginTask extends AsyncTask
{
	/**
	 * Old Mojang root auth key. This was used since the introduction of Xbox Live authentication in 0.15.0.
	 * This key is expected to be replaced by the key below in the future, but this has not yet happened as of
	 * 2023-07-01.
	 * Ideally we would place a time expiry on this key, but since Mojang have not given a hard date for the key change,
	 * and one bad guess has already caused a major outage, we can't do this.
	 * TODO: This needs to be removed as soon as the new key is deployed by Mojang's authentication servers.
	 */
	public const MOJANG_OLD_ROOT_PUBLIC_KEY = "MHYwEAYHKoZIzj0CAQYFK4EEACIDYgAE8ELkixyLcwlZryUQcu1TvPOmI2B7vX83ndnWRUaXm74wFfa5f/lwQNTfrLVHa2PmenpGI6JhIMUJaWZrjmMj90NoKNFSNBuKdm8rYiXsfaz3K36x/1U26HpG0ZxK/V1V";

	/**
	 * New Mojang root auth key. Mojang notified third-party developers of this change prior to the release of 1.20.0.
	 * Expectations were that this would be used starting a "couple of weeks" after the release, but as of 2023-07-01,
	 * it has not yet been deployed.
	 */
	public const MOJANG_ROOT_PUBLIC_KEY = "MHYwEAYHKoZIzj0CAQYFK4EEACIDYgAECRXueJeTDqNRRgJi/vlRufByu/2G0i2Ebt6YMar5QX/R0DIIyrJMcUpruK4QveTfJSTp3Shlq4Gk34cD/4GUWwkv0DVuzeuB+tXija7HBxii03NHDbPAD0AKnLr2wdAp";

	private const CLOCK_DRIFT_MAX = 60 * 60 * 24 * 7;

	/** @phpstan-var NonThreadSafeValue<LoginPacket> */
	private NonThreadSafeValue $packet;

	/**
	 * Whether the keychain signatures were validated correctly. This will be set to an error message if any link in the
	 * keychain is invalid for whatever reason (bad signature, not in nbf-exp window, etc). If this is non-null, the
	 * keychain might have been tampered with. The player will always be disconnected if this is non-null.
	 */
	private ?string $error = "Unknown";

	/**
	 * Whether the player is logged into Xbox Live. This is true if any link in the keychain is signed with the Mojang
	 * root public key.
	 */
	private bool $authenticated = false;

	public function __construct(Player $player, LoginPacket $packet)
	{
		$this->storeLocal($player);
		$this->packet = new NonThreadSafeValue($packet);
	}

	public function onRun() : void
	{
		try {
			$this->validateChain();
			$this->error = null;
		} catch (VerifyLoginException $e) {
			$this->error = $e->getDisconnectMessage();
		}
	}

	private function validateChain() : void
	{
		$packet = $this->packet->deserialize(); //Get it in a local variable to make sure it stays unserialized

		/** @var string[] $chain */
		$chain = $packet->chainData["chain"];

		$currentKey = null;
		$first = true;

		foreach ($chain as $jwt) {
			$this->validateToken($jwt, $currentKey, $first);
			if ($first) {
				$first = false;
			}
		}

		$this->validateToken($packet->clientDataJwt, $currentKey);
	}

	/**
	 * @throws VerifyLoginException if errors are encountered
	 */
	private function validateToken(string $jwt, ?string &$currentPublicKey, bool $first = false) : void
	{
		try {
			[$headersArray, $claimsArray, ] = JwtUtils::parse($jwt);
		} catch (JwtException $e) {
			throw new VerifyLoginException("Failed to parse JWT: " . $e->getMessage(), null, 0, $e);
		}

		$headerDerKey = base64_decode($headersArray["x5u"], true);
		if ($headerDerKey === false) {
			throw new VerifyLoginException("Invalid JWT public key: base64 decoding error decoding x5u");
		}

		if ($currentPublicKey === null) {
			if (!$first) {
				throw new VerifyLoginException("%pocketmine.disconnect.invalidSession.missingKey");
			}
		} elseif ($headerDerKey !== $currentPublicKey) {
			//Fast path: if the header key doesn't match what we expected, the signature isn't going to validate anyway
			throw new VerifyLoginException("Invalid JWT signature", "%pocketmine.disconnect.invalidSession.badSignature");
		}

		try {
			$signingKeyOpenSSL = JwtUtils::parseDerPublicKey($headerDerKey);
		} catch (JwtException $e) {
			throw new VerifyLoginException("Invalid JWT public key: " . $e->getMessage(), null, 0, $e);
		}
		try {
			if (!JwtUtils::verify($jwt, $signingKeyOpenSSL)) {
				throw new VerifyLoginException("%pocketmine.disconnect.invalidSession.badSignature");
			}
		} catch (JwtException $e) {
			throw new VerifyLoginException($e->getMessage(), null, 0, $e);
		}

		if ($headersArray["x5u"] === self::MOJANG_ROOT_PUBLIC_KEY || $headersArray["x5u"] === self::MOJANG_OLD_ROOT_PUBLIC_KEY) {
			$this->authenticated = true; //we're signed into xbox live
		}

		$time = time();
		if (isset($claimsArray["nbf"]) && $claimsArray["nbf"] > $time + self::CLOCK_DRIFT_MAX) {
			throw new VerifyLoginException("%pocketmine.disconnect.invalidSession.tooEarly");
		}

		if (isset($claimsArray["exp"]) && $claimsArray["exp"] < $time - self::CLOCK_DRIFT_MAX) {
			throw new VerifyLoginException("%pocketmine.disconnect.invalidSession.tooLate");
		}
		if (isset($claimsArray["identityPublicKey"])) {
			$identityPublicKey = base64_decode($claimsArray["identityPublicKey"], true);
			if ($identityPublicKey === false) {
				throw new VerifyLoginException("Invalid identityPublicKey: base64 error decoding");
			}
			try {
				//verify key format and parameters
				JwtUtils::parseDerPublicKey($identityPublicKey);
			} catch (JwtException $e) {
				throw new VerifyLoginException("Invalid identityPublicKey: " . $e->getMessage(), null, 0, $e);
			}
			$currentPublicKey = $identityPublicKey; //if there are further links, the next link should be signed with this
		}
	}

	public function onCompletion(Server $server) : void
	{
		/** @var Player $player */
		$player = $this->fetchLocal();
		if (!$player->isConnected()) {
			$server->getLogger()->error("Player " . $player->getName() . " was disconnected before their login could be verified");
		} else {
			$player->onVerifyCompleted($this->packet->deserialize(), $this->error, $this->authenticated);
		}
	}
}
