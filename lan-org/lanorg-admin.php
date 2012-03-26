<?php

$lanorg_event_form = NULL;

// Get a form to edit a tournament
// Each key must match a column in the tournement table
function lanorg_get_tournament_form() {
	return array(
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

// Get a form to edit an event
// Each key must match a column in the event table.
function lanorg_get_event_form() {
	return array(
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

// Get header HTML code for each admin tabs
function lanorg_get_admin_header($title, $add_new_link=FALSE) {
	// WordPress wrapper
	echo '<div class="wrap">';

	// Title
	echo '<h2>' . htmlentities($title, NULL, 'UTF-8');

	// The 'Add new item' link after title ?
	if ($add_new_link) {
		echo '<a href="?page=' . $_REQUEST['page'] . '&action=insert" class="add-new-h2">' . __('Add New', 'lanorg') . '</a>';
	}

	echo '</h2>';
}

// Get footer HTML code for each admin tabs
function lanorg_get_admin_footer() {
	echo '</div>'; // Closes <div class="wrap">
}

// Display admin tabs
function lanorg_get_admin_tabs($current) {
	$tabs = array(
		'lanorg' => __('Configuration', 'lanorg'),
		'lanorg-events' => __('Events', 'lanorg'),
		'lanorg-tournaments' => __('Tournaments', 'lanorg'),
		'lanorg-teams' => __('Teams', 'lanorg'),
	);

	echo '<h2 class="nav-tab-wrapper">';

	foreach ($tabs as $tab => $label) {
		$css_class = ($tab == $current) ? ' nav-tab-active' : '';
		echo "<a class='nav-tab$css_class' href='?page=$tab'>$label</a>";
	}
	echo '</h2>';
}

// Display admin settings tab
function lanorg_admin_settings() {
	global $lanOrg;

	lanorg_get_admin_header(__('Configuration', 'lanorg'), FALSE);

	lanorg_get_admin_tabs('lanorg');

	$values = array();
	$errors = array();

	echo 	'<form method="POST" action="options.php">';
	do_settings_sections('lanorg_settings');
	settings_fields('lanorg');
	echo 	'<p class="submit">' .
				'<input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes"/>' .
				'</p>';
	echo	'</form>';

}

function lanorg_add_admin_settings() {

	// Main admin section
	add_settings_section('lanorg_settings_main',
			'LAN Organization Settings',
			'lanorg_get_admin_section_header',
			'lanorg_settings'
	);

	//lanorg_add_setting_field('checkbox', 'dummy', 'Title', 'Description');
	//lanorg_add_setting_field('text', 'dummy', 'Title', 'Description');
}

// Add a new setting in configuration tab
// Available types :
//   * checkbox
//   * text
// Available sanitizers :
//   * TODO
function lanorg_add_setting_field($type, $id, $title, $description, $sanitizer=NULL) {
	if ($type == 'checkbox') {
		$sanitizer = 'lanorg_sanitize_checkbox';
	}

	add_settings_field($id,
		$title,
		'eg_setting_callback_function',
		'lanorg_settings',
		'lanorg_settings_main',
		array(
			'id' => $id,
			'desc' => $description,
			'type' => $type,
		)
	);
	register_setting('lanorg', $id, $sanitizer);
}

function lanorg_get_admin_section_header() {
}

function lanorg_sanitize_checkbox($value) {
	return empty($value) ? '0' : '1';
}

function eg_setting_callback_function($args) {
	$value = get_option($args['id']);
	$type = $args['type'];

	$extra_attrs = '';
	
	if ($type == 'checkbox') {
		$value = '1';
		$extra_attrs .= checked(1, get_option($args['id']), false);
	}

	echo 	'<input name="' . $args['id'] . '" id="id_' . $args['id'] . '" ' .
				'type="' . $args['type'] . '" value="' . htmlentities($value, NULL, 'UTF-8') . '" ' .
				'class="code" ' . $extra_attrs .
				'/>';
	if ($type == 'checkbox') {
		echo ' ' . $args['desc'];
	}
}

function lanorg_admin_tournaments() {

	$form = lanorg_get_tournament_form();

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
			'form' => $form,
		));

	$table->set_title_column('game');

	$table->run_action();
	$table->prepare_items();

	$table->display();

	lanorg_get_admin_footer();
}

function lanorg_admin_events() {

	$form = lanorg_get_event_form();

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
			'form' => $form,
		));

	$table->set_title_column('title');

	$table->run_action();
	$table->prepare_items();

	$table->display();

	lanorg_get_admin_footer();
}

?>