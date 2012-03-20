<?php

class LanOrgTeamListTable extends LanOrgListTable {
	var $add_user_form;

	function __construct($options){
		$this->add_user_form = array(
			array(
				'type' => 'text',
				'key' => 'username',
				'label' => __('User Name', 'lanorg'),
				'validator' => 'empty',
			),
		);
		parent::__construct($options);
	}

	function get_kick_form() {
		$form = array();

		array_push($form, array(
			'type' => 'checkbox',
			'key' => 'user-1',
			'text' => 'Example',
		));

		return $form;
	}

	function display_form() {
		global $lanOrg;
		$action = $this->get_action();

		parent::display_form();

		if ($action == 'edit') {
			$kick_form = $this->get_kick_form();

			echo '<h3>Users in team</h3>';
			$values = array();
			lanorg_form_post($kick_form, $values, $lanOrg->form_prefix);

			echo $this->get_form_markup($kick_form, 'Remove', $values);
			

			echo '<h3>Add user to team</h3>';
			echo $this->get_form_markup($this->add_user_form, 'Add');
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