{*

status.tpl template
Copyright (C) 2011 Fumaggo Internet Solutions. All rights reserved

*}
<div id="maincontent"><div id="fix">
<div id="maincontent-design">
<div class="border-box">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc float-break">

	<div class="content-view-full">
        	<div class="ezmollom">

				<div class="attribute-header">
					<h1>{'Spam filter report'|i18n( 'fumaggo/design/ezmollom/status' )}</h1>
				</div>
		
				<div class="break"></div>
				
				{switch match=$status}
				
					{case match='spam'}
						<p>{'Your submission has triggered the spam filter and will therefore not be accepted.'|i18n( 'fumaggo/design/ezmollom/status' )}.</p> 
					{/case}
				
					{case match='unsure'}
						{* not yet supported in this version of ezmollom *}
					{/case}
				
					{case match='profanity'}
						{'Your submission has triggered the profanity filter and will not be accepted until the inappropriate language is removed.'|i18n( 'fumaggo/design/ezmollom/status' )}
					{/case}
				
					{case match='quality'}
						{'Your submission has triggered the quality filter and will not be accepted until the content has been improved.'|i18n( 'fumaggo/design/ezmollom/status' )}
					{/case}
				
					{case match='sentiment'}
						{'Your submission has triggered the quality filter and will not be accepted until the content has been improved.'|i18n( 'fumaggo/design/ezmollom/status' )}
					{/case}
				
					{case match='error'}
						{'The spam filter installed on this site is currently unavailable. Per site policy, we are unable to accept new submissions until that problem is resolved. Please try resubmitting the form in a couple of minutes.'|i18n( 'fumaggo/design/ezmollom/status' )}
					{/case}
				
					{case}
					{/case}
				
				{/switch}
				
				<div class="break"></div>
				
				<p>{'Details'|i18n( 'fumaggo/design/ezmollom/status' )}</p>
				<ul>
					<li>{'Title:'|i18n( 'fumaggo/design/ezmollom/status' )} {$object.name|wash()}
					<li>{'ID:'|i18n( 'fumaggo/design/ezmollom/status' )} {$report.id}</li>
					<li>{'Reason:'|i18n( 'fumaggo/design/ezmollom/status' )} {$report.reason}</li>
					<li>{'Submitted:'|i18n( 'fumaggo/design/ezmollom/status' )} {$object.current.created|l10n( shortdatetime )}</li>
				</ul>
											
			</div>
    </div>

</div></div></div>
<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
</div>
</div></div>
