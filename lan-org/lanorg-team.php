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
	return $wpdb->insert($table_name,	$data, array('%d', '%d', '%d', '%d'));
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

	$teams = $wpdb->get_results("SELECT * FROM $table_name WHERE tournament_id = $tournament_id");
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

				if (isset($_POST['lanorg-invite']) && isset($_POST['lanorg-username'])) {
					$user = get_user_by('login', $_POST['lanorg-username']);
					if ($user) {
						lanorg_join_team($team_id, $user->ID, TRUE, FALSE);
					}
				}
				else if (isset($_POST['lanorg-kick']) && isset($_POST['lanorg-user']))
				{
					lanorg_leave_team($team_id, (int) $_POST['lanorg-user']);
				}
			}
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
function lanorg_display_team($team) {
	$current_user_id = get_current_user_id();

	$user_in_team = FALSE;
	$is_manager = $team->owner_id == $current_user_id;
	$can_invite = $is_manager;

	// Check if the user is in the team
	foreach ($team->users as $user) {
		if ($user->user_id == $current_user_id) {
			$user_in_team = TRUE;
			break ;
		}
	}

	echo	'<input type="hidden" name="lanorg-team-id" value="' . $team->id . '"/>';
	echo	'<table class="lanorg-team-list orange" cellspacing="0" cellpadding="0" border="0" style="width: 250px;">' .
				'<thead>' .
				'<tr class="header">' .
				'<th class="left"></th>' .
				'<th><span>' . htmlentities($team->name, NULL, 'UTF-8') . '</span>';

	echo	'<th>';
	if ($user_in_team) {
		echo	'<input type="submit" name="lanorg-leave" class="lanorg-button" value="Partir"/></th>';
	}
	else {
		echo	'<input type="submit" name="lanorg-join" class="lanorg-button" value="Rejoindre"/></th>';
	}

	echo	'</th><th class="right"></th>' .
				'</tr>' .
				'</thead>',
				'<tbody>';
	foreach ($team->users as $user) {
		$invite_accepted = $user->user_accept && $user->team_accept;

		$suffix = NULL;
		if (!$user->user_accept) {
			$suffix = '(invite pending)';
		}
		else if (!$user->team_accept) {
			$suffix = '(approval pending)';
		}
		else if ($user->user_id == $team->owner_id) {
			$suffix = '(manager)';
		}
		echo 	'<tr class="row">' .
					'<td class="left"></td>' .
					'<td><span><a href="' . lanorg_get_user_profile_url($user->user_id) . '">' .
					htmlentities($user->username, NULL, 'UTF-8') . '</a>' .
					($suffix ? '<small> ' . $suffix . '</small>' : '') . '' .
					'</span></td><td>';

		if ($is_manager) {
			echo	'<input type="radio" name="lanorg-user" class="lanorg-button" value="' . $user->user_id . '"/></td>';
		}

		echo	'</td><td class="right"></td>' .
					'</tr>';
	}

	if ($can_invite) {
		echo 	'<tr>' .
					'<td class="left"></td>' .
					'<td><input type="text" name="lanorg-username" class="lanorg-text" placeholder="Inviter un utilisateur..."/></td>';
		echo	'<td><input type="submit" name="lanorg-kick" class="lanorg-button" value="Expulser ^"/>';
		echo	'<input type="submit" name="lanorg-invite" class="lanorg-button" value="Inviter"/></td>';
		echo	'<td class="right"></td>' .
					'</tr>';
	}
	echo 	'</tbody>' .
				'</table>';
/*
<tbody>
<tr class="row">
<td class="left"></td>
<td>Les loups</td>
<td class="right"></td>
</tr>
<tr class="row">
<td class="left"></td>
<td>Les loups</td>
<td class="right"></td>
</tr>
</tbody>
</table>
*/

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