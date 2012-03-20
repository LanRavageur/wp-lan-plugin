<?php
global $lanOrg, $page_title, $lan_events;

$lanOrg->require_login();

$page_title = 'Tournois';

?>
<h1 style="clear: none;" class="entry-title">Tournois</h1>
<form class="lanorg-form" method="POST">
<?php foreach ($lan_events as $event) { ?>
<h2 style="clear: none;"><?php echo htmlentities($event->title, NULL, 'UTF-8'); ?></h2>
<?php foreach ($event->tournaments as $tournament) { ?>
<h3 style="clear: none;"><?php echo htmlentities($tournament->game, NULL, 'UTF-8'); ?></h3>
<?php } ?>
<?php } ?>
</form>