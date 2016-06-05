# SteamPlayer
Class for working with Steam Api. It consist of two main classes: `SteamPlayer`, `SteamPlayerCollection`.
* `SteamPlayer` is decorator for object with steam data, extending its addition funcionality.
* `SteamPlayerCollection` consist of SteamPlayer instances, give possibility for search instances according to some criteria.

# To start
You must set Steam Api Key:

```php
SteamPlayer::$API_KEY = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
```

# Easy to use
Get one object with Steam data:

```php
	$steamID = 'xxxxxxxxxxxxxxxxxxxxxxx';
	$object = SteamPlayer::getPlayer($steamID);
	echo $object->realname; // all properties: https://developer.valvesoftware.com/wiki/Steam_Web_API#GetPlayerSummaries_.28v0002.29
	.....
	.....
```


Get some objects in array, if given more than 100 ids they divided on part, 100 ids per request:

```php
	$steamIDs = ['xxxxxxxxxxxxxxxxxxxxxxx', 'xxxxxxxxxxxxxxxxxxxxxxxxxxx', 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'];
	$objects = SteamPlayer::getPlayers($steamIDs);
	foreach($objects as $object) {
		.......
	}
```

# Usage SteamPlayer
Load from list of steam identifiers:

```php
	$steamIDs = ['xxxxxxxxxxxxxxxxxxxx', 'xxxxxxxxxxxxxxxxxxxxx', 'xxxxxxxxxxxxxxxxxxxxxxxxxx', ....];
	$SteamPlayerCollection = SteamPlayer($steamIDs); //see below
```


Load from steam id:

```php
	$steamID = 'xxxxxxxxxxxxxxxxxxxxxxxxx';
	$instance = SteamPlayer::Create($steamID);

	echo $instance->avatar(SteamPlayer::AVATAR_LARGE); // Get link to large avatar from profile
	$instance->saveAvatar('/path/to/save/small.jpg', SteamPlayer::AVATAR_SMALL); // Download and save small avatar

	echo $instance->nickName(); // Get nickname from profile
	echo $instance->realName(); // Get realname from profile
	echo $instance->steamID();	// Get steam identifier 64 bit
	echo $instance->get();		// Get Steam Object with data, see up Easy to use
	echo $instance->Friends();	// Get friends list in SteamPlayerCollection instance

	echo $instance->realname; // Get by magic method
	echo $instance->steamid;	// Get by magic method
	echo $instance->primaryclanid; // Get by magic method

	/**
	 * If profile state not private we have possibility get status:
	 *
	 *	SteamPlayer::STATUS_OFFLINE 			//user offline
	 *	SteamPlayer::STATUS_ONLINE 				//user online
	 * 	SteamPlayer::STATUS_BUSY 				//user set status himself
	 *	SteamPlayer::STATUS_AWAY 				//set himself or afk
	 *	SteamPlayer::STATUS_SNOOZE 				//pc is sleep mode or long time afk
	 *	SteamPlayer::STATUS_LOOKING_TO_TRADE 	//looking to trade
	 *	SteamPlayer::STATUS_LOOKING_TO_PLAY		//looking to play
	 */
	if ($instance->getStatus() == SteamPlayer::STATUS_OFFLINE) {
		.....
	}

	$instance->isPrivate(); // If profile state is private - given true, is public - false

	// If profile state not private, else given NULL
	echo $instance->gameName();		// Name of game which currently playing
	echo $instance->gameId();		// App id of game which currently playing
	$instance->isPlaying();			// If user now play the game - true
	echo $instance->countryCode();	// Get short name of country (RU, US, UA, DE, etc...)
	echo $instance->localityCode();	// Get number of locality or short name (12, 4213, FL, etc...)
```

# Usage SteamPlayerCollection

```php
	// Load from list of steam identifiers
	$steamIDs = ['xxxxxxxxxxxxxxxxxxxx', 'xxxxxxxxxxxxxxxxxxxxx', 'xxxxxxxxxxxxxxxxxxxxxxxxxx', ....];
	$SteamPlayerCollection = SteamPlayer($steamIDs); //return instance of SteamPlayerCollection class

	echo $SteamPlayerCollection->count(); 		// Get count instances of SteamPlayer in collection
	$instances = $SteamPlayerCollection->get(); // Get an array of instances of SteamPlayer class
	foreach($instances as $player){
		echo $player->nickName();
	}

	// Get collection of Players, with the specified status
	$newOnlineSteamPlayerCollection = $SteamPlayerCollection->status(SteamPlayer::STATUS_ONLINE); 
	
	// Get collection of Players, excluding by specified statuses
	$newOtherSteamPlayerCollection = $SteamPlayerCollection->statusNot([SteamPlayer::STATUS_OFFLINE, SteamPlayer::STATUS_BUSY]);	
	
	// Get a collection of Players living in specified countries
	$newCountrySteamPlayerCollection = $SteamPlayerCollection->country(['RU', 'US']);

	// Get collection by profile state (private/public)
	$newPrivateSteamPlayerCollection = $SteamPlayerCollection->isPrivate();
	$newPublicSteamPlayerCollection = $SteamPlayerCollection->isPublic();

	// Get collection of Players which are playing in specified game
	$gameID = '21331'; // App identifier of game, may be array or string
	$newGameSteamPlayerCollection = $SteamPlayerCollection->inGame($gameID);

	// Get collection of Players which are playing in any of games
	$newPlayingSteamPlayerCollection = $SteamPlayerCollection->isPlaying();
```

# Exceptions
Base class of exception:
* `SteamException()`

Child classes:
* `HttpSteamException()` // When is request error
* `FileSteamException()` // When is error save file
* `InvalidParamsSteamException()` // When sending invalid params to request

**Examples:**

All steam exceptions:

```php
	try {
		some code...
	}
	catch(SteamException $error) {
		echo 'I have error: '.$error->getMessage();
	}
```


Save file:

```php
	try {
		$instance->saveAvatar('notfounddir/1.jpg');
	}
	catch(FileSteamException $error) {
		echo 'I can not save the file: '.$error->getMessage();
	}
```


Params error:

```php
	try {
		SteamPlayer::$API_KEY = 'invalid api key';
		SteamPlayer::getPlayer('xxxxxxxxxxxxxx');
	}
	catch(InvalidParamsSteamException $error) {
		echo 'Invalid params: '.$error->getMessage();
	}
```


Http error:
```php
	try {
		SteamPlayer::getPlayer('xxxxxxxxxxxxxx');
	}
	catch(HttpSteamException $error) {
		echo 'I can not send request: '.$error->getMessage();
	}
```

# Usage examples
Get game name of users from RU, EN, UA countries:

```php
	$steamIDs = ['xxxxxxxxxxxxxxxxxx', 'xxxxxxxxxxxxxxxxxxxxx', 'xxxxxxxxxxxxxxxxxxxxxxxxx', ....];
	$collection = SteamPlayer::Create($steamIDs);
	$newCollection = $collection->country(['RU', 'EN', 'UA'])->isPlaying();
	foreach($newCollection->get() as $SteamPlayer) {
		echo $SteamPlayer->gameName();
	}
```

Get users which playing in Dota2 and CS:GO from Ukraine:

```php
	$steamIDs = ['xxxxxxxxxxxxxxxxxx', 'xxxxxxxxxxxxxxxxxxxxx', 'xxxxxxxxxxxxxxxxxxxxxxxxx', ....];
	$collection = SteamPlayer::Create($steamIDs);
	$dota2AppId = 570;
	$csgoAppId = 730;
	$newCollection = $collection->inGame([$dota2AppId, $csgoAppId])->country('UA');
	foreach($newCollection->get() as $SteamPlayer) {
		echo $SteamPlayer->nickName(); // Get nickname
		echo $steamPlayer->realname; // Get by magic method
		echo $steamPlayer->steamid;	// Get by magic method
		echo $steamPlayer->primaryclanid; // Get by magic method
	}
```

	
Download avatar at users which from Russian Federation, Moskow or Krasnoyarsk and status not away:

```php
	$steamIDs = ['xxxxxxxxxxxxxxxxxx', 'xxxxxxxxxxxxxxxxxxxxx', 'xxxxxxxxxxxxxxxxxxxxxxxxx', ....];
	$collection = SteamPlayer::Create($steamIDs);
	$moskowID = '47';
	$krasnoyarskID = '39';
	$newCollection = $collection->country('RU')->locality([$moskowID, $krasnoyarskID])->statusNot(SteamPlayer::STATUS_AWAY);
	foreach($newCollection->get() as $SteamPlayer) {
		$SteamPlayer->saveAvatar($SteamPlayer->steamID().'.jpg', SteamPlayer::AVATAR_MEDIUM);
	}
```


Get single instance of SteamPlayer:

```php
	$steamID = 'xxxxxxxxxxxxxxxxxxxxxx';
	$instance = SteamPlayer::Create($steamID);
	echo $instance->lastlogoff; // Get by magic method
	echo $instance->avatar(SteamPlayer::AVATAR_SMALL); // Get link to small avatar
```

# Last Update
Added function for get the friend list

```php
	$friendsSteamPlayerCollection = $SteamPlayer->Friends();
	foreach($friendsSteamPlayerCollection->get() as $friend) {
		.....
	}
```