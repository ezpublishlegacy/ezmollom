<?php
// File containing the logic of editblacklist view
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

$http = eZHTTPTool::instance();
$Module = $Params["Module"];
$tpl = eZTemplate::factory(); 
$ID = $Params['ID'];
$params = array();
$http = eZHTTPTool::instance();
$mollom = new eZMollom();

if ( !$mollom )
	return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );

if ( $http->hasPostVariable( "DiscardButton" ) )
{
    $Module->redirectTo( $Module->functionURI( "blacklist" ) );
    return;
}

if ( $http->hasPostVariable( "StoreButton" ) )
{
	if ( $http->hasPostVariable( "ID" ) )
	{
		$ID = $http->postVariable( "ID" );
		$params['id'] = $http->postVariable( "ID" );
	}
	
	if ( $http->hasPostVariable( "Value" ) )
	{
		$params['value'] = $http->postVariable( "Value" );
		
		if ( trim( $params['value'] ) == "" )
		{
			$error = true;
		}
	}
	
	if ( $http->hasPostVariable( "Context" ) )
	{
		$params['context'] = $http->postVariable( "Context" );
	}
	
	if ( $http->hasPostVariable( "Match" ) )
	{
		$params['match'] = $http->postVariable( "Match" );
	}
	
	if ( $http->hasPostVariable( "Reason" ) )
	{
		$params['reason'] = $http->postVariable( "Reason" );
	}
	
	if ( $http->hasPostVariable( "Status" ) )
	{
		$params['status'] = (int)$http->postVariable( "Status" );
	}
	
	if ( $http->hasPostVariable( "Note" ) )
	{
		$params['note'] = $http->postVariable( "Note" );
	}
	   
    $entry = $mollom->saveBlacklistEntry( $params );	
    
    if ( $entry['id'] )
    {
    	$feedback = true;
    }
}

if ( !isset( $entry ) )
	$entry = $mollom->getBlacklistEntry($ID);
	
if ( $entry )
{
	$tpl->setVariable( "entry", $entry );
}
else
{
	return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

if ( $error )
	$tpl->setVariable( "error", true );

if ( $feedback )
	$tpl->setVariable( "feedback", true );
	
$Result = array();
$Result['path'] = array(
    array( 'url' => false, 'text' => ezpI18n::tr( 'fumaggo/design/ezmollom/', 'Spam Filter' ) ),
    array( 'url' => 'ezmollom/blacklist', 'text' => ezpI18n::tr( 'fumaggo/design/ezmollom/blacklist', 'Blacklist' ) ),
    array( 'url' => false, 'text' => ezpI18n::tr( 'fumaggo/design/ezmollom/blacklist', 'Edit blacklist entry' ) )
);

$Result['content'] = $tpl->fetch( 'design:editblacklist.tpl' );

?>