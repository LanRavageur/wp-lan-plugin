<?php

$lanorg_profile_edit_form = NULL;

function lanorg_init_profile_form() {
	global $lanorg_profile_edit_form;

	$lanorg_profile_edit_form = array(
		array(
			'type' => 'text',
			'key' => 'steam_id',
			'label' => __('Steam ID :', 'lanorg'),
		),
		array(
			'type' => 'text',
			'key' => 'origin_id',
			'label' => __('Compte EA/Origin :', 'lanorg'),
		),
		array(
			'type' => 'text',
			'key' => 'battlefield3_id',
			'label' => __('Soldat Battlefield 3 :', 'lanorg'),
		),
		array(
			'type' => 'text',
			'key' => 'facebook_id',
			'label' => __('Compte Facebook :', 'lanorg'),
		),
		array(
			'type' => 'text',
			'key' => 'twitter_id',
			'label' => __('Compte Twitter :', 'lanorg'),
		),
		array(
			'type' => 'text',
			'key' => 'youtube_id',
			'label' => __('Compte YouTube :', 'lanorg'),
		),
	);
}

// Show the user profile page
// If the user_id is NULL, a page to edit the current user profile is shown.
function lanorg_profile_page($user_id=0) {
	global $lanOrg, $lanorg_profile_edit_form;

	lanorg_init_profile_form();

	if ($user_id) {		// View user profile

		$GLOBALS['user_info'] = get_userdata($user_id);

		$meta_data = array();
		foreach ($lanorg_profile_edit_form as $field) {
			$key = $field['key'];

			$meta_data[$field['label']] = get_user_meta($user_id, $key, TRUE);
		}

		$GLOBALS['user_meta_data'] = $meta_data;

		$lanOrg->render_two_column_page('lanorg-profile.php');
	}
	else {		// Edit user profile

		$values = array();
		if (lanorg_form_post($lanorg_profile_edit_form, $values, $lanOrg->form_prefix)) {

			$errors = array();

			if (lanorg_form_validation($lanorg_profile_edit_form, $values, $errors)) {
				foreach ($values as $key => $value) {
					delete_user_meta(get_current_user_id(), $key);
					add_user_meta(get_current_user_id(), $key, $value, TRUE);
				}
			}
		}
		$lanOrg->render_two_column_page('lanorg-edit-profile.php');
	}
}

// Return url to the profile page of a user
function lanorg_get_user_profile_url($user_id=0) {
	// TODO: implement user_id
	global $wp_rewrite;

	if ($wp_rewrite->using_permalinks()) {
		$url = $wp_rewrite->root . 'profile/';
		if ($user_id != 0) {
			$url .= $user_id . '/';
		}
		$url = home_url(user_trailingslashit($url));
	}
	else {
		$vars = array('lanorg_page' => 'profile');
		if ($user_id != 0) {
			$vars['user_id'] = $user_id;
		}
		
		$url = add_query_arg($vars, home_url( '/'));
	}
	return htmlentities($url, NULL, 'UTF-8');
}

function lanorg_get_profile_edit_form() {
	$markup = '';
	global $lanorg_profile_edit_form;

	$current_user_id = get_current_user_id();
	$values = array();
	foreach ($lanorg_profile_edit_form as $field) {
		$key = $field['key'];

		$values[$key] = get_user_meta($current_user_id, $key, TRUE);
	}

	if ($lanorg_profile_edit_form !== NULL) {
		$markup = lanorg_form($lanorg_profile_edit_form, $values);
	}

	return $markup;
}

?>