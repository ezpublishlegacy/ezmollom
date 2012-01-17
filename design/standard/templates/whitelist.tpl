{*

whitelist.tpl template
Copyright (C) 2011 Fumaggo Internet Solutions. All rights reserved

*}

<form name="whitelistform" action={'ezmollom/whitelist'|ezurl()} method="post" >

<div class="context-block">
{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">
<h1 class="context-title">{'Whitelist'|i18n( 'fumaggo/design/ezmollom/whitelist' )}</h1>

{* DESIGN: Mainline *}<div class="header-mainline"></div>

{* DESIGN: Header END *}</div></div></div></div></div></div>

{* DESIGN: Content START *}<div class="box-ml"><div class="box-mr"><div class="box-content">

<div class="content-view-full">
        	<div class="ezmollom whitelist">
        	
        	<table class="list" cellspacing="0">

				<tr>
  					<th class="tight"><img src={'toggle-button-16x16.gif'|ezimage} alt="{'Toggle selection'|i18n( 'fumaggo/design/ezmollom')}" onclick="ezjs_toggleCheckboxes( document.whitelistform, 'DeleteIDArray[]' ); return false;"/></th>
  					<th>{'Value'|i18n( 'fumaggo/design/ezmollom/whitelist' )}</th>
  					<th>{'Created'|i18n( 'fumaggo/design/ezmollom/whitelist' )}</th>
  					<th>{'Last Match'|i18n( 'fumaggo/design/ezmollom/whitelist' )}</th>
  					<th>{'Match Count'|i18n( 'fumaggo/design/ezmollom/whitelist' )}</th>
  					<th>{'Context'|i18n( 'fumaggo/design/ezmollom/whitelist' )}</th>
  					<th>{'Status'|i18n( 'fumaggo/design/ezmollom/whitelist' )}</th>
  					<th class="tight">&nbsp;</th>
				</tr>
				
				{foreach $white_list as $entry sequence array( bglight, bgdark ) as $sequence}
					<tr class="{$sequence}">
						<td class="tight"><input type="checkbox" name="DeleteIDArray[]" value="{$entry.id}" title="{'Select whitelist entry for removal.'|i18n( 'fumaggo/design/ezmollom/whitelist' )}" /></td>
						<td>{$entry.value}</td>
						<td>{$entry.created|l10n(shortdatetime)}</td>
						<td>{$entry.lastmatch}</td>
						<td>{$entry.matchcount}</td>
						<td>{$entry.context}</td>
						<td>{$entry.status}</td>
						<td><a href={concat( 'ezmollom/editwhitelist/', $entry.id )|ezurl}><img class="button" src={'edit.gif'|ezimage} width="16" height="16" alt="{'Edit'|i18n( 'fumaggo/design/ezmollom/whitelist' )}" title="{'Edit whitelist entry.'|i18n( 'fumaggo/design/ezmollom/whitelist' )}" /></a></td>
					</tr>
				{/foreach}
			</table>
								
        	<div class="context-toolbar">
		{include name=navigator
         uri='design:navigator/google.tpl'
         page_uri='/role/list'
         item_count=$whitelist_count
         view_parameters=$view_parameters
         item_limit=$limit}
</div>

{* DESIGN: Content END *}</div></div></div>
<div class="controlbar">
{* DESIGN: Control bar START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">
<div class="block">
    <input class="button" type="submit" name="RemoveButton" value="{'Remove selected'|i18n( 'fumaggo/design/ezmollom/whitelist' )}" title="{'Remove selected entries.'|i18n( 'fumaggo/design/ezmollom/whitelist' )}" />
    <input class="button" type="submit" name="NewButton" value="{'New whitelist entry'|i18n( 'fumaggo/design/ezmollom/whitelist' )}" title="{'Create a new whitelist entry.'|i18n( 'fumaggo/design/ezmollom/whitelist' )}" />
</div>
{* DESIGN: Control bar END *}</div></div></div></div></div></div>
</div>

</div>

</form>
