<?php
add_filter('user_contactmethods', 'my_user_contactmethods');  

function my_user_contactmethods($user_contactmethods){
	
	unset($user_contactmethods['yim']);
	unset($user_contactmethods['aim']);
	unset($user_contactmethods['jabber']);

	$user_contactmethods['twitter'] = __('Twitter Username', 'lanorg');
	$user_contactmethods['facebook'] = __('Facebook Username', 'lanorg');

	return $user_contactmethods;
}
?>