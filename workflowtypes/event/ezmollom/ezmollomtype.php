<?php
// File containing the logic of eZ Mollom workflow
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
 * The eZMollomType class provides a spam filter workflow
 */
class eZMollomType extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = 'ezmollom';
    const CAPTCHA_CHECK = 0;
    	  
	function eZMollomType()
	{
	    $this->eZWorkflowEventType( eZMollomType::WORKFLOW_TYPE_STRING, ezpI18n::tr( 'extension/fumaggo/mollom/workflow/event', "Mollom Spam filter" ) );
	    $this->setTriggerTypes( array( 'content' => array( 'publish' => array( 'before' ) ), 'ezmollom' => array( 'ezcomment' => array( 'before'), 'ezcollect' => array( 'before') ) ) );
	}

	/**
	 * Workflow that sends the parameters to Mollom and return analysis.
	 */
	function execute( $process, $event )
	{
		$ini = eZINI::instance( 'ezmollom.ini', 'extension/ezmollom/settings' );
		$parameters = $process->attribute( 'parameter_list' );
		$id = $error = false;
		$object_id = $session_key = $collection_id = $comment_key = false;
		$collection = $version = $object = $comment = $captcha_retry = false;
		
		$extractor = new eZMollomExtractor();
		$mollom = eZMollomHandler::instance();
		$http = eZHTTPTool::instance();
		$result = array();
			
		//check answer to captcha
		if ( $http->hasPostVariable( 'captcha' ) and $http->hasPostVariable( 'id' ) )
		{
			$session_id = $http->postVariable( 'id' );
			$solution = $http->postVariable( 'captcha' );
			
			$result['id'] = $session_id;
			$result['spamClassification'] = "unsure";
			
			$captcha = $mollom->checkCaptcha(array(
				  'sessionID' => $session_id,
				  'solution' => $solution
				));
			
			if ( $captcha )
			{
				eZMollomLog::updateBySessionID( $session_id, "ham" );
				return eZWorkflowType::STATUS_ACCEPTED;	
			}
			else
			{
				$captcha_retry = true;	
			}
		}       
		
		// 1. extract information from content objects
		if ( isset( $parameters['object_id'] ) )
		{
			$object_id = $parameters['object_id'];
			$object = eZContentObject::fetch( $parameters['object_id'] );
			if ( !$object )
			{
			    return eZWorkflowType::STATUS_WORKFLOW_CANCELLED;
			}
			
			$versionID = $parameters['version'];
			$version = $object->version( $versionID );
			if ( !$version )
			{
			    return eZWorkflowType::STATUS_WORKFLOW_CANCELLED;
			}
			
			$user_id = $object->attribute( 'owner_id' );
			$name = $object->attribute( 'name' );
			$item = $extractor->objectInformationExtractor( $version );			
			$params = $item['params'];
			$identifier = "object";
		}
		
		// 2. extract information from ezcomments extension
		if ( isset( $parameters['comment'] ))
		{
			$comment = $parameters['comment'];
			$parameters['name'] = $comment['name'];
			$parameters['email'] =  $comment['email'];
			$parameters['url'] =  $comment['website'];
			$parameters['text'] =  $comment['comment'];
			$parameters['title'] = $name = $comment['title'];
			$comment['created'] = time();
			$session_key = $parameters['session_key'];
			$parameters['id'] = $session_key;
			$user_id = $parameters['user_id'];
			$item = $extractor->eZcommentsInformationExtractor( $parameters );
			$params = $item['params'];
			$identifier = "ezcomment";
		}
		
		if ( isset( $parameters['collection'] ))
		{
			$collection = $parameters['collection'];
			if ( !$collection )
				return eZWorkflowType::STATUS_WORKFLOW_CANCELLED;
			$collection_id = $collection->attribute( 'id' );
			
			$isObject = eZContentObject::fetch( $collection->attribute( 'contentobject_id' ) );
			if( !$isObject )
			    return eZWorkflowType::STATUS_WORKFLOW_CANCELLED;
			
			$item = $extractor->eZcollectionInformationExtractor( $collection_id, $collection );			
			$params = $item['params'];
			$name = $isObject->attribute('name');
			$user_id = $collection->attribute( 'creator_id' );
			$identifier = "infocollection";	
		}
				
		if ( !$params )
		{
			return eZWorkflowType::STATUS_ACCEPTED;		
		}

		if ( !isset($result['id']) AND !isset($params['error']) )
			$result = $mollom->checkContent( $params );

		//Content moderation system is currently unavailable
		if ( isset( $result['session_id'] ) ) $result['id'] = $result['session_id'];
		if ( !is_array( $result ) || !isset( $result['id'] ) ) 
		{
                if ( $version )
				{
					$version->setAttribute( 'status', eZContentObjectVersion::STATUS_DRAFT );
		        	$version->sync();
				}
				$process->Template['templateName'] = 'design:status.tpl';
               	$process->Template['templateVars'] = array ( 'status' => 'unavailable', 'comment' => $comment, 'version' => $version, 'collection' => $collection );
               	              	
               	return eZWorkflowType::STATUS_FETCH_TEMPLATE_REPEAT;	
                break;
		}
		
		//Log the classification in the Mollom log table
		$log_params = array( 'type' => $identifier, 'name' => $name, 'content' => base64_encode(gzcompress(serialize($params))), 'session_id' => $result['session_id'], 'status' => $result['spamClassification'], 'user_id' => $user_id, 'spam_score' => (isset($result['spamScore']) ? $result['spamScore'] : false), 'profanity_score' => (isset($result['profanityScore']) ? $result['profanityScore'] : false), 'quality_score' => (isset($result['qualityScore']) ? $result['qualityScore'] : false), 'sentiment_score' => (isset($result['sentimentScore']) ? $result['sentimentScore'] : false), 'object_id' => $object_id, 'comment_session' => $session_key, 'collection_id' => $collection_id  );		
		$ezmollom_log = eZMollomLog::create( $log_params );
		$ezmollom_log->store();

		// Check the spam classification.
		switch ( $result['spamClassification'] ) 
		{
			case 'ham':
				//return eZWorkflowType::STATUS_ACCEPTED;
				//continue to check quality, profanity and sentiment below.
				break;

			case 'spam':
				$process->Template['templateName'] = 'design:status.tpl';
               	$process->Template['templateVars'] = array (  'event' => $event, 'status' => 'spam', 'result' => $result, 'params' => $params, 'object' => $object, 'comment' => $comment, 'collection' => $collection, 'name' => $name );
               	$process->setAttribute( 'event_state', eZWorkflowType::STATUS_REJECTED );
               	return eZWorkflowType::STATUS_FETCH_TEMPLATE;	
                break;
			
			case 'unsure':
				//CAPTCHA is supported only for content objects 
				if ( $identifier == "object" )
				{	
					// Require to solve a CAPTCHA to get the post submitted.
					$captcha = $mollom->getImageCaptcha(array(
					  'sessionID' => $result['id'],
					  'type' => 'image',
					  'ssl' => false
					));
					
					if ( !is_array( $captcha ) || !isset( $captcha['id'] ) ) {
						$captcha = false;
					}
					
	                $process->Template['templateName'] = 'design:status.tpl';
	               	$process->Template['templateVars'] = array ( 'status' => 'unsure', 'result'=> $result, 'captcha' => $captcha, 'version' => $version, 'object' => $object, 'comment' => $comment, 'comment_key' => $comment_key, 'collection' => $collection, 'redirect_uri' => $redirectURI, 'retry' => $captcha_retry );
	                return eZWorkflowType::STATUS_FETCH_TEMPLATE_REPEAT;
				}
				else
				{
					$process->Template['templateName'] = 'design:status.tpl';
	               	$process->Template['templateVars'] = array (  'event' => $event, 'status' => 'spam', 'result' => $result, 'params' => $params, 'object' => $object, 'comment' => $comment, 'collection' => $collection, 'name' => $name );
	               	$process->setAttribute( 'event_state', eZWorkflowType::STATUS_REJECTED );
	               	return eZWorkflowType::STATUS_FETCH_TEMPLATE;	
				}	
                break;

			default:
				// If we end up here, Mollom responded with a unknown spamClassification.
				return eZWorkflowType::STATUS_WORKFLOW_CANCELLED;
				break;
		}
		
		
		// Check the profanity score.
		if ( $ini->hasVariable( 'MollomSettings', 'profanityMax' ) AND isset( $result['profanityScore'] ) )
		{
			if ( $result['profanityScore'] > $ini->variable( 'MollomSettings', 'profanityMax' ) ) 
			{
				if ( isset( $parameters['object_id'] ) )
                {
					$process->Template = array();
	            	$process->Template['templateName'] = 'design:status.tpl';
	            	$process->Template['templateVars'] = array (  'event' => $event, 'status' => 'profanity', 'result' => $result, 'params' => $params, 'object' => $object, 'comment' => $comment, 'collection' => $collection );	            	
	            	$version->setAttribute( 'status', eZContentObjectVersion::STATUS_DRAFT );
	           		$version->sync();
	           		return eZWorkflowType::STATUS_FETCH_TEMPLATE;	
                }
                else
                {
                	return eZWorkflowType::STATUS_REJECTED;	
                }
	           
			}
		}
		
		// Check the quality score.
		if ( $ini->hasVariable( 'MollomSettings', 'qualityMin' ) AND isset( $result['qualityScore'] ) )
		{
			if ( ( number_format( $result['qualityScore'],2 ) < $ini->variable( 'MollomSettings', 'qualityMin' ) ) )
			{
				if ( isset( $parameters['object_id'] ) )
				{
					$process->Template = array();
	            	$process->Template['templateName'] = 'design:status.tpl';
					$process->Template['templateVars'] = array (  'event' => $event, 'status' => 'quality', 'result' => $result, 'params' => $params, 'object' => $object, 'comment' => $comment, 'collection' => $collection );	            		           		$version->setAttribute( 'status', eZContentObjectVersion::STATUS_DRAFT );
	            	$version->sync();
	            	return eZWorkflowType::STATUS_FETCH_TEMPLATE;	
				}
				else
				{
					return eZWorkflowType::STATUS_REJECTED;		
				}
			}	
		}
		
		// Check the sentiment score.
		if ( $ini->hasVariable( 'MollomSettings', 'sentimentMin' ) AND isset( $result['sentimentScore'] ) )
		{
			if ( ( number_format( $result['sentimentScore'],2 ) < $ini->variable( 'MollomSettings', 'sentimentMin' ) ) ) 
			{
				if ( isset( $parameters['object_id'] ) )
				{
					$process->Template = array();
		            $process->Template['templateName'] = 'design:status.tpl';
		            $process->Template['templateVars'] = array (  'event' => $event, 'status' => 'sentiment', 'result' => $result, 'params' => $params, 'object' => $object, 'comment' => $comment, 'collection' => $collection );	            	
	            	$version->setAttribute( 'status', eZContentObjectVersion::STATUS_DRAFT );
	            	$version->sync();
	            	return eZWorkflowType::STATUS_FETCH_TEMPLATE;
				}
				else
				{
					return eZWorkflowType::STATUS_REJECTED;			
				}
			}		
		}
		
		return eZWorkflowType::STATUS_ACCEPTED;
	}
}
eZWorkflowEventType::registerEventType( eZMollomType::WORKFLOW_TYPE_STRING, 'ezmollomtype' );
?>
