<?php 
/**
 * Posterous v2 API
 * Difference: REST based queries with JSON response
 * 
 * Based on the v1 API work by Calvin Freitas:
 * http://calvinf.com/projects/posterous-api-library-php/
 * 
 * Current available API calls:
 * http://posterous.com/api
 * 
 * @author 		Juicy Media Ltd (info@juicymedia.co.uk)
 * @package		PosterousAPI
 * @copyright	Copyright (C) 2005 - 2011 Juicy Media Ltd, All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// the current Posterous API end-point
define('POSTEROUS_API_URL', 'http://posterous.com/api/2/sites/');

// ensure Curl extension installed
if(!extension_loaded("curl")) {
	throw(new Exception("The cURL extension for PHP is required for PosterousAPI to work."));
}

// catch exceptions
class PosterousException extends Exception {}

// This class contains functions for calling the Posterous API
class PosterousAPI {
	
	private $_site_id;
	private $_token;
	private $_user;
	private $_pass;
	private $_timeout = 10;
	private $_connect_timeout = 10;
	private $_useragent = "Posterous PHP API 1.0";

	/**
	 * 
	 * Main class constructor setting base values used by API.
	 * Usage: $api = new PosterousAPI($site,$token,$user,$pass);
	 * 
	 * @param string $site_id
	 * @param string $token
	 * @param string $user
	 * @param string $pass
	 */
	function __construct($site_id = NULL, $token = NULL, $user = NULL, $pass = NULL) {
		$this->_set_site_id($site_id);
		$this->_set_token($token);
		$this->_set_user($user);
		$this->_set_pass($pass);
	}
	
	/**
	 * Get the site_id value 
	 * @return string
	 */
	private function _get_site_id(){
		return $this->_site_id;
	}
	
	/**
	 * Set the site_id value
	 * @param string $site_id
	 */
	private function _set_site_id($site_id){
		$this->_site_id = $site_id;
	}	
	
	/**
	 * Get the token value 
	 * @return string
	 */
	private function _get_token(){
		return $this->_token;
	}
	
	/**
	 * Set the token value
	 * @param string $token
	 */
	private function _set_token($token){
		$this->_token = $token;
	}	

	/**
	 * Get the user value 
	 * @return string
	 */
	private function _get_user(){
		return $this->_user;
	}
	
	/**
	 * Set the user value
	 * @param string $user
	 */
	private function _set_user($user){
		$this->_user = $user;
	}	
	
	/**
	 * Get the pass value 
	 * @return string
	 */
	private function _get_pass(){
		return $this->_pass;
	}
	
	/**
	 * Set the pass value
	 * @param string $pass
	 */
	private function _set_pass($pass){
		$this->_pass = $pass;
	}	
	
	/**
	 * Set the cURL timeout value
	 * @param integer $timeout
	 */
	public function _set_timeout($timeout){
		$this->_timeout = $timeout;
	}	

	/**
	 * Get the timeout value 
	 * @return integer
	 */
	public function _get_timeout(){
		return (int) $this->_timeout;
	}

	/**
	 * Set the cURL connect timeout value
	 * @param integer $ctimeout
	 */
	public function _set_connect_timeout($ctimeout){
		$this->_connect_timeout = $ctimeout;
	}	

	/**
	 * Get the connect timeout value 
	 * @return integer
	 */
	public function _get_connect_timeout(){
		return (int) $this->_connect_timeout;
	}		
	
	/**
	 * 
	 * This checks the passed argument array for
	 * matching 'valid' arguments
	 * 
	 * @param array $args
	 * @param array $valid_args
	 */
	public function _validate($args, $valid_args) {
		$method_args = array();
		foreach($args as $key => $value) {
			if( in_array($key, $valid_args) ) {
				$method_args[$key] = $value;
			}
		}
		return $method_args;
	}

	/**
	 * 
	 * This funciton is used to make the cURL call
	 * to the Posterous API.
	 * 
	 * TODO: Update error catching as this is untested
	 * TODO: Untested "post" element
	 * 
	 * @param string $api_method
	 * @param array $method_args
	 * @param string $call_method
	 */
	public function _call($api_method, $method_args = null, $call_method = null) {
		$method_url = POSTEROUS_API_URL .$this->_get_site_id()."/". $api_method;
		
		$method_args['site_id'] = $this->_get_site_id();
		$method_args['api_token'] = $this->_get_token();
		
		if (empty($method_args['site_id'])){
			throw new PosterousException('Error: missing site_id.');
		} elseif (empty($method_args['api_token'])){
			throw new PosterousException('Error: missing api_token.');
		}
				
		try {
			$ch = curl_init();
	        
			curl_setopt($ch, CURLOPT_USERAGENT, $this->_useragent); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_get_connect_timeout());
			curl_setopt($ch, CURLOPT_TIMEOUT, $this->_get_timeout());
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);	
			curl_setopt($ch, CURLOPT_USERPWD, $this->_get_user() . ':' . $this->_get_pass());
			
			// untested "post" element
			if ($call_method == 'post'){
				curl_setopt($ch, CURLOPT_URL, $method_url);
				curl_setopt($ch, CURLOPT_POST, 1);
				if ( is_array($method_args) && !empty($method_args) ) {
					curl_setopt($ch, CURLOPT_POSTFIELDS, $method_args);
				}				
			} else {
				// default action i.e. simple GET request
				// generate URL-encoded query string from array
				// Src: http://php.net/manual/en/function.http-build-query.php
				$urlparams = http_build_query($method_args);			
				curl_setopt($ch, CURLOPT_URL, $method_url."?".$urlparams);
			}
	 
			$response_data = curl_exec($ch);
			$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			
			if ($response_code <> 200){
				throw new PosterousException('Error Code ' . $response_code . ': cURL connection error to posterous.');
			}	
			curl_close($ch);	
		}
		catch (Exception $e) {
			throw $e;
		}				
		
		// apply UTF8 encode to make sure JSON decode will work
		$processed_data = json_decode(utf8_encode($response_data), true);
		
		if ($processed_data === null && json_last_error() !== JSON_ERROR_NONE) {
		    throw new PosterousException('Error: Invalid JSON response.');
		}
		
		return $processed_data;
	}	
}

class PosterousAPIPosts extends PosterousAPI {
	
	/**
	 * Duplicate of main class constructor setting base values used by API.
	 * Usage: $api = new PosterousAPIPosts($site,$token,$user,$pass);
	 * 
	 * @param string $site_id
	 * @param string $token
	 * @param string $user
	 * @param string $pass
	 */
	function __construct($site_id = NULL, $token = NULL, $user = NULL, $pass = NULL) {	
		parent::__construct($site_id, $token, $user, $pass);
	}
	
	/**
	 * This will obtain all posts based on the supplied "tag":
	 * Usage: $output = $api->readpostsbytag(array('tag'=>$tag));
	 *  
	 * @param array $json_assoc_array
	 */
	public function readpostsbytag($args) {
		$api_method = 'posts/public';

		$valid_args = array('since_id','page','tag');
		$method_args = $this->_validate($args, $valid_args);

		$json_assoc_array = $this->_call( $api_method, $method_args );
		return $json_assoc_array;
	}
	
	/**
	 * Get the tags of the given site
	 * NB: should be in own extended class - seems pointless at the minute
	 * 
	 * @return array $json_assoc_array
	 */
	public function gettags() {
		$api_method = 'tags';
		$json_assoc_array = $this->_call( $api_method, null );
		return $json_assoc_array;
	}		
	
	
}

?>