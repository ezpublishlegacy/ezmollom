<?php
// File containing the logic of module definition
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

$Module = array( 'name' => 'ezmollom' );
$ini = eZINI::instance( 'ezmollom.ini', 'extension/ezmollom/settings' );
if ( $ini->hasVariable( 'MollomSettings', 'handler' ) AND $ini->variable( 'MollomSettings', 'handler' ) == "rest" )
{
	$type = "rest";
}
else
{
	$type ="xmlrpc";
}

$ViewList = array();

$ViewList['site'] = array(
		'functions' => array( 'admin' ),
		'script' => 'site.php' );

$ViewList['content'] = array(
		'functions' => array( 'admin' ),
		'script' => 'content.php',
		'params' => array( 'ViewMode' ),
    	'unordered_params' => array( "offset" => "Offset" ) );

$ViewList['report'] = array(
		'functions' => array( 'admin' ),
		'script' => 'report.php',
		'params' => array( 'SessionID' ),
		'single_post_actions' => array( 'SpamButton' => 'Spam',
                                    	'HamButton' => 'Ham' ) );

$FunctionList['admin'] = array( );
$FunctionList['use'] = array( );


?>
