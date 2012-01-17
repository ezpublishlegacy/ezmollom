<?php
// File containing the eZContentObjectMollom class
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

class eZContentObjectMollom
{
    /*!
     \static
     Returns an instance of a content object mollom information extractor
     suitable for the content object version $contentObjectVersion. 
     Builds an associative array containing any of the keys:
	   *   - id: The existing content ID of the content, if it or a variant or
	   *     revision of it has been checked before.
	   *   - postTitle: The title of the content.
	   *   - postBody: The body of the content. If the content consists of multiple
	   *     fields, concatenate them into one postBody string, separated by " \n"
	   *     (space and line-feed).
	   *   - authorName: The (real) name of the content author.
	   *   - authorUrl: The homepage/website URL of the content author.
	   *   - authorMail: The e-mail address of the content author.
	   *   - authorIp: The IP address of the content author.
	   *   - authorId: The local user ID on the client site of the content author.
	   *   - authorOpenid: An indexed array of Open IDs of the content author.
	   *   - checks: An indexed array of strings denoting the checks to perform, one
	   *     or more of: 'spam', 'quality', 'profanity', 'language', 'sentiment'.
	   *     Defaults to 'spam'.
	   *   - unsure: Integer denoting whether a "unsure" response should be allowed
	   *     (1) for the 'spam' check (which should lead to CAPTCHA) or not (0).
	   *     Defaults to 1.
	   *   - strictness: A string denoting the strictness of Mollom checks to
	   *     perform; one of 'strict', 'normal', or 'relaxed'. Defaults to 'normal'.
	   *   - rateLimit: Seconds that must have passed by for the same author to post
	   *     again. Defaults to 15.
	   *   - honeypot: The value of a client-side honeypot form element, if
	   *     non-empty.
	   *   - stored: Integer denoting whether the content has been stored (1) on the
	   *     client-side or not (0). Use 0 during form validation, 1 after
	   *     successful submission. Defaults to 0.
	   *   - url: The absolute URL to the stored content.
	   *   - contextUrl: An absolute URL to parent/context content of the stored
	   *     content; e.g., the URL of the article or forum thread a comment is
	   *     posted on (not the parent comment that was replied to).
	   *   - contextTitle: The title of the parent/context content of the stored
	   *     content; e.g., the title of the article or forum thread a comment is
	   *     posted on (not the parent comment that was replied to).
    */
    function mollomInformationExtractor( $version )
    {
		$ini = eZINI::instance( 'ezmollom.ini' );
		$infoExtractors = $ini->variable( 'InformationExtractorSettings', 'ExtractableClasses' );
		$debug = eZDebug::instance();
		$params = array();
	
		$contentObject = $version->attribute( 'contentobject' );
		$contentObjectID = $contentObject->attribute( 'id' );		
		
		$params['id'] = $contentObjectID;
		$params['postTitle'] = $contentObject->attribute( 'name' );
		
		$classIdentifier = $contentObject->attribute( 'class_identifier' );
		if ( !in_array( $classIdentifier, $infoExtractors ) )
		{
		    return false;
		}
	
		$iniGroup = $classIdentifier.'_MollomSettings';
		if ( !$ini->hasGroup( $iniGroup ) )
		{
	    	return false;
        }
        
    	if ( $ini->hasVariable( $classIdentifier . '_MollomSettings', 'postBody' ) )
		{
			$bodyIdentifier = $ini->variable( $classIdentifier . '_MollomSettings', 'postBody' );
		}
		else 
		{
			$bodyIdentifier = false;
		}
            		
		if ( $ini->hasVariable( $classIdentifier . '_MollomSettings', 'authorName' ) )
		{
			$authorIdentifier = $ini->variable( $classIdentifier . '_MollomSettings', 'authorName' );
		} else
		{
			$authorIdentifier = false;
		}
	
	    if ( $ini->hasVariable( $classIdentifier . '_MollomSettings', 'authorMail' ) )
	    {
			$emailIdentifier = $ini->variable( $classIdentifier . '_MollomSettings', 'authorMail' );
	    }
	    else 
	    {
	    	$emailIdentifier = false;
	    }
	
		if ( $ini->hasVariable( $classIdentifier . '_MollomSettings', 'authorUrl' ) )
		{
			$authorURLIdentifier = $ini->variable( $classIdentifier . '_MollomSettings', 'authorUrl' );
		}
		else 
		{
			$authorURLIdentifier = false;	
		}
	
		
		
		$attributeIdentifiers = array( 'postBody' => $bodyIdentifier, 'authorName' => $authorIdentifier, 'authorUrl' => $authorURLIdentifier, 'authorMail' => $emailIdentifier );
		$contentObjectAttributes = $contentObject->contentObjectAttributes();
		$loopLenght = count( $contentObjectAttributes );
		for( $i = 0; $i < $loopLenght; $i++ )
		{
			if ( in_array($contentObjectAttributes[$i]->attribute( 'contentclass_attribute_identifier' ), array_values( $attributeIdentifiers ) ) )
			{
				$key = array_search($contentObjectAttributes[$i]->attribute( 'contentclass_attribute_identifier' ), $attributeIdentifiers); 
				if ( $contentObjectAttributes[$i]->hasContent() )
				{
					$value = $contentObjectAttributes[$i]->attribute( 'content' );
					
					switch ( $datatypeString = $contentObjectAttributes[$i]->attribute( 'data_type_string' ) )
					{
					
						case 'ezauthor':
						{
							 foreach ( $value->attribute( 'author_list') as $author )
							 {
								$params['authorName'] = strip_tags($author['name']);
								$params['authorMail'] = $author['email'];
								if ( $author['id'] != 0 ) $params['authorId'] = $author['id'];
								continue;
							 }
						}
						break;
						
						case 'ezxml':
						{						
							if ( $value instanceof eZXMLText )
							{
								$outputHandler =  $value->attribute( 'output' );
								$itemDescriptionText = $outputHandler->attribute( 'output_text' );
								$value = substr(strip_tags($itemDescriptionText),0,1000);
							}
							$params[$key] = $value;
						}
						break;
						
						default:
						{
							$params[$key] = strip_tags($value);
						}
						break;
					}
					
					unset( $attributeIdentifiers[$key] );
					
				}
				else
				{
					$params[$key] = false;
				}
				
			}		
		}
		
    	if ( $ini->hasVariable( $classIdentifier . '_MollomSettings', 'checks' ) )
		{
			$params['checks'] = $ini->variable( $classIdentifier . '_MollomSettings', 'checks' );
		}
		else
		{
			$params['checks'] = $ini->variable( 'MollomSettings', 'checks' );
		}
		
		if ( $ini->hasVariable( $classIdentifier . '_MollomSettings', 'unsure' ) )
		{
			$params['unsure'] = $ini->variable( $classIdentifier . '_MollomSettings', 'unsure' );	
		}
		else
		{
			$params['unsure'] = $ini->variable( 'MollomSettings', 'unsure' );
		}
		
    	if ( $ini->hasVariable( $classIdentifier . '_MollomSettings', 'strictness' ) )
		{
			$params['strictness'] = $ini->variable( $classIdentifier . '_MollomSettings', 'strictness' );	
		}
		else
		{
			$params['strictness'] = $ini->variable( 'MollomSettings', 'strictness' );
		}
		
    	if ( $ini->hasVariable( $classIdentifier . '_MollomSettings', 'rateLimit' ) )
		{
			$params['rateLimit'] = $ini->variable( $classIdentifier . '_MollomSettings', 'rateLimit' );	
		}
		else
		{
			$params['rateLimit'] = $ini->variable( 'MollomSettings', 'rateLimit' );
		}
		
    	if ( $ini->hasVariable( $classIdentifier . '_MollomSettings', 'honeypot' ) )
		{
			$params['rateLimit'] = $ini->variable( $classIdentifier . '_MollomSettings', 'honeypot' );	
		}
		else
		{
			$params['rateLimit'] = $ini->variable( 'MollomSettings', 'honeypot' );
		}
				
		return $params;
    }
    
 
    function getExtractableClassList()
    {
        $ini = eZINI::instance( 'mollom.ini' );
        if ( !$ini->hasVariable( 'InformationExtractorSettings', 'ExtractableClasses' ) )
        	return false;
        $extractableClasses = $ini->variable( 'InformationExtractorSettings', 'ExtractableClasses' );
        return $extractableClasses;
    }

    function getExtractableNodes( $limit = false, $offset = false )
    {
        $classList = self::getExtractableClassList();
        $params = array(
            'ClassFilterType' => 'include',
            'ClassFilterArray' => $classList,
            'SortBy' => array( array( 'published', false ) ),
            'Limit' => $limit,
            'Offset' => $offset
        );
        $nodes = eZContentObjectTreeNode::subTreeByNodeID( $params, 1 );
        return $nodes;
    }

    function getExtractableNodesCount()
    {
        $classList = eZContentObjectMollom::getExtractableClassList();
        $params = array(
            'ClassFilterType' => 'include',
            'ClassFilterArray' => $classList
        );
		$node = eZContentObjectTreeNode::fetchNode( 1, 1);
        $nodeCount = $node->subTreeCount( $params, 1 );
        return $nodeCount;
    }
}

?>