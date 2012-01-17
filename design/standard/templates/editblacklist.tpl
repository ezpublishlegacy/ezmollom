{*

editblacklist.tpl template
Copyright (C) 2011 Fumaggo Internet Solutions. All rights reserved

*}

<form name="blacklistform" action={'ezmollom/editblacklist'|ezurl()} method="post" >

<input type="hidden" name="ID" value="{$entry.id}" />

<div class="context-block">

{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

<h1 class="context-title">{'Edit blacklist entry'|i18n( 'fumaggo/design/ezmollom/blacklist' )}</h1>

{* DESIGN: Mainline *}<div class="header-mainline"></div>

{* DESIGN: Header END *}</div></div></div></div></div></div>

{* DESIGN: Content START *}<div class="box-ml"><div class="box-mr"><div class="box-content">

{if $error}
	<div class="message-warning">
		<h2>{'The blacklist entry could not be stored.'|i18n( 'fumaggo/design/ezmollom/blacklist' )}</h2>
		<p>{'The field "value" is a required field.'|i18n( 'fumaggo/design/ezmollom/blacklist' )}</p>
	</div>
{/if}
			
{if $feedback}
	<div class="message-feedback">
		<h2>{'The blacklist entry was successfully stored.'|i18n( 'fumaggo/design/ezmollom/blacklist' )}</h2>
	</div>
{/if}   

<div class="context-attributes">

<div class="block">
	<label>{'Value'|i18n( 'fumaggo/design/ezmollom/blacklist' )}:</label>
	<input class="halfbox" id="Value" type="text" name="Value" value="{$entry.value}" title="{'Value'|i18n( 'fumaggo/design/ezmollom/blacklist' )}" />
</div>

<div class="block">
    <label>{'Context'|i18n( 'fumaggo/design/ezmollom/blacklist' )}:</label>
    <select name="Context" title="{'Context'|i18n( 'fumaggo/design/ezmollom/blacklist' )}">
	    <option {if eq($entry.context,"everything")}selected="selected"{/if} value="allFields">{'Everything'|i18n( 'fumaggo/design/ezmollom/blacklist' )}</option>
	    <option {if eq($entry.context,"allFields")}selected="selected"{/if} value="allFields">{'All Fields'|i18n( 'fumaggo/design/ezmollom/blacklist' )}</option>
	    <option {if eq($entry.context,"authorName")}selected="selected"{/if} value="authorName">{'Author Name'|i18n( 'fumaggo/design/ezmollom/blacklist' )}</option>
	    <option {if eq($entry.context,"authorMail")}selected="selected"{/if} value="authorMail">{'Author Mail'|i18n( 'fumaggo/design/ezmollom/blacklist' )}</option>
	    <option {if eq($entry.context,"authorIp")}selected="selected"{/if} value="authorIp">{'Author IP'|i18n( 'fumaggo/design/ezmollom/blacklist' )}</option>
	    <option {if eq($entry.context,"authorId")}selected="selected"{/if} value="authorId">{'Author ID'|i18n( 'fumaggo/design/ezmollom/blacklist' )}</option>
	    <option {if eq($entry.context,"links")}selected="selected"{/if} value="links">{'Links'|i18n( 'fumaggo/design/ezmollom/blacklist' )}</option>
	    <option {if eq($entry.context,"postTitle")}selected="selected"{/if} value="postTitle">{'Post Title'|i18n( 'fumaggo/design/ezmollom/blacklist' )}</option>   
    </select>
 </div>

</div>

<div class="block">
	<label>{'Match'|i18n( 'fumaggo/design/ezmollom/blacklist' )}:</label>
	<select name="Match" title="{'Match'|i18n( 'fumaggo/design/ezmollom/blacklist' )}">
		<option></option>
	    <option {if eq($entry.match,"exact")}selected="selected"{/if} value="exact">{'Exact'|i18n( 'fumaggo/design/ezmollom/blacklist' )}</option>
	    <option {if eq($entry.match,"contains")}selected="selected"{/if} value="contains">{'Contains'|i18n( 'fumaggo/design/ezmollom/blacklist' )}</option>
    </select>
</div>

<div class="block">
	<label>{'Reason'|i18n( 'fumaggo/design/ezmollom/blacklist' )}:</label>
	<select name="Reason" title="{'Reason'|i18n( 'fumaggo/design/ezmollom/blacklist' )}">
	    <option {if eq($entry.reason,"unwanted")}selected="selected"{/if} value="unwanted">{'Unwanted'|i18n( 'fumaggo/design/ezmollom/blacklist' )}</option>
	    <option {if eq($entry.reason,"spam")}selected="selected"{/if} value="spam">{'Spam'|i18n( 'fumaggo/design/ezmollom/blacklist' )}</option>
	    <option {if eq($entry.reason,"profanity")}selected="selected"{/if} value="profanity">{'Profanity'|i18n( 'fumaggo/design/ezmollom/blacklist' )}</option>
	    <option {if eq($entry.reason,"quality")}selected="selected"{/if} value="quality">{'Poor quality'|i18n( 'fumaggo/design/ezmollom/blacklist' )}</option>
    </select>
</div>

<div class="block">
	<label>{'Status'|i18n( 'fumaggo/design/ezmollom/blacklist' )}:</label>
	<select name="Status" title="{'Status'|i18n( 'fumaggo/design/ezmollom/blacklist' )}">
	   <option {if eq($entry.status,0)}selected="selected"{/if} value="0">{'Disabled'|i18n( 'fumaggo/design/ezmollom/blacklist' )}</option>
	   <option {if eq($entry.status,1)}selected="selected"{/if} value="1">{'Enabled'|i18n( 'fumaggo/design/ezmollom/blacklist' )}</option>
    </select>
</div>

<div class="block">
	<label>{"Note"|i18n( 'fumaggo/design/ezmollom/blacklist' )}:</label>
	<input class="halfbox" id="Note" type="text" name="Note" value="{$entry.note}" title="{"Note"|i18n( 'fumaggo/design/ezmollom/blacklist' )}" />
</div>

{* DESIGN: Content END *}</div></div></div>


{* Buttons. *}
<div class="controlbar">
{* DESIGN: Control bar START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">
<div class="block">
<input class="button" type="submit" name="StoreButton" value="{'OK'|i18n( 'fumaggo/design/ezmollom/blacklist' )}" />
<input class="button" type="submit" name="DiscardButton" value="{'Cancel'|i18n( 'fumaggo/design/ezmollom/blacklist' )}" />
</div>
{* DESIGN: Control bar END *}</div></div></div></div></div></div>
</div>

</div>


</form>