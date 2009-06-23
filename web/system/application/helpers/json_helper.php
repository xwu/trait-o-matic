<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

// ------------------------------------------------------------------------

/**
 * CodeIgniter JSON Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Evan Baliatico
 * @link		http://www.codeigniter.com/wiki/
 */

// ------------------------------------------------------------------------

// these functions are built-in in PHP 5.2+
if (function_exists('json_encode') && function_exists('json_decode'))
{
	return;
}
else
{
	// loading the helper automatically requires and instantiates the Services_JSON class
	if ( ! class_exists('Services_JSON'))
	{
		require_once(APPPATH.'helpers/json.php');
	}
	$json = new Services_JSON();

	/**
	 * json_encode
	 *
	 * Encodes php to JSON code. Parameter is the data to be encoded.
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function json_encode($data=null)
	{
		if ($data == null)
			return false;
		return $json->encode($data);
	}

	// ------------------------------------------------------------------------

	/**
	 * json_decode
	 *
	 * Decodes JSON code to php. Parameter is the data to be decoded.
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function json_decode($data=null)
	{
		if ($data == null)
			return false;
		return $json->decode($data);
	}

	// ------------------------------------------------------------------------
}


/* End of file json_helper.php */
/* Location: ./system/application/helpers/json_helper.php */