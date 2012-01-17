<?php
// File containing the eZMollom class
// SOFTWARE NAME: Mollom extension
// SOFTWARE RELEASE: 0.9
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

class eZMollom extends Mollom
{
	function __construct()
	{
		$ini = eZINI::instance( 'ezmollomconfig.ini' );
		$this->server = $ini->variable( 'MollomConfigSettings', 'server' );

		if ( $ini->hasVariable( 'MollomConfigSettings', 'publicKey' ) AND $ini->hasVariable( 'MollomConfigSettings', 'privateKey' ) )
		{
			if ( $ini->variable( 'MollomConfigSettings', 'publicKey' )!="" AND $ini->variable( 'MollomConfigSettings', 'privateKey' )!="" )
			{
				$this->publicKey = $ini->variable( 'MollomConfigSettings', 'publicKey' );
				$this->privateKey = $ini->variable( 'MollomConfigSettings', 'privateKey' );
			}
			else
			{
				$this->createKeys();
				$this->saveKeys();	
			}
		}
		else
		{
			//create a test account.
			$this->createKeys();
			$this->saveKeys();
		}
	}

	public function loadConfiguration( $name )
	{
		$ini = eZINI::instance( 'ezmollomconfig.ini' );
		if ( $ini->hasVariable( 'MollomConfigSettings', $name ) )
		{
			$name = $ini->variable( 'MollomConfigSettings', $name );
		}
		return $name;
	}

	public function saveConfiguration( $name, $value )
	{
		$ini = eZINI::instance( 'ezmollomconfig.ini', 'extension/ezmollom/settings' );
		$ini->setVariable( 'MollomConfigSettings', $name, $value );
		$ini->save();
		return $name;
	}

	public function deleteConfiguration( $name )
	{
		$ini = eZINI::instance( 'ezmollomconfig.ini', 'extension/ezmollom/settings' );
		$ini->setVariable( 'MollomConfigSettings', $name, '' );
		$ini->save();
		return $name;
	}

	public function getClientInformation()
	{
		$ezinfo = eZPublishSDK::version( true );
		$data = array(
	      'platformName' => 'eZ Publish',
	      'platformVersion' => $ezinfo,
	      'clientName' => 'eZMollom',
	      'clientVersion' => '0.9',
		);
		return $data;
	}

	function writeLog()
	{
		$messages = array();
		foreach ( $this->log as $i => $entry )
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
			$this->log[$i]['message'] = implode("\n", $output);
			unset( $this->log[$i]['arguments'] );
			$message = implode( '\n', $output );
			eZLog::write( $message, "ezmollom.log" );
		}
		$this->purgeLog();
	}

	protected function request( $method, $server, $path, $query = NULL, array $headers = array() )
	{
		$request = array(
	      'method' => $method,
	      'headers' => $headers,
	      'timeout' => $this->requestTimeout,
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
		$dhr = $this->httpRequest( $server . '/' . $path, $request );

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


	public function getWhitelist($publicKey = NULL)
	{
		if (!isset($publicKey)) {
			$publicKey = $this->publicKey;
		}
		$result = $this->query('GET', 'whitelist/' . $publicKey, array(), array('list'));
		return isset($result['list']) ? $result['list'] : $result;
	}

	public function getWhitelistEntry($entryId, $publicKey = NULL)
	{
		if (!isset($publicKey))
		{
			$publicKey = $this->publicKey;
		}
		$result = $this->query('GET', 'whitelist/' . $publicKey . '/' . $entryId, array(), array('entry', 'id'));
		return isset($result['entry']) ? $result['entry'] : $result;
	}
	 
	public function saveWhitelistEntry(array $data = array(), $publicKey = NULL)
	{
		if (!isset($publicKey))
		{
			$publicKey = $this->publicKey;
		}

		$path = 'whitelist/' . $publicKey;
		if (!empty($data['id']))
		{
			$path .= '/' . $data['id'];
			unset($data['id']);
		}
		$result = $this->query('POST', $path, $data, array('entry', 'id'));
		return isset($result['entry']) ? $result['entry'] : $result;
	}

	public function deleteWhitelistEntry($entryId, $publicKey = NULL)
	{
		if (!isset($publicKey)) {
			$publicKey = $this->publicKey;
		}
		$result = $this->query('POST', 'whitelist/' . $publicKey . '/' . $entryId . '/delete');
		return $this->lastResponseCode === TRUE;
	}

	//copy of Drupal timer_start function (see: http://api.drupal.org/api/drupal/includes--bootstrap.inc/function/timer_start/7)
	function timer_start( $name )
	{
		global $timers;

		$timers[$name]['start'] = microtime(TRUE);
		$timers[$name]['count'] = isset($timers[$name]['count']) ? ++$timers[$name]['count'] : 1;
	}

	//copy of Drupal timer_read function (see: http://api.drupal.org/api/drupal/includes--bootstrap.inc/function/timer_read/7)
	function timer_read( $name )
	{
		global $timers;

		if (isset($timers[$name]['start']))
		{
			$stop = microtime(TRUE);
			$diff = round(($stop - $timers[$name]['start']) * 1000, 2);

			if (isset($timers[$name]['time']))
			{
				$diff += $timers[$name]['time'];
			}
			return $diff;
		}
		return $timers[$name]['time'];
	}

	//copy of Drupal drupal_http_request function (see: http://api.drupal.org/api/drupal/includes--common.inc/function/drupal_http_request/7)
	public function httpRequest( $url, array $options = array() )
	{
		$result = new stdClass();

		// Parse the URL and make sure we can handle the schema.
		$uri = @parse_url( $url );

		if ( $uri == FALSE )
		{
			$result->error = 'unable to parse URL';
			$result->code = -1001;
			return $result;
		}

		if (!isset($uri['scheme']))
		{
			$result->error = 'missing schema';
			$result->code = -1002;
			return $result;
		}

		$this->timer_start(__FUNCTION__);

		// Merge the default options.
		$options += array(
		    'headers' => array(), 
		    'method' => 'GET', 
		    'data' => NULL, 
		    'max_redirects' => 3, 
		    'timeout' => 30.0, 
		    'context' => NULL,
		);
		 
		// stream_socket_client() requires timeout to be a float.
		$options['timeout'] = (float) $options['timeout'];

		switch ( $uri['scheme'] ) 
		{
			case 'http':
			case 'feed':
				$port = isset( $uri['port'] ) ? $uri['port'] : 80;
				$socket = 'tcp://' . $uri['host'] . ':' . $port;
				// RFC 2616: "non-standard ports MUST, default ports MAY be included".
				// We don't add the standard port to prevent from breaking rewrite rules
				// checking the host that do not take into account the port number.
				$options['headers']['Host'] = $uri['host'] . ( $port != 80 ? ':' . $port : '' );
				break;
			case 'https':
				// Note: Only works when PHP is compiled with OpenSSL support.
				$port = isset( $uri['port'] ) ? $uri['port'] : 443;
				$socket = 'ssl://' . $uri['host'] . ':' . $port;
				$options['headers']['Host'] = $uri['host'] . ( $port != 443 ? ':' . $port : '' );
				break;
			default:
				$result->error = 'invalid schema ' . $uri['scheme'];
				$result->code = -1003;
				return $result;
		}

		if ( empty( $options['context'] ) ) 
		{
			$fp = stream_socket_client($socket, $errno, $errstr, $options['timeout']);
		}
		else 
		{
			// Create a stream with context. Allows verification of a SSL certificate.
			$fp = stream_socket_client( $socket, $errno, $errstr, $options['timeout'], STREAM_CLIENT_CONNECT, $options['context'] );
		}

		// Make sure the socket opened properly.
		if ( !$fp ) 
		{
			// When a network error occurs, we use a negative number so it does not
			// clash with the HTTP status codes.
			$result->code = -$errno;
			$result->error = trim( $errstr ) ? trim( $errstr ) : t('Error opening socket @socket', array('@socket' => $socket));
			return $result;
		}

		// Construct the path to act on.
		$path = isset($uri['path']) ? $uri['path'] : '/';
		if (isset($uri['query'])) {
			$path .= '?' . $uri['query'];
		}


		// Merge the default headers.
		$options['headers'] += array('User-Agent' => 'Drupal (+http://drupal.org/)',);

		// Only add Content-Length if we actually have any content or if it is a POST
		// or PUT request. Some non-standard servers get confused by Content-Length in
		// at least HEAD/GET requests, and Squid always requires Content-Length in
		// POST/PUT requests.
		$content_length = strlen($options['data']);
		if ($content_length > 0 || $options['method'] == 'POST' || $options['method'] == 'PUT') {
			$options['headers']['Content-Length'] = $content_length;
		}

		// If the server URL has a user then attempt to use basic authentication.
		if (isset($uri['user'])) {
			$options['headers']['Authorization'] = 'Basic ' . base64_encode($uri['user'] . (isset($uri['pass']) ? ':' . $uri['pass'] : ''));
		}


		$request = $options['method'] . ' ' . $path . " HTTP/1.0\r\n";
		foreach ($options['headers'] as $name => $value) {
			$request .= $name . ': ' . trim($value) . "\r\n";
		}
		$request .= "\r\n" . $options['data'];
		$result->request = $request;

		// Calculate how much time is left of the original timeout value.
		$timeout = $options['timeout'] - $this->timer_read(__FUNCTION__) / 1000;
		if ($timeout > 0) {
			stream_set_timeout($fp, floor($timeout), floor(1000000 * fmod($timeout, 1)));
			fwrite($fp, $request);
		}

		// Fetch response. Due to PHP bugs like http://bugs.php.net/bug.php?id=43782
		// and http://bugs.php.net/bug.php?id=46049 we can't rely on feof(), but
		// instead must invoke stream_get_meta_data() each iteration.
		$info = stream_get_meta_data($fp);
		$alive = !$info['eof'] && !$info['timed_out'];
		$response = '';

		while ($alive) {
			// Calculate how much time is left of the original timeout value.
			$timeout = $options['timeout'] - $this->timer_read(__FUNCTION__) / 1000;
			if ($timeout <= 0) {
				$info['timed_out'] = TRUE;
				break;
			}
			stream_set_timeout($fp, floor($timeout), floor(1000000 * fmod($timeout, 1)));
			$chunk = fread($fp, 1024);
			$response .= $chunk;
			$info = stream_get_meta_data($fp);
			$alive = !$info['eof'] && !$info['timed_out'] && $chunk;
		}
		fclose($fp);

		if ($info['timed_out']) {
			$result->code = HTTP_REQUEST_TIMEOUT;
			$result->error = 'request timed out';
			return $result;
		}
		// Parse response headers from the response body.
		// Be tolerant of malformed HTTP responses that separate header and body with
		// \n\n or \r\r instead of \r\n\r\n.
		list($response, $result->data) = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);
		$response = preg_split("/\r\n|\n|\r/", $response);

		// Parse the response status line.
		list($protocol, $code, $status_message) = explode(' ', trim(array_shift($response)), 3);
		$result->protocol = $protocol;
		$result->status_message = $status_message;

		$result->headers = array();

		// Parse the response headers.
		while ($line = trim(array_shift($response))) {
			list($name, $value) = explode(':', $line, 2);
			$name = strtolower($name);
			if (isset($result->headers[$name]) && $name == 'set-cookie') {
				// RFC 2109: the Set-Cookie response header comprises the token Set-
				// Cookie:, followed by a comma-separated list of one or more cookies.
				$result->headers[$name] .= ',' . trim($value);
			}
			else {
				$result->headers[$name] = trim($value);
			}
		}

		$responses = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Time-out',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Large',
		415 => 'Unsupported Media Type',
		416 => 'Requested range not satisfiable',
		417 => 'Expectation Failed',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Time-out',
		505 => 'HTTP Version not supported',
		);
		// RFC 2616 states that all unknown HTTP codes must be treated the same as the
		// base code in their class.
		if ( !isset( $responses[$code] ) ) {
			$code = floor($code / 100) * 100;
		}
		$result->code = $code;

		switch ( $code ) {
			case 200: // OK
			case 304: // Not modified
				break;
			case 301: // Moved permanently
			case 302: // Moved temporarily
			case 307: // Moved temporarily
				$location = $result->headers['location'];
				$options['timeout'] -= timer_read(__FUNCTION__) / 1000;
				if ($options['timeout'] <= 0) {
					$result->code = HTTP_REQUEST_TIMEOUT;
					$result->error = 'request timed out';
				}
				elseif ( $options['max_redirects'] ) {
					// Redirect to the new location.
					$options['max_redirects']--;
					$result = httpRequest( $location, $options);
					$result->redirect_code = $code;
				}
				if ( !isset( $result->redirect_url ) ) {
					$result->redirect_url = $location;
				}
				break;
			default:
				$result->error = $status_message;
		}

		return $result;
	}

	public function checkKeys()
	{
		if ( !empty($this->publicKey) )
		{
			$result = $this->verifyKeys();
		}
		else
		{
			$result = self::AUTH_ERROR;
		}

		if ( $result === self::AUTH_ERROR )
		{
			$this->createKeys();
			$this->saveKeys();
		}
	}

	public function createKeys()
	{
		// Without any API keys, the client does not even attempt to perform a
		// request. Set dummy API keys to overcome that sanity check.
		$this->publicKey = 'public';
		$this->privateKey = 'private';

		// Skip authorization for creating testing API keys.
		$oAuthStrategy = $this->oAuthStrategy;
		$this->oAuthStrategy = '';

		$ini = eZINI::instance( 'site.ini' );
		$url = $ini->variable( 'SiteSettings', 'SiteURL');
		$email = $ini->variable( 'MailSettings', 'AdminEmail');
		$result = $this->createSite(array(
	      'url' => $url,
	      'email' => $email,
		));
		$this->oAuthStrategy = $oAuthStrategy;

		if ( is_array( $result ) && isset($result['publicKey'] ) )
		{
			$this->publicKey = $result['publicKey'];
			$this->privateKey = $result['privateKey'];
		}
		else
		{
			unset( $this->publicKey, $this->privateKey );
		}
	}

	public function saveKeys()
	{
		$this->saveConfiguration( 'publicKey', $this->publicKey );
		$this->saveConfiguration( 'privateKey', $this->privateKey );
	}
}
?>