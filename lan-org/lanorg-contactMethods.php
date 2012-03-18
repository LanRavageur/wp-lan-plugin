<?php
add_filter('user_contactmethods', 'my_user_contactmethods');  

function my_user_contactmethods($user_contactmethods){
	
	// Remove those useless contact methods
	unset($user_contactmethods['yim']);
	unset($user_contactmethods['aim']);
	unset($user_contactmethods['jabber']);

	// Adds some new cool contact methods :)
	$user_contactmethods['twitter'] = __('Twitter Username', 'lanorg') . ' <span class="description">' . __('(without @)', 'lanorg') . '</span>';
	$user_contactmethods['facebook'] = __('Facebook Username', 'lanorg');
	$user_contactmethods['steam'] = __('Steam Account', 'lanorg') . ' <span class="description">' . __('(to let new friends meet you ingame)', 'lanorg') . '</span>';
	$user_contactmethods['origin'] = __('Origin Account', 'lanorg') . ' <span class="description">' . __('(same here)', 'lanorg') . '</span>';

	return $user_contactmethods;
}
?>