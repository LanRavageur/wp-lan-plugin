<?php
$lanorg_registration_form = array(
	array(
		'type' => 'text',
		'key' => 'tournament',
		'label' => 'Tournois :',
		'validator' => 'empty',
	),
);

function lanorg_get_registration_form() {
	$fields = array();

	$events = lanorg_get_events();

	foreach ($events as $event_id => $event_title) {
		$field = array(
			'type' => 'checkbox',
			'key' => 'event-' . $event_id,
			'text' => $event_title,
		);
		array_push($fields, $field);
	}
	return $fields;
}

// Return url to registration page
function lanorg_get_registration_url() {
	global $wp_rewrite;

	if ($wp_rewrite->using_permalinks()) {
		$url = $wp_rewrite->root . 'registration/';
		$url = home_url(user_trailingslashit($url));
	}
	else {
		$vars = array('lanorg_page' => 'registration');
		$url = add_query_arg($vars, home_url( '/'));
	}
	return htmlentities($url, NULL, 'UTF-8');
}

function lanorg_registration_page() {
	global $lanOrg;

	$current_user_id = get_current_user_id();
	$events = lanorg_get_events();

	$form = lanorg_get_registration_form();

	$values = array();
	if (lanorg_form_post($form, $values, $lanOrg->form_prefix)) {

		$errors = array();

		if (lanorg_form_validation($form, $values, $errors)) {
			foreach ($events as $event_id => $event_title) {
				if ($values['event-' . $event_id] == 1) {
					lanorg_join_event($event_id, $current_user_id);
				}
				else {
					lanorg_leave_event($event_id, $current_user_id);
				}
			}

		}
	}
	$lanOrg->render_two_column_page('lanorg-registration.php');
}

// Get the HTML markup for the registration form
// Called from the template
function lanorg_get_registration_form_markup()
{
	$current_user_id = get_current_user_id();

	$values = lanorg_get_user_events($current_user_id);

	foreach ($values as $key => $value) {
		$values['event-' . $value] = '1';
	}

	$fields = lanorg_get_registration_form();
	return lanorg_form($fields, $values);
}

?>