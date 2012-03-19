<?php

require_once('lanorg-admin-list.php');

$lanorg_tournament_form = array(
	array(
		'type' => 'text',
		'key' => 'game',
		'label' => __('Game :', 'lanorg'),
		'validator' => 'empty',
	),
	array(
		'type' => 'text',
		'key' => 'publisher',
		'label' => __('Publisher :', 'lanorg'),
	),
	array(
		'type' => 'checkbox',
		'key' => 'allow_teams',
		'text' => __('Enable teams', 'lanorg'),
		'default' => FALSE,
	)
);

// Get admin header & footer HTML code
function lanorg_get_admin_header($title, $add_new_link=FALSE) {
	echo '<div class="wrap">';
	echo '<h2>' . htmlentities($title, NULL, 'UTF-8');
	if ($add_new_link) {
		echo '<a href="?page=' . $_REQUEST['page'] . '&action=insert" class="add-new-h2">Add New</a>';
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
		'lanorg-tournaments' => 'Tournaments',
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

	lanorg_get_admin_header('Tournaments', TRUE);

	lanorg_get_admin_tabs('lanorg-tournaments');

	$table = new LanOrgListTable(array(
			'singular'  => 'tournament',
			'plural'    => 'tournaments',
			'columns' 	=> array(
				'game'			=> 'Game',
				'publisher' => 'Publisher',
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

?>