{*

editwhitelist.tpl template
Copyright (C) 2011 Fumaggo Internet Solutions. All rights reserved

*}

<form name="whitelistform" action={'ezmollom/editwhitelist'|ezurl()} method="post" >

<input type="hidden" name="ID" value="{$entry.id}" />

<div class="context-block">

{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

<h1 class="context-title">{'Edit whitelist entry'|i18n( 'fumaggo/design/ezmollom/whitelist' )}</h1>

{* DESIGN: Mainline *}<div class="header-mainline"></div>

{* DESIGN: Header END *}</div></div></div></div></div></div>

{* DESIGN: Content START *}<div class="box-ml"><div class="box-mr"><div class="box-content">

{if $error}
	<div class="message-warning">
		<h2>{'The whitelist entry could not be stored.'|i18n( 'fumaggo/design/ezmollom/whitelist' )}</h2>
		<p>{'The field "value" is a required field.'|i18n( 'fumaggo/design/ezmollom/whitelist' )}</p>
	</div>
{/if}
			
{if $feedback}
	<div class="message-feedback">
		<h2>{'The whitelist entry was successfully stored.'|i18n( 'fumaggo/design/ezmollom/whitelist' )}</h2>
	</div>
{/if}   

<div class="context-attributes">

<div class="block">
	<label>{'Value'|i18n( 'fumaggo/design/ezmollom/whitelist' )}:</label>
	<input class="halfbox" id="Value" type="text" name="Value" value="{$entry.value}" title="{'Value'|i18n( 'fumaggo/design/ezmollom/whitelist' )}" />
</div>

<div class="block">
    <label>{'Context'|i18n( 'fumaggo/design/ezmollom/whitelist' )}:</label>
    <select name="Context" title="{'Context'|i18n( 'fumaggo/design/ezmollom/whitelist' )}">
	    <option {if eq($entry.context,"everything")}selected="selected"{/if} value="allFields">{'Everything'|i18n( 'fumaggo/design/ezmollom/whitelist' )}</option>
	    <option {if eq($entry.context,"allFields")}selected="selected"{/if} value="allFields">{'All Fields'|i18n( 'fumaggo/design/ezmollom/whitelist' )}</option>
	    <option {if eq($entry.context,"authorName")}selected="selected"{/if} value="authorName">{'Author Name'|i18n( 'fumaggo/design/ezmollom/whitelist' )}</option>
	    <option {if eq($entry.context,"authorMail")}selected="selected"{/if} value="authorMail">{'Author Mail'|i18n( 'fumaggo/design/ezmollom/whitelist' )}</option>
	    <option {if eq($entry.context,"authorIp")}selected="selected"{/if} value="authorIp">{'Author IP'|i18n( 'fumaggo/design/ezmollom/whitelist' )}</option>
	    <option {if eq($entry.context,"authorId")}selected="selected"{/if} value="authorId">{'Author ID'|i18n( 'fumaggo/design/ezmollom/whitelist' )}</option>
	    <option {if eq($entry.context,"links")}selected="selected"{/if} value="links">{'Links'|i18n( 'fumaggo/design/ezmollom/whitelist' )}</option>
	    <option {if eq($entry.context,"postTitle")}selected="selected"{/if} value="postTitle">{'Post Title'|i18n( 'fumaggo/design/ezmollom/whitelist' )}</option>   
    </select>
 </div>

</div>

<div class="block">
	<label>{'Status'|i18n( 'fumaggo/design/ezmollom/whitelist' )}:</label>
	<select name="Status" title="{'Status'|i18n( 'fumaggo/design/ezmollom/whitelist' )}">
	   <option {if eq($entry.status,0)}selected="selected"{/if} value="0">{'Disabled'|i18n( 'fumaggo/design/ezmollom/whitelist' )}</option>
	   <option {if eq($entry.status,1)}selected="selected"{/if} value="1">{'Enabled'|i18n( 'fumaggo/design/ezmollom/whitelist' )}</option>
    </select>
</div>

<div class="block">
	<label>{"Note"|i18n( 'fumaggo/design/ezmollom/whitelist' )}:</label>
	<input class="halfbox" id="Note" type="text" name="Note" value="{$entry.note}" title="{"Note"|i18n( 'fumaggo/design/ezmollom/whitelist' )}" />
</div>

{* DESIGN: Content END *}</div></div></div>


{* Buttons. *}
<div class="controlbar">
{* DESIGN: Control bar START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">
<div class="block">
<input class="button" type="submit" name="StoreButton" value="{'OK'|i18n( 'fumaggo/design/ezmollom/whitelist' )}" />
<input class="button" type="submit" name="DiscardButton" value="{'Cancel'|i18n( 'fumaggo/design/ezmollom/whitelist' )}" />
</div>
{* DESIGN: Control bar END *}</div></div></div></div></div></div>
</div>

</div>


</form>