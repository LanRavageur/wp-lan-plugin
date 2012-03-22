<?php
global $lanOrg, $page_title, $lan_events;

$lanOrg->require_login();

$page_title = 'Tournois';

?>
<h1 style="clear: none;" class="entry-title">Tournois</h1>
<form class="lanorg-form" method="POST">
<ul>
<?php foreach ($lan_events as $event) { ?>
<li>
<h2 style="clear: none;"><?php echo htmlentities($event->title, NULL, 'UTF-8'); ?></h2>
<?php foreach ($event->tournaments as $tournament) { ?>
<ul>
<li><a href="<?php echo lanorg_get_tournament_url($tournament->id); ?>"><?php echo htmlentities($tournament->game, NULL, 'UTF-8'); ?></a></li>
</ul>
<p style="clear: none;"></p>
<?php } ?>
</li>
<?php } ?>
</ul>
</form>