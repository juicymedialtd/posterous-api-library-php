Posterous v2 API Library for PHP 5
===============================
Current Version: 2.1
Author: Juicy Media Ltd (Peter Davies)

Notes
-----
This is an initial release of a PHP 5 library for the Posterous v2 API based on the work by Calvin Freitas.

The aim of this form is to provide a base library that Juicy can use with their Posterous Joomla component which is in the process of being developed (13/07/2011).

Demo Use
-----
$api = new PosterousAPIPosts($site,$token,$user,$pass);

try {
  $alldata = (!empty($tag))? $cache->call(array($api, 'readpostsbytag'), array('tag'=>$tag)) : array();
} catch (PosterousException $e) {
	echo 'Caught exception: ',  $e->getMessage(), "\n";
	exit();
}

print_r($alldata);


Links
-----
Get more information about:
Juicy Media Ltd: http://www.juicymedia.co.uk
Calvin Freitas at his website: http://calvinf.com/posterous-api-library-php/
