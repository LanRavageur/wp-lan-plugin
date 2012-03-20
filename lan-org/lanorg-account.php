<?php

$lanorg_signup_form = NULL;

function lanorg_init_form() {
	global $lanorg_signup_form;

	$lanorg_signup_form = array(
		array(
			'type' => 'text',
			'key' => 'nickname',
			'label' => __('Choose a nickname', 'lanorg'),
			'validator' => array('empty', 'username_exists', 'username_valid'),
		),
		array(
			'type' => 'text',
			'key' => 'firstname',
			'label' => __('First name', 'lanorg'),
			'validator' => 'empty',
		),
		array(
			'type' => 'text',
			'key' => 'lastname',
			'label' => __('Name', 'lanorg'),
			'validator' => 'empty',
		),
		array(
			'type' => 'text',
			'key' => 'email',
			'label' => __('Email', 'lanorg'),
			'validator' => array('empty', 'email_exists', 'email_valid'),
		),
		array(
			'type' => 'text',
			'key' => 'password',
			'label' => __('Password', 'lanorg'),
			'password' => true,
			'validator' => 'empty',
		),
	);
}

add_action('init', 'lanorg_init_form');

$lanorg_login_form = array(
	array(
		'type' => 'text',
		'key' => 'username-or-email',
		'label' => __('Email', 'lanorg'),
		'validator' => array('empty'),
	),
	array(
		'type' => 'text',
		'key' => 'password',
		'label' => __('Password', 'lanorg'),
		'password' => true,
		'validator' => 'empty',
	)
);

// Return an associative array that contains user id as key and user name as value.
function lanorg_get_user_list() {
	$user_list = array();

	$users = get_users();
	foreach ($users as $user) {
		$user_list[$user->ID] = $user->user_login;
	}
	return $user_list;
}

// Get the HTML markup for the registration form
// Called from the template
function lanorg_get_signup_form_markup()
{
	$markup = '';
	global $lanorg_signup_form;

	if ($lanorg_signup_form !== NULL) {
		$markup = lanorg_form($lanorg_signup_form);
	}
	return $markup;

}

// Get the HTML markup for the login form
// Called from the template
function lanorg_get_login_form_markup(&$error = NULL)
{
	global $lanorg_login_form;

	$values = array();
	$errors = array();

	$html_markup = lanorg_form($lanorg_login_form, $values, $errors);

	$error = NULL;

	if (count($errors) == 0) {
		lanorg_login_user($error);
	}
	
	return $html_markup;
}

// Shortcode handler for the registration form
function lanorg_shortcode_registration_form($attr) {
		global $lanOrg;

		wp_enqueue_style('lanorg-form');

		// Turn on output buffering to capture form markup
		ob_start();

		$lanOrg->render_template('lanorg-register.php');

		// Get buffered content
		$content = ob_get_clean();
		return $content;
}

function lanorg_login_user(&$error_message = '')
{
	global $lanorg_login_form, $lanOrg;

	$success = FALSE;
	$values = array();
	if (lanorg_form_post($lanorg_login_form, $values, $lanOrg->form_prefix)) {
		$errors = array();
		if (lanorg_form_validation($lanorg_login_form, $values, $errors)) {
			//authentification by email if fail by username
			$user = get_user_by( 'email', $values['username-or-email'] );
			if (isset($user, $user->user_login)){
				$username = $user->user_login;
			}
			else {
				$username = $values['username-or-email'];
			}
			$user_info = array(
				'user_login' => $username,
				'user_password' => $values['password'],
			);
			$user = wp_signon($user_info);
			if(!is_wp_error($user)){
				wp_redirect(home_url());
				$success = TRUE;				
			}
			else{
				$error_message = __('Username or password incorrect', 'lanorg');
			}
		}
	}
	return $success;
}

function lanorg_process_registration_form()
{
	global $lanorg_signup_form, $lanOrg;

	$values = array();

	if ($lanorg_signup_form !== NULL) {
		if (lanorg_form_post($lanorg_signup_form, $values, $lanOrg->form_prefix)) {
			$errors = array();
			if (lanorg_form_validation($lanorg_signup_form, $values, $errors)) {
				wp_insert_user(array(
					'user_login' => $values['nickname'],
					'first_name' => $values['firstname'],
					'last_name' => $values['lastname'],
					'user_email' => $values['email'],
					'user_pass' => $values['password'],
				));

				wp_redirect(home_url());
				exit;
			}
		}
	}
}

?>