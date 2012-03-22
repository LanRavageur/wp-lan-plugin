<?php

global $page_title, $user_info, $user_meta_data, $lanOrg;

$lanOrg->require_login();

$user = wp_get_current_user();

if ($user_info) {
	$page_title = 'Profile de ' . $user_info->data->user_login;
}
else {
	$page_title = 'Profile inexistant';
}

?>
<h1 style="clear: none;" class="entry-title">Profile de <?php echo $user_info->data->user_login; ?></h1>
<?php echo get_avatar($user_info->data->ID); ?>
<dl>
<?php foreach ($user_meta_data as $key => $value) { ?>
<dt><?php echo htmlentities($key, NULL, 'UTF-8'); ?></dt>
<dd><?php echo htmlentities($value, NULL, 'UTF-8'); ?></dd>
<?php } ?>
</dl>