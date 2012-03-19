<?php

function lanorg_get_event_users($event_id) {
	global $wpdb;

	$event_id = (int) $event_id;
	$table_name = $wpdb->prefix . 'lanorg_events_users';

	return $wpdb->get_col("SELECT user_id FROM $table_name WHERE event_id = $event_id", 0);
}

function lanorg_get_user_events($user_id) {
	global $wpdb;

	$user_id = (int) $user_id;
	$table_name = $wpdb->prefix . 'lanorg_events_users';

	return $wpdb->get_col("SELECT event_id FROM $table_name WHERE user_id = $user_id", 0);
}

function lanorg_join_event($event_id, $user_id) {
	global $wpdb;

	$table_name = $wpdb->prefix . 'lanorg_events_users';

	$data = array(
		'event_id' => $event_id,
		'user_id' => $user_id,
	);
	return $wpdb->insert($table_name,	$data, array('%d', '%d'));
}

function lanorg_leave_event($event_id, $user_id) {
	global $wpdb;

	$table_name = $wpdb->prefix . 'lanorg_events_users';
	$event_id = (int) $event_id;
	$user_id = (int) $user_id;

	$wpdb->query("DELETE FROM $table_name WHERE event_id = $event_id AND user_id = $user_id LIMIT 1");
}

?>