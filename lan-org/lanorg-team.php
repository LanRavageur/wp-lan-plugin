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

		$team_users = lanorg_get_team_users($this->id);

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

			echo '<h3>' . _e('Users in team', 'lanorg') . '</h3>';

			if (count($this->kick_user_form) > 0) {
				echo $this->get_form_markup($this->kick_user_form, 'Remove');
			}
			else {
				echo '<p>' . _e('This team doesn\'t have any member', 'lanorg') . '.</p>';
			}

			echo '<h3>' . _e('Add user to team', 'lanorg') . '</h3>';
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
				foreach ($team_users as $user) {
					if ($values['user-' . $user->user_id]) {
						lanorg_leave_team($this->id, $user->user_id);
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

	lanorg_get_admin_header(__('Teams', 'lanorg'), TRUE);

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

// Create a team
function lanorg_create_team($tournament_id, $owner_id, $team_name) {
	global $wpdb;

	if (lanorg_tournament_exists($tournament_id)) {
		$table_name = $wpdb->prefix . 'lanorg_teams';
		$wpdb->insert($table_name, array(
				'name' => $team_name,
				'tournament_id' => $tournament_id,
				'owner_id' => $owner_id,
				'position' => 16777215,
			), array('%s', '%d', '%d')
		);
		lanorg_join_team($wpdb->insert_id, $owner_id);
		lanorg_reorder_team($tournament_id);
	}
}

// Delete a team
function lanorg_delete_team($team_id) {
	global $wpdb;

	$deleted = FALSE;
	$team_id = (int) $team_id;
	$table_name = $wpdb->prefix . 'lanorg_teams_users';

	// Remove all players from team
	if ($wpdb->query("DELETE FROM $table_name WHERE team_id = $team_id") !== FALSE) {
		$table_name = $wpdb->prefix . 'lanorg_teams';

		$deleted = !!$wpdb->query("DELETE FROM $table_name WHERE id = $team_id LIMIT 1");
	}

	return $deleted;
}

// User joins a team
function lanorg_join_team($team_id, $user_id, $team_accepted=TRUE, $user_accepted=TRUE) {
	global $wpdb;

	$table_name = $wpdb->prefix . 'lanorg_teams_users';

	// Escape is done by wpdb->insert
	$data = array(
		'team_id' => $team_id,
		'user_id' => $user_id,
		'team_accept' => $team_accepted,
		'user_accept' => $user_accepted,
	);
	if ($wpdb->insert($table_name, $data, array('%d', '%d', '%d', '%d')) === FALSE) {
		$data = array();

		if ($team_accepted) {
			$data['team_accept'] = $team_accepted;
		}
		if ($user_accepted) {
			$data['user_accept'] = $user_accepted;
		}
		if (count($data) > 0) {
			$wpdb->update($table_name, $data,
				array('team_id' => $team_id, 'user_id' => $user_id), array('%d', '%d'));
		}
	}
}

// User leaves a team
function lanorg_leave_team($team_id, $user_id) {
	global $wpdb;

	$table_name = $wpdb->prefix . 'lanorg_teams_users';
	$team_id = (int) $team_id;
	$user_id = (int) $user_id;

	if ($wpdb->query("DELETE FROM $table_name WHERE team_id = $team_id AND user_id = $user_id LIMIT 1")) {
		if (lanorg_is_owner_of_team($team_id, $user_id)) {
			// Delete team if the manager leave it
			lanorg_delete_team($team_id);
		}
	}
}

// Reorder team positions
function lanorg_reorder_team($tournament_id) {
	global $wpdb;

	$table_name = $wpdb->prefix . 'lanorg_teams';

	$teams = lanorg_get_teams($tournament_id);
	$position = 0;
	foreach ($teams as $team) {
		if ((int) $team->position != $position) {
			$wpdb->update($table_name, array(
				'position' => $position,
			), array('id' => $team->id), array('%d'));
		}
		$position++;
	}
}

// Return all users of a team
function lanorg_get_team_users($team_id) {
	global $wpdb;

	$team_id = (int) $team_id;
	$table_name = $wpdb->prefix . 'lanorg_teams_users';
	$user_table_name = $wpdb->prefix . 'users';

	$team_users = $wpdb->get_results(
		"SELECT *, " .
		"(SELECT user_login FROM $user_table_name WHERE ID = user_id) as username " .
		"FROM $table_name WHERE team_id = $team_id"
	);
	return $team_users;
}

// Return all teams for a tournament
function lanorg_get_teams($tournament_id) {
	global $wpdb;

	$tournament_id = (int) $tournament_id;
	$table_name = $wpdb->prefix . 'lanorg_teams';

	$teams = $wpdb->get_results("SELECT * FROM $table_name WHERE tournament_id = $tournament_id ORDER BY position ASC");
	return $teams;
}

// Return team by id
function lanorg_get_teams_by_id($team_id) {
	global $wpdb;

	$team_id = (int) $team_id;
	$table_name = $wpdb->prefix . 'lanorg_teams';

	$team = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $team_id", ARRAY_A);
	return $team;
}

// Return if the user is the owner of a team
function lanorg_is_owner_of_team($team_id, $user_id) {
	global $wpdb;

	$team_id = (int) $team_id;
	$user_id = (int) $user_id;
	$table_name = $wpdb->prefix . 'lanorg_teams';

	return $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM $table_name WHERE id = $team_id AND owner_id = $user_id")) > 0;
}



function lanorg_get_create_team_form() {
	return array(
		array(
			'type' => 'text',
			'key' => 'create_team',
			'validator' => 'empty',
		),
	);
}

function lanorg_get_create_team_form_markup() {
	return lanorg_form(lanorg_get_create_team_form());
}

function lanorg_team_page() {
	global $lanOrg;

	$current_user_id = get_current_user_id();

	// Apply button action
	if (isset($_POST['lanorg-team-id'])) {
		$team_id = (int) $_POST['lanorg-team-id'];

		$team = lanorg_get_teams_by_id($team_id);

		if ($team) {
			if (isset($_POST['lanorg-join'])) {
				lanorg_join_team($team_id, $current_user_id, FALSE, TRUE);
			}

			if (isset($_POST['lanorg-leave'])) {
				lanorg_leave_team($team_id, $current_user_id);
			}

			// Action for team administrator
			if ($team['owner_id'] == $current_user_id) {

				// Invite a user
				if (isset($_POST['lanorg-invite']) && isset($_POST['lanorg-username'])) {
					$user = get_user_by('login', $_POST['lanorg-username']);
					if ($user) {
						lanorg_join_team($team_id, $user->ID, TRUE, FALSE);
					}
				}
				// Kick a user from team
				else if (isset($_POST['lanorg-kick']) && isset($_POST['lanorg-user']))
				{
					lanorg_leave_team($team_id, (int) $_POST['lanorg-user']);
				}
				// Accept a user from team
				else if (isset($_POST['lanorg-accept'])) {
					$user = get_user_by('id', $_POST['lanorg-user']);
					if ($user) {
						lanorg_join_team($team_id, $user->ID, TRUE, FALSE);
					}
				}
			}
		}
	}

	// Create team
	$create_team_form = lanorg_get_create_team_form();

	$values = array();
	if (lanorg_form_post($create_team_form, $values, $lanOrg->form_prefix) && isset($_POST['lanorg-tournament-id'])) {

		$errors = array();
		// Verify user name
		if (lanorg_form_validation($create_team_form, $values, $errors)) {
			$tournament_id = (int) $_POST['lanorg-tournament-id'];

			lanorg_create_team($tournament_id, $current_user_id, $values['create_team']);
		}
	}

	$lan_events = lanorg_get_all_events();

	foreach ($lan_events as $event) {
		$event->tournaments = lanorg_get_tournaments($event->id);

		foreach ($event->tournaments as $tournament) {
			$tournament->teams = lanorg_get_teams($tournament->id);

			foreach ($tournament->teams as $team) {
				$team->users = lanorg_get_team_users($team->id);
			}
			//lanorg_get_team_users($tournament->id);
		}
	}

	$GLOBALS['lan_events'] = $lan_events;

	$lanOrg->render_two_column_page('lanorg-team.php');
}

// Display team table HTML markup
function lanorg_display_team($team, $tournament) {
	$current_user_id = get_current_user_id();

	$user_in_team = FALSE;
	$user_accept = FALSE;
	$is_manager = $team->owner_id == $current_user_id;
	$can_invite = $is_manager;

	// Check if the user is in the team
	foreach ($team->users as $user) {
		if ($user->user_id == $current_user_id) {
			$user_in_team = TRUE;
			$user_accept = $user->user_accept;
			break ;
		}
	}

	echo	'<input type="hidden" name="lanorg-team-id" value="' . $team->id . '"/>';
	echo	'<table class="lanorg-team-list orange" cellspacing="0" cellpadding="0" border="0" style="width: 290px;">' .
				'<thead>' .
				'<tr class="header">' .
				'<th class="left"></th>' .
				'<th><span>' . htmlentities($team->name, NULL, 'UTF-8') . '</span>';

	echo	'<th class="lanorg-right">';
	if ($user_in_team) {
		if ($user_accept) {
			if ($is_manager) {
				echo	'<input type="submit" name="lanorg-leave" class="lanorg-button" value="' . __('Delete', 'lanorg') . '"/>';
			}
			else {
				echo	'<input type="submit" name="lanorg-leave" class="lanorg-button" value="' . __('Leave', 'lanorg') . '"/>';
			}
		}
		else {
			echo	'<input type="submit" name="lanorg-join" class="lanorg-button" value="' . __('Yes', 'lanorg') . '"/>';
			echo	'<input type="submit" name="lanorg-leave" class="lanorg-button" value="' . __('No', 'lanorg') . '"/>';
		}
	}
	else {
		if (!$tournament || !$tournament->team_size || count($team->users) < (int) $tournament->team_size) {
			echo	'<input type="submit" name="lanorg-join" class="lanorg-button" value="' . __('Join', 'lanorg') . '"/>';
		}
	}

	echo	'<span>' .
				(($tournament && $tournament->team_size) ? ' ' . count($team->users) . ' / ' . $tournament->team_size : '') .
				'</span>';

	echo	'</th><th class="right"></th>' .
				'</tr>' .
				'</thead>',
				'<tbody>';
	foreach ($team->users as $user) {
		$invite_accepted = $user->user_accept && $user->team_accept;

		$suffix = NULL;
		if (!$user->user_accept) {
			$suffix = __('(invite pending)', 'lanorg');
		}
		else if (!$user->team_accept) {
			$suffix = __('(approval pending)', 'lanorg');
		}
		else if ($user->user_id == $team->owner_id) {
			$suffix = __('(manager)', 'lanorg');
		}
		echo 	'<tr class="row">' .
					'<td class="left"></td>' .
					'<td><span><a href="' . lanorg_get_user_profile_url($user->user_id) . '" class="lanorg-link">' .
					htmlentities($user->username, NULL, 'UTF-8') . '</a>' .
					'</span></td><td>';

		echo	($suffix ? '<small> ' . $suffix . '</small>' : '');
		if ($is_manager) {
			echo '<input type="radio" name="lanorg-user" class="lanorg-button" value="' . $user->user_id . '"/>';
		}

		echo	'</td><td class="right"></td>' .
					'</tr>';
	}

	if ($can_invite) {
		echo 	'<tr>' .
					'<td class="left"></td>' .
					'<td colspan="2"><input type="submit" name="lanorg-kick" class="lanorg-button" value="' . __('Kick', 'lanorg') . '"/>' .
					'<input type="submit" name="lanorg-accept" class="lanorg-button" value="' . __('Accept', 'lanorg') . '"/></td>' .
					'<td class="right"></td>' .
					'</tr>';

		echo 	'<tr>' .
					'<td class="left"></td>' .
					'<td><input type="text" name="lanorg-username" class="lanorg-text" placeholder="' . __('Invite a user...', 'lanorg') . '"/></td>';
		echo	'<td><input type="submit" name="lanorg-invite" class="lanorg-button" value="' . __('Invite', 'lanorg') . '"/></td>';
		echo	'<td class="right"></td>' .
					'</tr>';
	}
	echo 	'</tbody>' .
				'</table>';

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