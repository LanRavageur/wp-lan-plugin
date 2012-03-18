add_filter('user_contactmethods', 'my_user_contactmethods');  

<?php
function my_user_contactmethods($user_contactmethods){

  unset($user_contactmethods['yim']);
  unset($user_contactmethods['aim']);
  unset($user_contactmethods['jabber']);

  $user_contactmethods['twitter'] = _e('Twitter Username');
  $user_contactmethods['facebook'] = _e('Facebook Username');

  return $user_contactmethods;
}
?>