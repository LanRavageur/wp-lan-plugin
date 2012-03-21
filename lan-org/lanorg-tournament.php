<?php

function lanorg_tournament_page() {
	global $lanOrg;

	$lan_events = lanorg_get_all_events();

	foreach ($lan_events as $event) {
		$event->tournaments = lanorg_get_tournaments($event->id);
	}

	$GLOBALS['lan_events'] = $lan_events;

	$lanOrg->render_two_column_page('lanorg-tournament.php');
}

// Return url to registration page
function lanorg_get_tournament_url() {
	global $wp_rewrite;

	if ($wp_rewrite->using_permalinks()) {
		$url = $wp_rewrite->root . 'tournament/';
		$url = home_url(user_trailingslashit($url));
	}
	else {
		$vars = array('lanorg_page' => 'tournament');
		$url = add_query_arg($vars, home_url( '/'));
	}
	return htmlentities($url, NULL, 'UTF-8');
}

function lanorg_get_tournaments($event_id) {
	global $wpdb;

	$event_id = (int) $event_id;
	$table_name = $wpdb->prefix . 'lanorg_tournaments';

	return $wpdb->get_results("SELECT * FROM $table_name WHERE event_id = $event_id", 0);
}

function lanorg_tournament_exists($tournament_id) {
	global $wpdb;

	$tournament_id = (int) $tournament_id;
	$table_name = $wpdb->prefix . 'lanorg_tournaments';

	return $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM $table_name WHERE id = $tournament_id")) > 0;
}

// Returns an associative array containing id as key and name as value.
function lanorg_get_tournament_list() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'lanorg_tournaments';

	$tournaments = $wpdb->get_results("SELECT id, game FROM $table_name", ARRAY_A);
	$tournament_list = array();
	foreach ($tournaments as $tournament) {
		$tournament_list[$tournament['id']] = $tournament['game'];
	}

	return $tournament_list;
}

?>