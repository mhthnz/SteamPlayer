<?php
include 'SteamCore.php';
include 'SteamPlayerCollection.php';


/**
 *	For began need set Steam API:
 *
 *		SteamPlayer::$API_KEY = 'xxxxxxxxxxxxxxxxxxxxxxxxx';
 *
 **/
class SteamPlayer extends SteamCore{


	/**
	 *	@var object of user data
	 */
	private $_cache;


	const STATUS_OFFLINE 			= '0'; 		//user offline
	const STATUS_ONLINE 			= '1';		//user online
	const STATUS_BUSY 				= '2';		//user set status himself
	const STATUS_AWAY 				= '3';		//set himself or afk
	const STATUS_SNOOZE 			= '4';		//pc is sleep mode or long time afk
	const STATUS_LOOKING_TO_TRADE 	= '5';		//looking to trade
	const STATUS_LOOKING_TO_PLAY	= '6';		//looking to play

	const AVATAR_SMALL 	= 'avatar'; 			//32x32
	const AVATAR_MEDIUM = 'avatarmedium'; 		// 64x64
	const AVATAR_LARGE 	= 'avatarfull'; 		// 184x184


	/**
	 *	Create SteamPlayer instance by SteamID or SteamPlayerCollection if array given.
	 *	@param array|integer $steamIDs Array of SteamIDs or one SteamID
	 *
	 *	Usage:
	 *	$instance = SteamPlayer::Create('xxxxxxxxxxxxxxxxxxxxxxxxx');
	 *	$instance = SteamPlayer::Create(['xxxxxxxxxxxxxxxxxxxxxxxxx', 'xxxxxxxxxxxxxxxxxxxxxxxxxxxx', 'xxxxxxxxxxxxxxxxxxxxxxxxxxxx']);
	 *
	 *	@return SteamPlayer|SteamPlayerCollection object
	 */
	public static function Create($steamIDs)
	{	
		//If not array
		if (!is_array($steamIDs)) {
			$player = static::getPlayer($steamIDs);
			if ($player !== false) {
				return new static($player);
			} else {
				return false;
			}
		}
		//If array
		$countIDs = count($steamIDs);
		$players = [];
		if ($countIDs <= 100) {
			$players = static::getPlayers($steamIDs);	
		} else {
			//100 steam ids per request
			$countRequests = ceil($countIDs / 100);
			for($i = 0; $i < $countRequests; $i++) {
				$sliceArray = array_slice($steamIDs, ($i*99), 100);
				if (($tempPlayers = static::getPlayers($sliceArray)) !== false) {
					$players = array_merge($players, $tempPlayers);
				}
			}
		}
		//Create instances
		$instances = [];
		foreach($players as $player) {
			$instances[] = new static($player);
		}
		return new SteamPlayerCollection($instances);
	}


	/**
	 *	Class constructor.
	 *	@param object $cache User data object
	 */
	public function __construct($cache)
	{
		$this->_cache = $cache;
	}


	/**
	 * Magic
	 */
	public function __isset($name)
	{
		return $this->exists($name);
	}


	/**
	 * Magic
	 */	
	public function __get($name)
	{
		if (!isset($this->$name))
			return NULL;
		return $this->_cache->$name;
	}

	
	/**
	 *	Get nickname.
	 *	@return string
	 */
	public function nickName()
	{
		return $this->personaname;
	}


	/**
	 *	Get Steam Id 64bit.
	 *	@return integer 64 bit
	 */
	public function steamID()
	{
		return $this->steamid;
	}


	/**
	 *	Get realname if not private profile.
	 *	@return string|NULL
	 */
	public function realName()
	{
		return $this->realname;
	}


	/**
	 *	Get short country name if not private profile.
	 *	@return string|NULL
	 */
	public function countryCode()
	{
		return $this->loccountrycode;
	}


	/**
	 *	Get status if not private profile.
	 *	@return integer|NULL
	 */
	public function getStatus()
	{	
		if (!$this->isPrivate())
			return $this->personastate;
		else return NULL;
	}


	/**
	 *	Get locallity code, if not private profile.
	 *	@return integer|NULL
	 */
	public function localityCode()
	{
		return $this->locstatecode;
	}


	/**
	 *	User is playing now?
	 *	@return boolean true|NULL
	 */
	public function isPlaying()
	{
		return $this->exists('gameid') ? true : NULL;
	}


	/**
	 *	Get name of game, currently playing.
	 *	@return string|NULL
	 */
	public function gameName()
	{
		return $this->gameextrainfo;
	}


	/**
	 *	Get appID of game, currently playing.
	 *	@return integer|NULL
	 */
	public function gameId()
	{
		return $this->gameid;
	}


	/**
	 *	Is private profile?
	 *	@return boolean True - private, false - public
	 */
	public function isPrivate()
	{
		return $this->communityvisibilitystate == 1 ? true : false;
	}


	/**
	 *	Download and save avatar image.
	 *	@param const string $size SteamPlayer::AVATAR_LARGE, SteamPlayer::AVATAR_MEDIUM, SteamPlayer::AVATAR_SMALL
	 *	@param string $filename /path/to/image.jpg
	 *	@return boolean
	 */
	public function saveAvatar($filename, $size = self::AVATAR_LARGE)
	{
		$url = $this->avatar($size);
		if (!($img = @file_get_contents($url))) {
			$error = error_get_last();
			throw new HttpSteamException($error['message']);
		}
		if (!@file_put_contents($filename, $img)) {
			$error = error_get_last();
			throw new FileSteamException($error['message']);
		}
		return true;
	}


	/**
	 *	Get link to avatar image.
	 *	@param const string $size SteamPlayer::AVATAR_LARGE, SteamPlayer::AVATAR_MEDIUM, SteamPlayer::AVATAR_SMALL
	 *	@return string
	 */
	public function avatar($size = self::AVATAR_LARGE)
	{
		return $this->$size;
	}


	/**
	 *	Object with all the properties.
	 *	@return Object
	 */
	public function get()
	{
		return $this->_cache;
	}


	/**
	 *	Get object with all properties without SteamPlayer class.
	 *	@param integer 64bit $steamID Steam Identifier 64 bit https://steamcommunity/profile/{identifier}
	 *	@return Object
	 */
	public static function getPlayer($steamID)
	{
		$players = static::getPlayers([$steamID]);
		if (!count($players)) return false;
		return array_shift($players);
	}


	/**
	 *	Get array of objects with all properties without SteamPlayer class.
	 *	@param array $steamIDs List of Steam Identifiers 
	 *		
	 *		Usage:
	 *		SteamPlayer::getPlayers(['xxxxxxxxxxxxxxxxxxxxxxxx', 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx']);
	 *
	 *	@return array of objects
	 */
	public static function getPlayers($steamIDs = [])
	{	
		$count = count($steamIDs);
		if (!$count || $count > 100 ) return false;
		$url = 'http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/';
		$params = ['steamids' => implode(',', $steamIDs)];
		$response = static::http($url, $params);
		if ($response === false) return false;
		return $response->players;
	}


	/**
	 *	Exists property of object.
	 *	@param string $property Property name
	 *	@return boolean
	 */
	protected function exists($property)
	{
		return property_exists($this->_cache, $property);
	}
}
