<?php
// File containing the eZMollom class
// SOFTWARE NAME: Mollom extension
// SOFTWARE RELEASE: 1.0
// COPYRIGHT NOTICE: Copyright (C) 2011 Fumaggo  All rights reserved.
// SOFTWARE LICENSE: GNU General Public License v2.0
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.

/**
 * The eZMollom class provides functionality shared by the REST and XML-RPC interfaces
 * of Mollom. 
 */

class eZMollom
{
	function __construct()
	{
	}
	
	/**
	 * Function 'loadConfiguration' returns a setting in ezmollomconfig.ini.
	 * 	 
	 * * @param string $name
	 * The name of the setting to load
	 * 
	 * @return int
	 * On success, the value of the setting
	*/ 
	public static function loadConfiguration( $name )
	{
		$ini = eZINI::instance( 'ezmollomconfig.ini' );
		if ( $ini->hasVariable( 'MollomConfigSettings', $name ) )
		{
			$value = $ini->variable( 'MollomConfigSettings', $name );
			if ( isset( $value ) and $value!="" ) return $value;
		}
		return false;
	}
	
	/**
	 * Function 'saveConfiguration' saves a setting in ezmollomconfig.ini.
	 * 	 
	 * * @param string $name
	 * The name of the setting to save
	 * 
	 * * @param string $value
	 * The value to save. 
	 * 
	 * @return true
	 * On success, returns true
	*/ 
	public static function saveConfiguration( $name, $value )
	{
		$ini = eZINI::instance( 'ezmollomconfig.ini', 'extension/ezmollom/settings' );
		$ini->setVariable( 'MollomConfigSettings', $name, $value );
		$result = $ini->save();
		if ($result)
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Function 'deleteConfiguration' deletes a setting from ezmollomconfig.ini.
	 * 
	 * * @param string $name
	 * The name of the setting to delete
	 * 
	 * @return true
	 * On success, returns true
	*/ 
	public static function deleteConfiguration( $name )
	{
		$ini = eZINI::instance( 'ezmollomconfig.ini', 'extension/ezmollom/settings' );
		$ini->setVariable( 'MollomConfigSettings', $name, '' );
		$result = $ini->save();
		if ($result)
		{
			return true;
		}
		return false;
	}

	/**
	 * Function 'getClientInformation' returns information about the CMS (eZ Publish),
	 * the version of eZ Publish used, the eZMollom extension and the version of eZMollom.
	 * @return array
	 * On success, returns an array with information about the CMS and mollom extension used.
	*/ 
	public static function getClientInformation()
	{
		$ezinfo = eZPublishSDK::version( true );
		$data = array(
	      'platformName' => 'eZ Publish',
	      'platformVersion' => $ezinfo,
	      'clientName' => 'Fumaggo eZ Mollom extension',
	      'clientVersion' => '1.0',
		);
		return $data;
	}

	/**
	 * Function 'writeLog' saves information about the communication with the Mollom servers
	 * to /var/log/ezmollom.log. Both request and reply are logged.
	 * 
	 * * @param object $log
	 * The log entry to be written.
	 * 
	 * On success, returns true
	*/ 
	public static function writeLog( $log )
	{
		$messages = array();
		foreach ( $log as $i => $entry )
		{
			$entry += array( 'arguments' => array() );
			$message = array( $entry['message'] => $entry['arguments'],
			);

			if ( isset( $entry['request'] ) )
			{
				$message['Request: @request<pre>@parameters</pre>'] = array(
	          	'@request' => $entry['request'],
	          	'@parameters' => !empty( $entry['data'] ) ? $entry['data'] : '',
				);
			}
			if ( isset( $entry['headers'] ) )
			{
				$message['Request headers:<pre>@headers</pre>'] = array(
	          	'@headers' => $entry['headers'],
				);
			}
			if ( isset( $entry['response'] ) )
			{
				$message['Response:<pre>@response</pre>'] = array(
	          	'@response' => $entry['response'],
				);
			}
			$messages[] = $message;

			$output = array();
			foreach ( $message as $text => $args )
			{
				foreach ( $args as &$arg )
				{
					if ( is_array($arg) )
					{
						$arg = var_export( $arg, TRUE );
					}
				}
				$output[] = strtr( $text, $args );
			}
			$log[$i]['message'] = implode("\n", $output);
			unset( $log[$i]['arguments'] );
			$message = implode( '\n', $output );
			eZLog::write( $message, "ezmollom.log" );
		}
		return true;
	}

	/**
	 * Function 'request' performs a HTTP request to a Mollom server.
	 *
	 * @param string $method
	 *   The HTTP method to use; i.e., 'GET', 'POST', or 'PUT'.
	 * @param string $server
	 *   The base URL of the server to perform the request against; e.g.,
	 *   'http://foo.mollom.com'.
	 * @param string $path
	 *   The XMLRPC path/resource to request; e.g., 'site/1a2b3c'.
	 * @param string $query
	 *   (optional) A prepared string of HTTP query parameters to append to $path
	 *   for $method GET, or to use as request body for $method POST.
	 * @param array $headers
	 *   (optional) An associative array of HTTP request headers to send along
	 *   with the request.
	 *
	 * @return object
	 *   An object containing response properties:
	 *   - code: The HTTP status code as integer returned by the Mollom server.
	 *   - message: The HTTP status message string returned by the Mollom server,
	 *     or NULL if there is no message.
	 *   - headers: An associative array containing the HTTP response headers
	 *     returned by the Mollom server. Header name keys are expected to be
	 *     lower-case; i.e., "content-type" instead of "Content-Type".
	 *   - body: The HTTP response body string returned by the Mollom server, or
	 *     NULL if there is none.
	 *
	 * @see Mollom::handleRequest()
	 */
	public static function request( $method, $server, $path, $query = NULL, array $headers = array() )
	{
		$request = array(
	      'method' => $method,
	      'headers' => $headers,
	      'timeout' => 1.0,
		);

		if ( isset( $query ) )
		{
			if ( $method == 'GET' )
			{
				$path .= '?' . $query;
			}
			elseif ( $method == 'POST' )
			{
				$request['data'] = $query;
			}
		}
		
		$mollomhttprequest = new eZMollomHTTPRequest();
		$dhr = $mollomhttprequest->httpRequest( $server . '/' . $path, $request );

		$dhr->code = (int) $dhr->code;
			
		if ( $dhr->code >= 200 && $dhr->code < 300 )
		{
			unset( $dhr->error );
		}

		if ( !isset( $dhr->data ) )
		{
			$dhr->data = NULL;
		}

		if ( $dhr->code === 1 )
		{
			$dhr->code = -1;
		}

		$response = (object) array(
	      'code' => $dhr->code,
	      'message' => isset( $dhr->error ) ? $dhr->error : NULL,
	      'headers' => isset( $dhr->headers ) ? $dhr->headers : array(),
	      'body' => $dhr->data,
		);
		return $response;
	}
}
?>