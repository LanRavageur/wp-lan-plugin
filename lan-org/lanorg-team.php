<?php

// Teams administration table
class LanOrgTeamListTable extends LanOrgListTable {
	var $add_user_form;
	var $kick_user_form;

	function __construct($options){

		// Form in edit team panel to add a new user
		$this->add_user_form = array(
			array(
				'type' => 'select',
				'key' => 'add_user_id',
				'label' => __('User Name', 'lanorg'),
				'choices' => 'lanorg_get_user_list',
				'validator' => 'empty',
			),
		);

		parent::__construct($options);
	}

	// Get the form used to kick players
	// It is made of checkboxes
	function get_kick_form() {
		global $wpdb;
		$form = array();

		$table_name = $wpdb->prefix . 'lanorg_teams_users';
		$user_table_name = $wpdb->prefix . 'users';

		$team_users = $wpdb->get_results(
			"SELECT user_id, " .
			"(SELECT user_login FROM $user_table_name WHERE ID = user_id) as username " .
			"FROM $table_name WHERE team_id = $this->id"
		);

		foreach ($team_users as $user) {
			array_push($form, array(
				'type' => 'checkbox',
				'key' => 'user-' . $user->user_id,
				'value' => '1',
				'text' => $user->username,
			));
		}

		return $form;
	}

	// Display forms to add a new user and kick users from team
	function display_form() {
		global $lanOrg;
		$action = $this->get_action();

		parent::display_form();

		if ($action == 'edit') {

			echo '<h3>Users in team</h3>';

			if (count($this->kick_user_form) > 0) {
				echo $this->get_form_markup($this->kick_user_form, 'Remove');
			}
			else {
				echo '<p>This team doesn\'t have any member.</p>';
			}

			echo '<h3>Add user to team</h3>';
			echo $this->get_form_markup($this->add_user_form, 'Add');
		}
	}

	// Apply modifications when user press Remove or Add
	function run_action() {
		global $wpdb, $lanOrg;

		parent::run_action();

		$this->kick_user_form = $this->get_kick_form();
		$values = array();
		$errors = array();

		// Get POST data to add user
		if (lanorg_form_post($this->add_user_form, $values, $lanOrg->form_prefix)) {

			// Verify user name
			if (lanorg_form_validation($this->add_user_form, $values, $errors)) {
				// Add user to team
				lanorg_join_team($this->id, $values['add_user_id']);
			}
		}

		$values = array();
		// Get POST data to remove user
		if (lanorg_form_post($this->kick_user_form, $values, $lanOrg->form_prefix)) {

			// Verify user name
			if (lanorg_form_validation($this->kick_user_form, $values, $errors)) {

				// Remove every checked user
				$team_users = lanorg_get_team_users($this->id);
				print_r($values);
				foreach ($team_users as $user_id) {
					if ($values['user-' . $user_id]) {
						echo 'REMOVE ' . $user_id;
						lanorg_leave_team($this->id, $user_id);
					}
				}

				// Team list has changed, update checkboxes
				$this->kick_user_form = $this->get_kick_form();
			}
		}
		
	}
}

function lanorg_admin_team_page() {
	$form = array(
		array(
			'type' => 'text',
			'key' => 'name',
			'label' => __('Name', 'lanorg'),
			'validator' => 'empty',
		),
		array(
			'type' => 'select',
			'key' => 'tournament_id',
			'label' => __('Tournament', 'lanorg'),
			'choices' => 'lanorg_get_tournament_list',
			'validator' => 'empty',
		),
		array(
			'type' => 'select',
			'key' => 'owner_id',
			'label' => __('Owner', 'lanorg'),
			'choices' => 'lanorg_get_user_list',
			'validator' => 'empty',
		),
	);

	lanorg_get_admin_header('Teams', TRUE);

	lanorg_get_admin_tabs('lanorg-teams');

	$table = new LanOrgTeamListTable(array(
			'singular'  => __('team', 'lanorg'),
			'plural'    => __('teams', 'lanorg'),
			'columns' 	=> array(
				'name'					=> __('Name', 'lanorg'),
				'tournament_id' => __('Tournament', 'lanorg'),				
				'owner_id'  		=> __('Owner', 'lanorg'),				
			),
			'table_name'=> 'lanorg_teams',
			'form' => $form,
		));

	$table->set_title_column('name');

	$table->run_action();
	$table->prepare_items();

	$table->display();

	lanorg_get_admin_footer();
}

// User joins a team
function lanorg_join_team($team_id, $user_id) {
	global $wpdb;

	$table_name = $wpdb->prefix . 'lanorg_teams_users';

	// Escape is done by wpdb->insert
	$data = array(
		'team_id' => $team_id,
		'user_id' => $user_id,
	);
	return $wpdb->insert($table_name,	$data, array('%d', '%d'));
}

// User leaves a team
function lanorg_leave_team($team_id, $user_id) {
	global $wpdb;

	$table_name = $wpdb->prefix . 'lanorg_teams_users';
	$team_id = (int) $team_id;
	$user_id = (int) $user_id;

	$wpdb->query("DELETE FROM $table_name WHERE team_id = $team_id AND user_id = $user_id LIMIT 1");
}

// Return all users of a team
function lanorg_get_team_users($team_id) {
	global $wpdb;

	$team_id = (int) $team_id;
	$table_name = $wpdb->prefix . 'lanorg_teams_users';

	return $wpdb->get_col("SELECT user_id FROM $table_name WHERE team_id = $team_id", 0);
}


function lanorg_team_page() {
	global $lanOrg;

	$lan_events = lanorg_get_all_events();

	foreach ($lan_events as $event) {
		$event->tournaments = lanorg_get_tournaments($event->id);
	}

	$GLOBALS['lan_events'] = $lan_events;

	$lanOrg->render_two_column_page('lanorg-team.php');
}

// Return url to teams management page
function lanorg_get_team_url() {
	global $wp_rewrite;

	if ($wp_rewrite->using_permalinks()) {
		$url = $wp_rewrite->root . 'team/';
		$url = home_url(user_trailingslashit($url));
	}
	else {
		$vars = array('lanorg_page' => 'teams');
		$url = add_query_arg($vars, home_url( '/'));
	}
	return htmlentities($url, NULL, 'UTF-8');
}

?>