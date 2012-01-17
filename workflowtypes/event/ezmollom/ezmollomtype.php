<?php
// File containing the logic of eZ Mollom workflow
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

class eZMollomType extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = 'ezmollom';
    	
	function eZMollomType()
	{
	    $this->eZWorkflowEventType( eZMollomType::WORKFLOW_TYPE_STRING, ezpI18n::tr( 'extension/fumaggo/mollom/workflow/event', "Mollom Spam filter" ) );
	    $this->setTriggerTypes( array( 'content' => array( 'publish' => array( 'before' ) ), 'ezmollom' => array( 'ezcomment' => array( 'before') ) ) );
	}

	function execute( $process, $event )
	{
		$ini = eZINI::instance( 'ezmollom.ini', 'extension/ezmollom/settings' );
		$parameters = $process->attribute( 'parameter_list' );
		
		//ez publish objects
		if ( isset( $parameters['object_id'] ) )
		{
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
					
			$mollomObject = new eZContentObjectMollom();
			$params = $mollomObject->mollomInformationExtractor( $version );
			
		}
		
		//ezcomments
		if ( isset( $parameters['session_key'] ))
		{
			if ( isset( $parameters['text'] ) )
				$params['postBody'] = $parameters['text'];
			if ( isset( $parameters['title']) )
				$params['postTitle'] = $parameters['title'];
			if ( isset( $parameters['ip_address']) )
				$params['authorIp'] = $parameters['ip_address'];
			if ( isset( $parameters['name']) )
				$params['authorName'] = $parameters['name'];
			if ( isset( $parameters['name']) )
				$params['authorName'] = $parameters['name'];
			if ( isset( $parameters['email']) )
				$params['authorMail'] = $parameters['email'];
			if ( isset( $parameters['url']) )
				$params['authorUrl'] = $parameters['url'];
			if ( isset( $parameters['session_key']) )
				$params['id'] = $parameters['session_key'];
		}
			
		if ( !$params )
		{
			return eZWorkflowType::STATUS_ACCEPTED;		
		}
		
		$mollom = new eZMollom();
		$result = $mollom->checkContent( $params );
		
		//Content moderation system is currently unavailable
		if ( !is_array( $result ) || !isset( $result['id'] ) ) 
		{
				
			$this->setInformation( "The content moderation system is currently unavailable. Please try again later." );
			if ( isset( $parameters['object_id'] ) )
 			{
				$process->Template = array();
            	$process->Template['templateName'] = 'design:status.tpl';
            	$process->Template['templateVars'] = array ( 'status' => 'error', 'report' => $result, 'object' => $object  );
            	$version->setAttribute( 'status', eZContentObjectVersion::STATUS_DRAFT );
	        	$version->sync();
            	return eZWorkflowType::STATUS_FETCH_TEMPLATE;
 			}
 			else
 			{	
                return eZWorkflowType::STATUS_REJECTED;
 			}
		}
		
		$mollom_id = $result['id'];
			
		// Check the spam classification.
		switch ( $result['spamClassification'] ) 
		{
			case 'ham':
				//return eZWorkflowType::STATUS_ACCEPTED;
				//continue to check quality, profanity and sentiment below.
				break;

			case 'spam':
				$this->setInformation( "Your submission has triggered the spam filter and will not be accepted." );
				if ( isset($object) )
                {
					$process->Template = array();
                	$process->Template['templateName'] = 'design:status.tpl';
                	$process->Template['path'] = array( array( 'text' => ezpI18n::tr( 'fumaggo/design/ezmollom/workflow', 'Spam Filter' ), 'url' => false ) );
                	$process->Template['templateVars'] = array ( 'status' => 'spam', 'report' => $result, 'object' => $object, '$mollom_id' => $mollom_id  );
                	$version->setAttribute( 'status', eZContentObjectVersion::STATUS_DRAFT );
		            $version->sync();
                	return eZWorkflowType::STATUS_FETCH_TEMPLATE;
                }
                else
                {
                	return eZWorkflowType::STATUS_REJECTED;
                }
                break;
			
            	//TODO.
				case 'unsure':
					// Require to solve a CAPTCHA to get the post submitted.
					$captcha = $mollom->createCaptcha(array(
					  'contentId' => $result['id'],
					  'type' => 'image',
					));
				
					//TODO: decide what to do here. Redirect?
					if (!is_array($captcha) || !isset($captcha['id'])) {
						$this->setInformation( "The content moderation system is currently unavailable. Please try again later." );
						return eZWorkflowType::STATUS_REJECTED;
					}
					
					if ( isset( $parameters['object_id'] ) )
					{
						$process->Template['templateName'] = 'design:captcha.tpl';
	               		$process->Template['templateVars'] = array ( 'status' => 'unsure', 'report' => $result, 'image' => $captcha['url'], 'object' => $object, '$mollom_id' => $mollom_id );
	                  	$version->setAttribute( 'status', eZContentObjectVersion::STATUS_DRAFT );
		            	$version->sync();
		            	return eZWorkflowType::STATUS_FETCH_TEMPLATE;
	                }
	                else
	                {
	                	return eZWorkflowType::STATUS_REJECTED;		
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
	            	$process->Template['templateVars'] = array ( 'status' => 'profanity', 'report' => $result, 'object' => $object, '$mollom_id' => $mollom_id   );
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
			if ( $result['qualityScore'] < $ini->variable( 'MollomSettings', 'qualityMin' ) ) 
			{
				if ( isset( $parameters['object_id'] ) )
				{
					$process->Template = array();
	            	$process->Template['templateName'] = 'design:status.tpl';
	            	$process->Template['templateVars'] = array ( 'status' => 'quality', 'report' => $result, 'object' => $object, '$mollom_id' => $mollom_id   );
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
		
		// Check the sentiment score.
		if ( $ini->hasVariable( 'MollomSettings', 'sentimentMin' ) AND isset( $result['sentimentScore'] ) )
		{
			if ( $result['sentimentScore'] < $ini->variable( 'MollomSettings', 'sentimentMin' ) ) 
			{
				if ( isset( $parameters['object_id'] ) )
				{
					$process->Template = array();
		            $process->Template['templateName'] = 'design:status.tpl';
		            $process->Template['templateVars'] = array ( 'status' => 'sentiment', 'report' => $result, 'object' => $object, '$mollom_id' => $mollom_id   );
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
