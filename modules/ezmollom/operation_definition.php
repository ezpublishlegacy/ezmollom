<?php
// File containing the logic of operationlist definition
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

$OperationList = array();
$OperationList['ezcomment'] = array( 'name' => 'ezcomment',
                                        'default_call_method' => array( 'include_file' => 'extension/ezmollom/classes/ezmollomoperationcollection.php',
                                                                        'class' => 'eZMollomOperationCollection' ),
                                        'parameter_type' => 'standard',
                                        'parameters' => array( array( 'name' => 'session_key',
                                                                      'type' => 'string',
                                                                      'required' => true ),
																array( 'name' => 'comment',
                                                                      'type' => 'object',
                                                                      'required' => true ),
																array( 'name' => 'user_id',
                                                                      'type' => 'integer',
                                                                      'required' => true )
                                                               ),
                                        'keys' => array( 'session_key' ),
                                        'body' => array( array( 'type' => 'trigger',
                                                                'name' => 'pre_ezcomment',
                                                                'keys' => array( 'session_key' ) )));

                                                               
$OperationList['ezcollect'] = array( 'name' => 'ezcollect',
                                        'default_call_method' => array( 'include_file' => 'extension/ezmollom/classes/ezmollomoperationcollection.php',
                                                                        'class' => 'eZMollomOperationCollection' ),
                                        'parameter_type' => 'standard',
                                        'parameters' => array( array( 'name' => 'collection_id',
                                                                      'type' => 'string',
                                                                      'required' => true ),
																array( 'name' => 'collection',
                                                                      'type' => 'object',
                                                                      'required' => true )
                                                               ),
                                        'keys' => array( 'collection_id' ),
                                        'body' => array( array( 'type' => 'trigger',
                                                                'name' => 'pre_ezcollect',
                                                                'keys' => array( 'collection_id' ) )));

                                                               
                                                               
?>