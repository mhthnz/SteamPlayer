<?php

/**
 * @class SteamException Base exception class
 */
class SteamException extends \Exception{}

/**
 *	@Class HttpSteamException At the request error
 */
class HttpSteamException extends SteamException{}

/**
 *	@Class InvalidParamsSteamException At the invalid params
 */
class InvalidParamsSteamException extends SteamException{}

/**
 *	@class FileSteamException At the error filsystem
 */
class FileSteamException extends SteamException{}




/**
 *	@Class SteamCore sending the request and response processing.
 *	@author R.Andrey https://github.com/mhthnz
 *
 *	@property string $API_KEY Key for access to Steam API
 */
class SteamCore {

	/**
	 *	@var Key for Steam API
	 */
	public static $API_KEY;


	/**
	 *	Sending request and response processing
	 *	@param string $url Link to request
	 *	@param array $params Params to request
	 *	@return json object
	 */
	public static function http($url, $params)
	{
		$request_uri = '?key='.static::$API_KEY.'&';
		foreach($params as $name => $value) {
			$request_uri .= $name.'='.$value.'&';
		}
		$context = stream_context_create([
		    'http' => array(
		        'ignore_errors' => true
		     )
		]);
		//processing response
		if (!$response = @file_get_contents($url. $request_uri, false, $context)) {
			$error = error_get_last();
			throw new HttpSteamException($error['message']);
		} else {
			if (!preg_match('/200 OK/', $http_response_header[0])) {
				self::generateException($response, $http_response_header[0]);
			}
		}
		$json = json_decode($response);
		if (!$json) return false;
		return $json;
	}


	/**
	 *	Generate exceptions.
	 *	@param string $body Body of response
	 *	@param string $code Code of response
	 */
	protected static function generateException($body, $code)
	{
		if (preg_match('/verify your <pre>([A-z_]+)/is', $body, $result))
			throw new InvalidParamsSteamException("Invalid parameter: ".$result[1]);
		if (preg_match('/required parameters/is', $body))
			throw new InvalidParamsSteamException("Missing required parameters.");			
		throw new HttpSteamException("Failed to send request, code: ".$code);
	}
}
