<?php
include "../SteamPlayer.php";

// Must set SteamApi key
SteamPlayer::$API_KEY = 'STEAM API KEY HERE';

// Find player by identifier
$steamID = '76561198088033749';
$player = SteamPlayer::Create($steamID);

// Show data
echo 'steam id '.$player->steamID()."<br/>\r\n";
echo 'nickname: '.$player->nickName()."<br/>\r\n";
echo 'realname: '.$player->realName()."<br/>\r\n";
echo 'countrycode: '.$player->countryCode()."<br/>\r\n";
echo 'localitycode: '.$player->localityCode()."<br/>\r\n";
echo 'avatar small: <img src="'.$player->avatar(SteamPlayer::AVATAR_SMALL).'">'."<br/>\r\n";
echo 'avatar medium: <img src="'.$player->avatar(SteamPlayer::AVATAR_MEDIUM).'">'."<br/>\r\n";
echo 'avatar large: <img src="'.$player->avatar(SteamPlayer::AVATAR_LARGE).'">'."<br/>\r\n";
echo 'private profile: '.($player->isPrivate() ? 'true' : 'false')."<br/>\r\n";
echo 'is playing: '. $player->isPlaying()."<br/>\r\n";
echo 'game name: '. $player->gameName()."<br/>\r\n";
echo 'game id: '. $player->gameId()."<br/>\r\n";

// Save avatar
$player->saveAvatar('small.jpg', SteamPlayer::AVATAR_SMALL);
$player->saveAvatar('medium.jpg', SteamPlayer::AVATAR_MEDIUM);
$player->saveAvatar('large.jpg', SteamPlayer::AVATAR_LARGE);

// Get original object
echo '<hr/>Original Object: <br/>';
var_dump($player->get());
echo "<hr/><br/>\r\n";


// Get other properties via magic method
echo 'lastlogoff: '.$player->lastlogoff."<br/>\r\n";
echo 'loccityid: '.$player->loccityid."<br/>\r\n";
echo 'primaryclanid: '.$player->primaryclanid."<br/>\r\n";
echo 'timecreated: '.$player->timecreated."<br/>\r\n";
