<?php
global $lanOrg, $page_title, $tournaments, $event_id;

$lanOrg->require_login();

$page_title = 'Tournois';

?>
<h1 style="clear: none;" class="entry-title">Tournois</h1>
<ul>
<?php foreach ($tournaments as $tournament) { ?>
<li><a href="<?php echo lanorg_get_tournament_url($event_id, $tournament->id); ?>" class="lanorg-link">
<?php echo htmlentities($tournament->game, NULL, 'UTF-8'); ?></a></li>
</a>
</li>
<?php } ?>
</ul>
<p style="clear: none;"></p>
