<?php
// File containing the logic of whitelist view
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
$mollom = new eZMollom();

if ( $http->hasPostVariable( 'RemoveButton' )  )
{
   if ( $http->hasPostVariable( 'DeleteIDArray' ) )
    {
    	$deleteIDArray = $http->postVariable( 'DeleteIDArray' );
    	foreach ( $deleteIDArray as $deleteID )
        {
            $result = $mollom->deleteWhitelistEntry( $deleteID );
        }
    }
}

if ( $http->hasPostVariable( 'NewButton' )  )
{
    return $Module->redirectToView( 'editwhitelist', array() );
}

$Limit = 25;
$Offset = 0;

if ( isset( $Params['Offset'] ) )
	$Offset = $Params['Offset'];

if ( !is_numeric( $Offset ) )
	$Offset = 0;

if ( isset( $Params['Limit'] ) )
	$Limit = $Params['Limit'];

if ( !is_numeric( $Limit ) )
    	$Limit = 25;

$viewParameters = array( 'offset' => $Offset, 'limit' => $Limit );
$tpl->setVariable( "view_parameters", $viewParameters );

$whitelist = $mollom->getWhitelist();
$tpl->setVariable( "white_list", $whitelist );

$Result = array();
$Result['path'] = array(
    array( 'url' => false, 'text' => ezpI18n::tr( 'fumaggo/design/ezmollom/', 'Spam Filter' ) ),
    array( 'url' => false, 'text' => ezpI18n::tr( 'fumaggo/design/ezmollom/whitelist', 'Whitelist' ) )
);

$Result['content'] = $tpl->fetch( 'design:whitelist.tpl' );

?>