<?php
add_filter('user_contactmethods', 'my_user_contactmethods');  

function my_user_contactmethods($user_contactmethods){
	
	unset($user_contactmethods['yim']);
	unset($user_contactmethods['aim']);
	unset($user_contactmethods['jabber']);

	$user_contactmethods['twitter'] = __('Twitter Username');
	$user_contactmethods['facebook'] = __('Facebook Username');

	return $user_contactmethods;
}
?>