<?php
include "../SteamPlayer.php";

// Must set SteamApi key
SteamPlayer::$API_KEY = 'STEAM API KEY HERE';

// Find player by identifier
$steamID = '76561198088033749';
$player = SteamPlayer::Create($steamID);

// Get friend list of current user
$friendsSteamPlayersCollection = $player->Friends();
echo 'count friends: '.$friendsSteamPlayersCollection->count().'<hr/>';


############################	 Showing each friend 	##########################

foreach($friendsSteamPlayersCollection->get() as $friend) {
	echo $friend->nickName().'<br/>';
}

echo '<hr/>';


##################    Get friends which are living in RU and PE 	########################

$newCollection = $friendsSteamPlayersCollection->country(['RU', 'PE']);
foreach($newCollection->get() as $friend) {
	echo $friend->nickName().' | '.$friend->countryCode().'<br/>';
}

echo '<hr/>';


######################### Get friends which are playing in any of games and status is not away	##################

$newCollection = $friendsSteamPlayersCollection->statusNot(SteamPlayer::STATUS_AWAY)->isPlaying();
foreach($newCollection->get() as $friend) {
	echo $friend->nickName().' | '.$friend->gameName().'<br/>';
}


