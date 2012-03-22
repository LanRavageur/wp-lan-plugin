<?php

function lanorg_tournament_page($tournament_id=NULL) {
	global $lanOrg;

	if ($tournament_id !== NULL) {
		$teams = lanorg_get_teams($tournament_id);
		$matches = lanorg_get_matches($tournament_id);
		$rounds = array();


		$round = array();
		for ($i = 0; $i < count($teams); $i += 2) {
			$team1 = $teams[$i];
			$team2 = ($i + 1 < count($teams)) ? $teams[$i + 1] : NULL;

			$match = array('team1' => $team1, 'team2' => $team2, 'result' => NULL);

			array_push($round, $match);

			//lanorg_get_next_matches($matches, $rounds, 0, $team1->id, $team2->id);
		}

		$round_index = 0;

		$has_team = FALSE;
		$selected_match = NULL;

		if (isset($_POST['lanorg-match'])) {
			$selected_match = $_POST['lanorg-match'];
		}

		do {
			$next_round = array();
			$has_team = FALSE;

			$match_index = 0;
			foreach ($round as &$match_planned) {
				$unique_id = $round_index . '_' . $match_index;
				$unique_id1 = $unique_id . '_1';
				$unique_id2 = $unique_id . '_2';
				$match_planned['unique_id1'] = $unique_id1;
				$match_planned['unique_id2'] = $unique_id2;

				// Match has been selected
				if ($selected_match == $unique_id1 || $selected_match == $unique_id2) {
					$selected_team = $selected_match == $unique_id1 ? 1 : 2;
					if (isset($_POST['lanorg-delete-match'])) {
						// Delete a match
						if (lanorg_delete_match($tournament_id, $round_index,
								$match_planned['team1']->id, $match_planned['team2']->id))
						{
							// Update local copy
							$matches = lanorg_get_matches($tournament_id);
						}
						continue ; // this match no longer exist
					}

					if (isset($_POST['lanorg-winner'])) {

						// Define a winner team
						if (lanorg_add_match($tournament_id, $round_index,
							$match_planned['team1']->id, $match_planned['team2']->id,
							$selected_team))
						{
							// Update local copy
							$matches = lanorg_get_matches($tournament_id);
						}
					}
				}

				$winner_id = NULL;

				foreach ($matches as &$match_played) {

					if ($match_played['round'] == $round_index &&
							$match_planned['team1']->id == $match_played['team1_id'] &&
							$match_planned['team2']->id == $match_played['team2_id'])
					{
						$match_planned['result'] = $match_played;
						$winner_id = $match_played['team1_id'];
						break ;
					}
				}

				$next_match_index = floor($match_index / 2);
				if ($match_index % 2 == 0) {
					$next_round[$next_match_index] = array();
				}
				if ($winner_id !== NULL) {
					$team_found = NULL;
					// Find winner team by id
					foreach ($teams as $team) {
						if ($team->id == $winner_id) {
							$team_found = $team;
							$has_team = TRUE;
						}
					}

					if ($team_found !== NULL) {
						// Winner team advance
						$next_round[$next_match_index][($match_index % 2 == 0) ? 'team1' : 'team2'] =
							$team_found;
					}

				}

				$match_index++;
			}
			
			array_push($rounds, $next_round);

			$rounds[$round_index] = $round;
			$round_index++;
			$round = $next_round;
		} while ($has_team);
		//array_push($rounds, $teams);

		//array_push($rounds, array_slice($teams, 0, 4));

		//array_push($rounds, array_slice($teams, 0, 2));

		//array_push($rounds, array_slice($teams, 0, 2));

		$GLOBALS['tournament'] = lanorg_get_tournament_by_id($tournament_id);
		$GLOBALS['teams'] = $teams;
		$GLOBALS['rounds'] = $rounds;

		$lanOrg->render_two_column_page('lanorg-tournament-view.php');
	}
	else {
		$lan_events = lanorg_get_all_events();

		foreach ($lan_events as $event) {
			$event->tournaments = lanorg_get_tournaments($event->id);
		}

		$GLOBALS['lan_events'] = $lan_events;

		$lanOrg->render_two_column_page('lanorg-tournament.php');
	}
}

function lanorg_add_match($tournament_id, $round, $team1_id, $team2_id, $winner) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'lanorg_matches';

	return $wpdb->insert($table_name, array(
			'tournament_id' => $tournament_id,
			'round' => $round,
			'team1_id' => $team1_id,
			'team2_id' => $team2_id,
			'winner' => $winner,
		), array('%d', '%d', '%d', '%d', '%d')
	);
}

// Cancel a match
function lanorg_delete_match($tournament_id, $round, $team1_id, $team2_id) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'lanorg_matches';
	$result = FALSE;

	$stmt = "DELETE FROM $table_name WHERE " .
		"team1_id = $team1_id AND team2_id = $team2_id AND " .
		"round = $round AND tournament_id = $tournament_id";

	if ($wpdb->query($stmt)) {
		// Delete all submatches which the user played
		
		$stmt = "DELETE FROM $table_name WHERE " .
			"((team1_id = $team1_id OR team2_id = $team2_id) OR " .
			"(team1_id = $team2_id OR team2_id = $team1_id)) AND " .
			"round > $round AND tournament_id = $tournament_id";
		$result = !!$wpdb->query($stmt);
	}
	
	return $result;
}

// Return url to registration page
function lanorg_get_tournament_url($tournament_id=NULL) {
	global $wp_rewrite;

	if ($wp_rewrite->using_permalinks()) {
		$url = $wp_rewrite->root . 'tournament/';
		if ($tournament_id !== NULL) {
			$url .= $tournament_id . '/';
		}
		$url = home_url(user_trailingslashit($url));
	}
	else {
		$vars = array('lanorg_page' => 'tournament');
		if ($tournament_id !== NULL) {
			$vars['tournament_id'] = $tournament_id;
		}
		$url = add_query_arg($vars, home_url( '/'));
	}
	return htmlentities($url, NULL, 'UTF-8');
}

// Get all matches played for a tournement
function lanorg_get_matches($tournament_id) {
	global $wpdb;

	$tournament_id = (int) $tournament_id;
	$table_name = $wpdb->prefix . 'lanorg_matches';

	return $wpdb->get_results("SELECT * FROM $table_name WHERE tournament_id = $tournament_id", ARRAY_A);
}

// Get all tournement of an event
function lanorg_get_tournaments($event_id) {
	global $wpdb;

	$event_id = (int) $event_id;
	$table_name = $wpdb->prefix . 'lanorg_tournaments';

	return $wpdb->get_results("SELECT * FROM $table_name WHERE event_id = $event_id", 0);
}

// Get a tournament by its id
function lanorg_get_tournament_by_id($tournament_id) {
	global $wpdb;

	$tournament_id = (int) $tournament_id;
	$table_name = $wpdb->prefix . 'lanorg_tournaments';

	return $wpdb->get_row("SELECT * FROM $table_name WHERE id = $tournament_id LIMIT 1", ARRAY_A);
	
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