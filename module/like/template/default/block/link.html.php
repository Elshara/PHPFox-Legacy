<?php 
/**
 * [PHPFOX_HEADER]
 * 
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		Raymond_Benc
 * @package 		Phpfox
 * @version 		$Id: link.html.php 6671 2013-09-25 10:06:46Z Fern $
 */
 
defined('PHPFOX') or exit('NO DICE!'); 

?>	
<script type="text/javascript">
	if (typeof $Core.Like == 'undefined') $Core.Like = {l}{r};
	$Core.Like.Actions = 
	{l}
		{if Phpfox::getParam('like.allow_dislike') && isset($aActions)}
			{literal}
				doAction: function(sActionTypeId, sItemTypeId, iItemId, sModule, sIteration, iParentId, oObj)
				{
					$(oObj).parent().parent().parent().after('<div id="js_gen_display_action_' + sItemTypeId + '_' + iItemId + '"></div>');
					window.oTempO = oObj;
					
					/* Figure out if this item has been liked */
					if ( $(oObj).closest('.comment_mini_link_like').find('.js_like_link_unlike').is(':visible'))					
					{
						$(oObj).closest('.comment_mini_link_like').find('.js_like_link_toggle').toggle();
					}
					
					
					$.ajaxCall('like.doAction', 'action_type_id=' + sActionTypeId + '&item_type_id=' + sItemTypeId + '&item_id=' + iItemId + '&module_name=' + sModule + '&parent_id=' + iParentId);
					/* $('.like_action_' + sActionTypeId + '_' + sIteration).each(function(){$(this).toggle();});			*/
					$(oObj).parent().find('a.like_action').toggle();
					
				},
				removeAction: function(sActionTypeId, sItemTypeId, iItemId, sModule, sIteration, iParentId, oObj)
				{
					$.ajaxCall('like.removeAction', 'action_type_id=' + sActionTypeId + '&item_type_id=' + sItemTypeId + '&item_id=' + iItemId + '&module_name=' + sModule + '&parent_id=' + iParentId);
					if ( $(oObj).closest('.comment_mini_link_like').find('.js_like_link_unlike').is(':visible'))					
					{
						$(oObj).closest('.comment_mini_link_like').find('.js_like_link_toggle').toggle();
					}
					$(oObj).parent().find('a.like_action').toggle();
				},
			{/literal}
		{/if}
	{literal}
		doLike : function(bIsCustom, sItemTypeId, iItemId, iParentId, oObj)
		{			
			
			if ($(oObj).closest('.comment_mini_link_like').find('.like_action_unmarked').is(':visible'))
			{
				$('#dislike_remove_' + iItemId).hide();
				$(oObj).closest('.comment_mini_link_like').find('.like_action_marked').show();
				$(oObj).closest('.comment_mini_link_like').find('.like_action_unmarked').hide();
			}
			$(oObj).parent().find('.js_like_link_unlike:first').show(); 
			$(oObj).hide();
			$.ajaxCall('like.add', 'type_id=' + sItemTypeId + '&item_id=' + iItemId + '&parent_id=' +iParentId + '&custom_inline=' + bIsCustom, 'GET');
		}
	};
	{/literal}
</script>

<li class="li_action">
	<a href="#" onclick="$Core.Like.Actions.doLike({if $aLike.like_is_custom}1{else}0{/if}, '{$aLike.like_type_id}', {$aLike.like_item_id}, {if isset($aFeed.feed_id)}{$aFeed.feed_id}{else}0{/if}, this); return false;" class="js_like_link_toggle js_like_link_like"{if $aLike.like_is_liked} style="display:none;"{/if}>
		{phrase var='feed.like'}
	</a>
	<a href="#" onclick="$(this).parents('div:first').find('.js_like_link_like:first').show(); $(this).hide(); $.ajaxCall('like.delete', 'type_id={$aLike.like_type_id}&amp;item_id={$aLike.like_item_id}&amp;parent_id={if isset($aFeed.feed_id)}{$aFeed.feed_id}{else}{/if}{if $aLike.like_is_custom}&amp;custom_inline=1{/if}', 'GET'); return false;" class="js_like_link_toggle js_like_link_unlike"{if $aLike.like_is_liked}{else} style="display:none;"{/if}>{phrase var='feed.unlike'}</a>	
</li>
{if Phpfox::getParam('like.allow_dislike') && isset($aActions) && is_array($aActions) && !empty($aActions)}
	<li><span>&middot;</span></li>
	<li class="li_action">
		{foreach from=$aActions name=action item=aAction}
			{if isset($aAction.action_type_id)}
				<a href="#" onclick="$Core.Like.Actions.doAction('{$aAction.action_type_id}', '{if isset($aLike.like_type_id)}{$aLike.like_type_id}{else}{$aAction.item_type_id}{/if}', {$aAction.item_id}, '{if $aLike.like_type_id == 'feed_mini'}comment{else}{$aAction.module_name}{/if}', {$aAction.iActionIteration},  {$aFeed.feed_id}, this); return false;" class="like_action_{$aAction.action_type_id}_{$aAction.iActionIteration} like_action like_action_marked" {if $aAction.is_marked}style="display:none;"{/if}>
					{$aAction.phrase}
				</a>
				<a href="#" id="dislike_remove_{$aAction.item_id}" onclick="$Core.Like.Actions.removeAction('{$aAction.action_type_id}', '{if isset($aLike.like_type_id)}{$aLike.like_type_id}{else}{$aAction.item_type_id}{/if}', {$aAction.item_id}, '{if $aLike.like_type_id == 'feed_mini'}comment{else}{$aAction.module_name}{/if}', {$aAction.iActionIteration},  {$aFeed.feed_id}, this); return false;" class="like_action_{$aAction.action_type_id}_{$aAction.iActionIteration} like_action like_action_unmarked" {if !$aAction.is_marked}style="display:none;"{/if}>
					{phrase var='like.remove'} {$aAction.phrase}
				</a>
			{/if}
		{/foreach}
	</li>
{else}

{/if}
