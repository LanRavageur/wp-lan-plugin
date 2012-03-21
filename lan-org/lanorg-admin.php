<?php

$lanorg_tournament_form = NULL;
$lanorg_event_form = NULL;

// TODO : Move events and tournaments to seperated files
function lanorg_get_events() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'lanorg_events';

	$event_list = $wpdb->get_results("SELECT id, title FROM $table_name", ARRAY_A);
	$events = array();
	foreach ($event_list as $event) {
		$events[$event['id']] = $event['title'];
	}

	return $events;
}

function lanorg_init_tournament_form() {
	global $lanorg_tournament_form;

	$lanorg_tournament_form = array(
		array(
			'type' => 'select',
			'key' => 'event_id',
			'label' => __('Event', 'lanorg'),
			'choices' => 'lanorg_get_events',
			'validator' => 'empty',
		),
		array(
			'type' => 'text',
			'key' => 'game',
			'label' => __('Game', 'lanorg'),
			'validator' => 'empty',
		),
		array(
			'type' => 'text',
			'key' => 'publisher',
			'label' => __('Publisher', 'lanorg'),
		),
		array(
			'type' => 'text',
			'key' => 'platform',
			'label' => __('Platform', 'lanorg'),
		),
		array(
			'type' => 'text',
			'key' => 'max_team',
			'label' => __('Maximum team count', 'lanorg'),
		),
		array(
			'type' => 'text',
			'key' => 'team_size',
			'label' => __('Players per team', 'lanorg'),
		),
		array(
			'type' => 'checkbox',
			'key' => 'allow_teams',
			'text' => __('Enable teams', 'lanorg'),
			'default' => FALSE,
		),
	);
}

function lanorg_init_event_form() {
	global $lanorg_event_form;

	$lanorg_event_form = array(
		array(
			'type' => 'text',
			'key' => 'title',
			'label' => __('Title', 'lanorg'),
			'validator' => 'empty',
		),
		array(
			'type' => 'text',
			'key' => 'date',
			'label' => __('Date', 'lanorg'),
		),
		array(
			'type' => 'text',
			'key' => 'location',
			'label' => __('Location', 'lanorg'),
		),
	);
}

// Get admin header & footer HTML code
function lanorg_get_admin_header($title, $add_new_link=FALSE) {
	echo '<div class="wrap">';
	echo '<h2>' . htmlentities($title, NULL, 'UTF-8');
	if ($add_new_link) {
		echo '<a href="?page=' . $_REQUEST['page'] . '&action=insert" class="add-new-h2">' . __('Add New', 'lanorg') . '</a>';
	}
	echo '</h2>';
}

function lanorg_get_admin_footer() {
	echo '</div>';
}

// Display admin tabs
function lanorg_get_admin_tabs($current) {
	$tabs = array(
		'lanorg' => 'Configuration',
		'lanorg-events' => 'Events',
		'lanorg-tournaments' => 'Tournaments',
		'lanorg-teams' => 'Teams',
	);

	echo '<h2 class="nav-tab-wrapper">';
	foreach ($tabs as $tab => $label) {
		$css_class = ($tab == $current) ? ' nav-tab-active' : '';
		echo "<a class='nav-tab$css_class' href='?page=$tab'>$label</a>";
	}
	echo '</h2>';
}

function lanorg_admin_settings() {
	lanorg_get_admin_tabs('lanorg');
}

function lanorg_admin_tournaments() {
	global $lanorg_tournament_form;

	lanorg_init_tournament_form();

	lanorg_get_admin_header('Tournaments', TRUE);

	lanorg_get_admin_tabs('lanorg-tournaments');

	$table = new LanOrgListTable(array(
			'singular'  => __('tournament', 'lanorg'),
			'plural'    => __('tournaments', 'lanorg'),
			'columns' 	=> array(
				'game'			=> __('Game', 'lanorg'),
				'publisher' => __('Publisher', 'lanorg'),				
				'platform' => __('Platform', 'lanorg'),
				'allow_teams' => __('Teams enebled', 'lanorg'),
			),
			'table_name'=> 'lanorg_tournaments',
			'form' => $lanorg_tournament_form,
		));

	$table->set_title_column('game');

	$table->run_action();
	$table->prepare_items();

	$table->display();

	lanorg_get_admin_footer();
}

function lanorg_admin_events() {
	global $lanorg_event_form;

	lanorg_init_event_form();

	lanorg_get_admin_header('Events', TRUE);

	lanorg_get_admin_tabs('lanorg-events');

	$table = new LanOrgListTable(array(
			'singular'  => __('event', 'lanorg'),
			'plural'    => __('events', 'lanorg'),
			'columns' 	=> array(
				'title'		=> __('Title', 'lanorg'),
				'date' 		=> __('Date', 'lanorg'),				
				'location'=> __('Location', 'lanorg'),
			),
			'table_name'=> 'lanorg_events',
			'form' => $lanorg_event_form,
		));

	$table->set_title_column('title');

	$table->run_action();
	$table->prepare_items();

	$table->display();

	lanorg_get_admin_footer();
}

?>