<?php
global $lanOrg, $page_title, $tournaments;

$lanOrg->require_login();

$page_title = __('Teams', 'lanorg');

?>
<h2 style="clear: none;"><?php echo htmlentities($event->title, NULL, 'UTF-8'); ?></h2>
<?php foreach ($tournaments as $tournament) { ?>
<h3 style="clear: none;"><?php echo htmlentities($tournament->game, NULL, 'UTF-8'); ?></h3>
<form class="lanorg-form" method="POST">
<?php echo lanorg_get_create_team_form_markup(); ?>
<input type="hidden" name="lanorg-tournament-id" value="<?php echo $tournament->id ?>">
<input type="submit" value="Créer une équipe" class="btn btn-primary" />
</form>

<?php foreach ($tournament->teams as $team) { ?>

<form class="lanorg-form" method="POST">
<?php echo lanorg_display_team($team, $tournament); ?>
</form>

<?php } ?>

<div style="clear: both;"></div>
<?php } ?>
