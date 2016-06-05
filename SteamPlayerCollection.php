<?php


/**
 *	@class SteamPlayerCollection search in an array of objects	
 *	@author R.Andrey https://github.com/mhthnz
 *
 *	@property int $count An count objects in array.
 *	@property array $instances An array of objects SteamPlayer class.
 *	@property array $indexes An array of indexing objects for searching.
 *	@property array $sortingBy An array list of indexind type
 *
 */
class SteamPlayerCollection {

	/**
	 *	@var int count users
	 */
	private $_count = 0;

	/**
	 *	@var array list of SteamPlayer objects
	 */
	private $_instances = [];

	/**
	 *	@var array list of condition for searching
	 */
	private $_indexes = [];

	/**
	 *	@var array list of indexing type (name => SteamPlayer::method)
	 */
	protected static $_sortingBy = [
		'status' 	=>	['function' => 'getStatus'],
		'country' 	=>	['function' => 'countryCode'],
		'isplaying'	=>	['function' => 'isPlaying'],
		'locality'	=>	['function' => 'localityCode'],
		'game'		=>	['function' => 'gameId'],
		'private'	=>	['function' => 'isPrivate'],
	];

	/**
	 *	Class constructor.
	 *	@param array $instances List of SteamPlayer objects.
	 *	@param array $indexes If not first instance of SteamPlayerCollection then we get already indexed array.
	 */
	public function __construct(array $instances, $indexes = false)
	{
		$this->_instances = $instances;
		$this->_count = count($instances);
		if ($indexes !== false) {
			$this->_indexes = $indexes;
		} else {
			$this->indexInstances($instances);
		}
	}

	/**
	 *	Get all instances of SteamPlayer in current collection.
	 *	@return array SteamPlayer instances
	 */
	public function get()
	{	
		return $this->_instances;
	}


	/**
	 *	Get total count instances of SteamPlayer in current collection.
	 *	@return integer count SteamPlayer instances
	 */
	public function count()
	{
		return $this->_count;
	}


	/**
	 *	Search by country short code.
	 *	@param string $code Short name of country (RU, DE, etc)
	 *	@return SteamPlayerCollection object
	 */
	public function country($code)
	{
		return $this->searchInstances('country', $code);
	}



	/**
	 *	Search by locality code.
	 *	@param string $code Short id or short name of locality (3241, 53, FL, etc..)
	 *	@return SteamPlayerCollection object
	 */
	public function locality($code)
	{
		return $this->searchInstances('locality', $code);
	}


	/**
	 *	Search by private state of profile.
	 *	@return SteamPlayerCollection object
	 */	
	public function isPrivate()
	{
		return $this->searchInstances('private', 1);
	}


	/**
	 *	Search by private state of profile.
	 *	@return SteamPlayerCollection object
	 */	
	public function isPublic()
	{
		return $this->searchInstances('private', 0);
	}


	/**
	 *	Search by game in which is now playing.
	 *	@param integer|array $gameId App ID of game
	 *	@return SteamPlayerCollection object
	 */	
	public function inGame($gameId)
	{
		return $this->searchInstances('game', $gameId);
	}

	
	/**
	 *	Get every instance who is now playing.
	 *	@return SteamPlayerCollection object
	 */	
	public function isPlaying()
	{
		return $this->searchInstances('isplaying', 1);
	}


	/**
	 *	Get every instance whitch not has the following statuses.
	 *	@param array|integer $statuses Statuses which are excluded
	 *	@return SteamPlayerCollection object
	 */
	public function statusNot($statuses)
	{
		$allStatuses = [
			SteamPlayer::STATUS_OFFLINE, 
			SteamPlayer::STATUS_ONLINE, 
			SteamPlayer::STATUS_BUSY, 
			SteamPlayer::STATUS_AWAY, 
			SteamPlayer::STATUS_SNOOZE, 
			SteamPlayer::STATUS_LOOKING_TO_TRADE, 
			SteamPlayer::STATUS_LOOKING_TO_PLAY
		];
		if (!is_array($statuses))
			$statuses = [$statuses];
		$statuses = array_diff($allStatuses, $statuses);
		return $this->status($statuses);
	}


	/**
	 *	Get every instance which has the following statuses.
	 *	@param array|integer $statuses which are included
	 *	@return SteamPlayerCollection object
	 */
	public function status($statuses)
	{
		return $this->searchInstances('status', $statuses);
	}


	/**
	 *	Indexing come array of objects
	 *	@param array $instances array of SteamPlayer objects
	 */
	protected function indexInstances($instances)
	{
		foreach($this->_instances as $key => $instance) {
			foreach(self::$_sortingBy as $criteria => $option) {
				$value = $instance->$option['function']();
				if ($value !== NULL) {
					$this->_indexes[ $criteria ][ $value ][] = $key;
				}
			}
		}
	}



	/**
	 *	Search index by condition.
	 *	@param string $criteria Search criterion
	 *	@param string|array $values Search value
	 *	@return SteamPlayerCollection object
	 */
	protected function searchInstances($criteria, $values)
	{
		$ids = [];
		//if given array values, search by each value
		if (array_key_exists($criteria, $this->_indexes)) {
			if (is_array($values)) {
				foreach($values as $value) {
					if (array_key_exists($value, $this->_indexes[ $criteria ])) {
						$currentIds = $this->_indexes[ $criteria ][ $value ];
						$ids = array_merge($currentIds, $ids);
					}
				}
			} else { //string given
				if (array_key_exists($values, $this->_indexes[ $criteria ])) {
					$ids = array_merge($ids, $this->_indexes[ $criteria ][ $values ]);
				}
			}
			$ids = array_flip($ids);		
			$instances = [];
			$instances = array_intersect_key($this->_instances, $ids);
		}
		//get appropriate instances and return new static class
		return new static($instances, $this->_indexes);
	}	

}
