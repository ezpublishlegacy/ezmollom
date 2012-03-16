{*

status.tpl template
Copyright (C) 2011 Fumaggo Internet Solutions. All rights reserved

*}

{if not(is_set($collection))}
<div id="maincontent">
<div class="float-break" id="maincontent-design"><div id="fix">

<div class="attribute-header">
	<h3>{'Spam filter report'|i18n( 'fumaggo/design/ezmollom/status' )}</h3>
</div>

<div class="break"></div>
{/if}

{switch match=$status}

	{case match='spam'}
		<p>{'Your submission has triggered the spam filter and will therefore not be accepted. If your post has been incorrectly marked as spam, please contact the webmaster with the details below.'|i18n( 'fumaggo/design/ezmollom/status' )}</p> 
	{/case}

	{case match='unsure'}
		
		<p>{'Please enter the following captcha to ensure your submission is not spam.'|i18n( 'fumaggo/design/ezmollom/status' )}</p> 
		
		{if $retry}
			<p>{'Your answer is incorrect, please try again.'|i18n( 'fumaggo/design/ezmollom/status' )}</p> </p>
		{/if}
		
		{if $object}
			 <form method="post" action={concat('content/edit/',$object.id,"/",$object.current_version,"/",$object.current_language)|ezurl} name="CheckCaptcha">
				 <img src="{$captcha.url}" align ="left" alt="{'Type the characters you see in this picture.'|i18n( 'fumaggo/design/ezmollom/status' )}"/>
				 <input type="text" name="captcha" value="">
				 <input type="hidden" name="id" value="{$captcha.id}">
				 <input type="hidden" name="HasObjectInput" value="0" />
				 <input type="submit" title="Check Captcha" value="Check Captcha" name="PublishButton" class="defaultbutton">
			 </form>
		{/if}
	{/case}

	{case match='profanity'}
		{'Your submission has triggered the profanity filter and will not be accepted until the inappropriate language is removed.'|i18n( 'fumaggo/design/ezmollom/status' )}
	{/case}

	{case match='quality'}
		{'Your submission has triggered the quality filter and will not be accepted until the content has been improved.'|i18n( 'fumaggo/design/ezmollom/status' )}
	{/case}

	{case match='sentiment'}
		{'Your submission has triggered the sentiment filter and will not be accepted until the content has been improved.'|i18n( 'fumaggo/design/ezmollom/status' )}
	{/case}

	{case}
		{'The spam filter installed on this site is currently unavailable. Per site policy, we are unable to accept new submissions until that problem is resolved. Please try resubmitting the form in a couple of minutes.'|i18n( 'fumaggo/design/ezmollom/status' )}
	{/case}

{/switch}

{if or( eq( $status, 'spam' ), eq( $status, 'profanity' ), eq( $status, 'quality' ), eq( $status, 'sentiment' ) )}

<div class="break"></div>
<p><strong>{'Details'|i18n( 'fumaggo/design/ezmollom/status' )}</strong></p>

	{if $comment}
		<ul>
			<li>{'ID:'|i18n( 'fumaggo/design/ezmollom/status' )} {$result.session_id}</li>
			<li>{'Title:'|i18n( 'fumaggo/design/ezmollom/status' )} {$name|wash()}
			{if $result.reason}
				<li>{'Reason:'|i18n( 'fumaggo/design/ezmollom/status' )} {$report.reason}</li>
			{/if}
			<li>{'Submitted:'|i18n( 'fumaggo/design/ezmollom/status' )} {$comment.created|l10n( shortdatetime )}</li>
		</ul>
	{/if}
	
	{if $collection}
		<ul>
			<li>{'ID:'|i18n( 'fumaggo/design/ezmollom/status' )} {$result.session_id}</li>
			<li>{'Title:'|i18n( 'fumaggo/design/ezmollom/status' )} {$name|wash()}
			{if $result.reason}
				<li>{'Reason:'|i18n( 'fumaggo/design/ezmollom/status' )} {$report.reason}</li>
			{/if}
			<li>{'Submitted:'|i18n( 'fumaggo/design/ezmollom/status' )} {$collection.created|l10n( shortdatetime )}</li>
		</ul>
	{/if}
	
	{if $object}
		<ul>
			<li>{'ID:'|i18n( 'fumaggo/design/ezmollom/status' )} {$result.session_id}</li>
			<li>{'Title:'|i18n( 'fumaggo/design/ezmollom/status' )} {$name|wash()}
			{if $result.reason}
				<li>{'Reason:'|i18n( 'fumaggo/design/ezmollom/status' )} {$report.reason|wash()}</li>
			{/if}
			<li>{'Submitted:'|i18n( 'fumaggo/design/ezmollom/status' )} {$object.current.created|l10n( shortdatetime )}</li>
		</ul>
	{/if}
{/if}

{if not(is_set($collection))}
</div></div>
</div>		
{/if}								
			