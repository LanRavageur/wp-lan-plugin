<?php

class TournamentBrackets {
	// Array of matches per rounds
	private $rounds = array();

	// List of teams
	private $teams;

	public function __construct($teams=array()) {
		$this->teams = $teams;
	}

	public function CreateSimpleEliminationTree() {
		$round_index = 0;

		$round_index = 0;
		$nb_teams = count($this->teams);

		do
		{
			$this->CreateRound();
			for ($i = 0; $i < $nb_teams; $i += 2) {


				$this->CreateMatch($round_index, NULL, NULL);
			}

			$round_index++;
			$nb_teams = ceil($nb_teams / 2.0);

		} while ($nb_teams > 1);
	}

	// Set result for every played match in round
	public function CreateMatchesResults($matches_played) {
		$round_index = 0;

		// Associative array of id => team
		$teams_list = array();
		foreach ($this->teams as $team) {
			$teams_list[$team->id] = $team;
		}

		foreach ($this->rounds as &$matches_planned) {
			$i = 0;
			$skip_branches = 0;
			foreach ($matches_planned as &$match_planned) {
				$match_planned['result'] = NULL;
				$match_planned['team1'] = NULL;
				$match_planned['team2'] = NULL;

				// Every team plays on first round
				if ($round_index == 0) {
					$match_planned['team1'] = $this->teams[$i];
					$match_planned['team2'] = ($i + 1 < count($this->teams)) ? $this->teams[$i + 1] : NULL;
				}
				else {
					// Find out which team played
					$match_index = $i;
					$last_round = $this->rounds[$round_index - 1];

					if (isset($last_round[$match_index]) &&
							isset($last_round[$match_index + 1]))
					{
						$match1 = $last_round[$match_index];
						$match2 = $last_round[$match_index + 1];

						$match_result1 = $match1['result'];
						$match_result2 = $match2['result'];

						if ($match_result1 && $match_result2) {
							$match_planned['team1'] = ($match_result1['winner'] == 1)
								? $match1['team1']
								: $match1['team2'];

							$match_planned['team2'] = ($match_result2['winner'] == 1)
								? $match2['team1']
								: $match2['team2'];

						}
					}
				}

				if ($match_planned['team1'] && $match_planned['team2']) {
					// Match exists, no gap between each match
					$skip_branches = 0;
					foreach ($matches_played as $key => &$match_played) {
						if ($match_played['round'] == $round_index &&
								$match_planned['team1']->id == $match_played['team1_id'] &&
								$match_planned['team2']->id == $match_played['team2_id'])
						{
							$match_planned['result'] = $match_played;
							unset($matches_played[$key]);
							break ;
						}
					}
				}
				else {
					//$skip_branches++;
				}

				// Only pass to next teams when it is correctly aligned by preceding round branch
				if ($skip_branches % 4 == 0) {
					$i += 2;
				}
			}

			// Seperate played matches which doesn't get into the dynamically generated tree
			if ($round_index > 0) {
				// Create empty matches to create gap
				$last_nb_matches = count($this->rounds[$round_index - 1]);
				$nb_matches = count($this->rounds[$round_index]);
				$skip_branches = ceil($last_nb_matches / 2) - $nb_matches;
				for ($i = 0; $i < $skip_branches; $i++) {
					$this->CreateMatch($round_index, NULL, NULL, NULL);
				}
			}

			foreach ($matches_played as $key => &$match_played) {
				if ($match_played['round'] == $round_index) {
					$team1_id = (int) $match_played['team1_id'];
					$team2_id = (int) $match_played['team2_id'];

					if (isset($teams_list[$team1_id]) && isset($teams_list[$team2_id])) {
						$team1 = $teams_list[$team1_id];
						$team2 = $teams_list[$team2_id];
						$this->CreateMatch($round_index, $team1, $team2, $match_played);
					}
				}
			}

			$round_index++;
		}
	}

	// Create a new round at the end of the list
	public function CreateRound() {
		array_push($this->rounds, array());
	}

	// Create a new match
	// @param round index begining at 0
	// @param team1 team playing the match
	// @param team2 team opponent
	// @param result Match result, array containing scores and the winning team
	public function CreateMatch($round_index, $team1, $team2, $result=NULL) {
		$added = FALSE;
		if (isset($this->rounds[$round_index])) {
			$match_index = count($this->rounds[$round_index]);

			$match = array(
				'team1' => $team1,
				'team2' => $team2,
				'result' => $result,
				'unique_id1' => self::GetUniqueID($round_index, $match_index, 1),
				'unique_id2' => self::GetUniqueID($round_index, $match_index, 2),
			);

			array_push($this->rounds[$round_index], $match);
		}

		return $added;
	}

	public static function GetUniqueID($round_index, $match_index, $team_index) {
		$unique_id = $round_index . '_' . $match_index;
		return $unique_id . '_' . $team_index;
	}

	public function GetRounds() {
		return $this->rounds;
	}

	// Set the winner team
	// A string that contains a match in this format :
	// [round_index]_[match_index]_[team_index]
	public function AddMatch($tournament_id, $winner) {

		$round_index = 0;
		foreach ($this->rounds as &$matches_planned) {
			$match_index = 0;
			foreach ($matches_planned as &$match_planned) {
				$unique_id1 = TournamentBrackets::GetUniqueID($round_index, $match_index, '1');
				$unique_id2 = TournamentBrackets::GetUniqueID($round_index, $match_index, '2');

				if ($unique_id1 == $winner || $unique_id2 == $winner) {
					$winner_team_index = $unique_id1 == $winner ? 1 : 2;

					lanorg_add_match(	$tournament_id, $round_index,
														$match_planned['team1']->id, $match_planned['team2']->id,
														$winner_team_index);
				}

				$match_index++;
			}
			$round_index++;
		}
	}

	// Delete a match
	// A string that contains a match in this format :
	// [round_index]_[match_index]_[team_index]
	public function DeleteMatch($tournament_id, $match_id) {
		$round_index = 0;
		foreach ($this->rounds as &$matches_planned) {
			$match_index = 0;
			foreach ($matches_planned as &$match_planned) {
				$unique_id1 = TournamentBrackets::GetUniqueID($round_index, $match_index, '1');
				$unique_id2 = TournamentBrackets::GetUniqueID($round_index, $match_index, '2');

				if ($unique_id1 == $match_id || $unique_id2 == $match_id) {
					$winner_team_index = $unique_id1 == $match_id ? 1 : 2;

					lanorg_delete_match($tournament_id, $round_index,
															$match_planned['team1']->id, $match_planned['team2']->id,
															$winner_team_index);
				}
				$match_index++;
			}
			$round_index++;
		}
	}

	public function ForEachMatch($func) {
		$round_index = 0;
		foreach ($this->rounds as &$matches_planned) {
			$match_index = 0;
			foreach ($matches_planned as &$match_planned) {
				if ($func($round_index, $matches_planned, $match_index, $match_planned)) {
					break ;
				}
				$match_index++;
			}
			$round_index++;
		}
	}
}

function lanorg_render_tournament_page($event_id, $tournament_id=NULL) {
	global $lanOrg;

	if ($tournament_id !== NULL) {
		$teams = lanorg_get_teams($tournament_id);
		$matches = lanorg_get_matches($tournament_id);
		$rounds = array();

		$brackets = new TournamentBrackets($teams);
		$brackets->CreateSimpleEliminationTree();
		$brackets->CreateMatchesResults($matches);

		if (isset($_POST['lanorg-match'])) {
			$selected_match = $_POST['lanorg-match'];

			if (isset($_POST['lanorg-add-match'])) {
				$brackets->AddMatch($tournament_id, $selected_match);
			}
			if (isset($_POST['lanorg-delete-match'])) {
				$brackets->DeleteMatch($tournament_id, $selected_match);
			}
			// Reload matches
			$matches = lanorg_get_matches($tournament_id);
			$brackets = new TournamentBrackets($teams);
			$brackets->CreateSimpleEliminationTree();
			$brackets->CreateMatchesResults($matches);
		}

		$rounds = $brackets->GetRounds();

		$GLOBALS['tournament'] = lanorg_get_tournament_by_id($tournament_id);
		$GLOBALS['teams'] = $teams;
		$GLOBALS['rounds'] = $rounds;
		$GLOBALS['event_id'] = $event_id;

		$lanOrg->render_two_column_page('lanorg-tournament-view.php');

	}
	else { // List all tournaments
		$tournaments = lanorg_get_tournaments($event_id);

		$GLOBALS['tournaments'] = $tournaments;
		$GLOBALS['event_id'] = $event_id;

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
		
		$stmt = "DELETE FROM $table_name WHERE " .
			"((team1_id = $team1_id OR team2_id = $team2_id) OR " .
			"(team1_id = $team2_id OR team2_id = $team1_id)) AND " .
			"round > $round AND tournament_id = $tournament_id";
		$result = !!$wpdb->query($stmt);
	}

	return $result;
}

// Return url to registration page
function lanorg_get_tournament_url($event_id, $tournament_id=NULL) {
	global $wp_rewrite;

	if ($wp_rewrite->using_permalinks()) {
		$url = $wp_rewrite->root . 'tournament/' . $event_id . '/';
		if ($tournament_id !== NULL) {
			$url .= $tournament_id . '/';
		}
		$url = home_url(user_trailingslashit($url));
	}
	else {
		$vars = array(
			'lanorg_page' => 'tournament',
			'event' => $event_id,
		);
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