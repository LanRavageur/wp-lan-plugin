<?php
global $lanOrg, $page_title, $lan_events;

$lanOrg->require_login();

$page_title = __('Teams', 'lanorg');

?>
<h1 style="clear: none;" class="entry-title"><?php _e('Teams', 'lanorg'); ?></h1>
<form class="lanorg-form" method="POST">
<?php foreach ($lan_events as $event) { ?>
<h2 style="clear: none;"><?php echo htmlentities($event->title, NULL, 'UTF-8'); ?></h2>
<?php foreach ($event->tournaments as $tournament) { ?>
<h3 style="clear: none;"><?php echo htmlentities($tournament->game, NULL, 'UTF-8'); ?></h3>
<form method="POST">
<?php echo lanorg_get_create_team_form_markup(); ?>
<input type="hidden" name="lanorg-tournament-id" value="<?php echo $tournament->id ?>">
<input type="submit" value="Créer une équipe" />
</form>

<?php foreach ($tournament->teams as $team) { ?>

<form method="POST">
<?php echo lanorg_display_team($team, $tournament); ?>
</form>

<?php } ?>

<div style="clear: both;"></div>
<?php } ?>

<?php } ?>
</form>