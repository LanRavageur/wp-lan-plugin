<?php
add_filter('user_contactmethods', 'my_user_contactmethods');  

function my_user_contactmethods($user_contactmethods){
	
	// Remove those useless contact methods
	unset($user_contactmethods['yim']);
	unset($user_contactmethods['aim']);
	unset($user_contactmethods['jabber']);

	// Adds some new cool contact methods :)
	$user_contactmethods['steam_id'] = __('Steam Account', 'lanorg') . ' <span class="description">' . __('(to let new friends meet you ingame)', 'lanorg') . '</span>';
	$user_contactmethods['origin_id'] = __('Origin Account', 'lanorg') . ' <span class="description">' . __('(same here)', 'lanorg') . '</span>';
	$user_contactmethods['battlefield3_id'] = __('Battlefield 3 Soldier', 'lanorg') . ' <span class="description">' . __('(same here)', 'lanorg') . '</span>';
	$user_contactmethods['battlenet_id'] = __('Battle.net Account', 'lanorg') . ' <span class="description">' . __('(same here)', 'lanorg') . '</span>';
	
	$user_contactmethods['facebook_id'] = __('Facebook Username', 'lanorg');
	$user_contactmethods['twitter_id'] = __('Twitter Username', 'lanorg') . ' <span class="description">' . __('(without @)', 'lanorg') . '</span>';
	$user_contactmethods['youtube_id'] = __('Youtube Account', 'lanorg');
	
	return $user_contactmethods;
}
?>