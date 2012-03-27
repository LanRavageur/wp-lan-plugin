<?php
global $lanOrg, $page_title;
global $tournament, $rounds, $tournament_tree;

$lanOrg->require_login();

$page_title = 'Tournois';


?>
<h1 style="clear: none;" class="entry-title"><?php echo htmlentities($tournament['game'], NULL, 'UTF-8'); ?></h1>
<form method="POST" class="lanorg-form">
<p>
<input type="submit" name="lanorg-add-match" value="Declare winner" class="btn btn-success"/>
<input type="submit" name="lanorg-delete-match" value="Cancel match" class="btn btn-danger"/>
</p>
<?php
$tournament_page_size = count($rounds) * 157 + 20;
?>
<div class="tournament-brackets">
<div style="width: <?php echo $tournament_page_size; ?>px;">

<?php
$round_index = 0;
$new_height = 75;
$first_margin_top = 0;

foreach ($rounds as $matches) {
?>
<ul class="ttn-round">
<?php
// Number of competitors playing this round
//$nb_competitors = pow(2, floor(log(count($teams), 2)));

// Number of competitors who doesn't play in this round but are not eliminated
//$nb_standby_competitors = count($teams);

$y = 0;

$y += $first_margin_top;

$first = TRUE;
$height = pow(2, $round_index) * 50 + 25;

$last_y = 0;
foreach ($matches as $match) {

if ($match && $match['team1'] && $match['team2']) {
$margin_top = $y - $last_y;
$y += $height;

$last_y = $y;
$css_classes1 = array('ttn-top');
$css_classes2 = array('ttn-bottom');

if (isset($match['result'])) {
	$result = $match['result'];
	if ($result['winner'] == 2) {
		array_push($css_classes1, 'ttn-lost');
	}
	else {
		array_push($css_classes2, 'ttn-lost');
	}
}
?>
<li class="ttn-leaf" style="height: <?php echo $height ?>px; margin-top: <?php echo $margin_top ?>px;">
<div class="<?php echo implode(' ', $css_classes1); ?>">
<!--select class="ttn-player">
<?php foreach ($teams as $t) { if ($t) { ?>
<option value="1"><?php echo htmlentities($t[0], NULL, 'UTF-8'); ?></option>
<option value="1"><?php echo htmlentities($t[1], NULL, 'UTF-8'); ?></option>
<?php } } ?>
</select-->
<a href="#"><?php echo htmlentities($match['team1']->name, NULL, 'UTF-8'); ?></a>
<input type="radio" class="ttn-select" name="lanorg-match"
	value="<?php echo $match['unique_id1']; ?>"/>
</div>
<div class="versus">VS</div>
<div class="<?php echo implode(' ', $css_classes2); ?>">
<div class="ttn-content">
<!--select class="ttn-player">
<option value="1">Salade</option>
</select-->
<a href="#"><?php echo htmlentities($match['team2']->name, NULL, 'UTF-8'); ?></a>
<input type="radio" class="ttn-select" name="lanorg-match"
	value="<?php echo $match['unique_id2']; ?>"/></div>
</div>
</li>

<?php

$y += ($height - 50);
}
else {
$y += pow(2, $round_index) * 100;
}


} // END foreach ($match as $matches) ?>
</ul>
<?php

$first_margin_top += $height / 2 - 12.5;
$round_index++;
} // END foreach ($rounds as $matches)
?>
<div style="clear: left;"></div>
</form>
</div>
</div>