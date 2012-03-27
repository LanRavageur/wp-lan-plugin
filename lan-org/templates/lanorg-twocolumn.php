<?php
global $lanOrg, $content_template;

$user = wp_get_current_user();

$events = lanorg_get_events();

?>
<div class="lanorg-2col-left">
<div class="lanorg-menu-wrap">
<ul class="lanorg-menu">
<li class="lanorg-menu-title">
<a href="<?php echo lanorg_get_user_profile_url(); ?>" class="lanorg-link"><?php echo htmlentities($user->user_login, NULL, 'UTF-8'); ?></a>
</li>
<li>
<a href="<?php echo lanorg_get_registration_url(); ?>" class="lanorg-link"><?php _e('Admission', 'lanorg'); ?></a>
</li>
<li>
<a href="<?php echo wp_logout_url( home_url() ); ?>" title="Logout"><?php _e('Logout', 'lanorg'); ?></a>
</li>
</ul>
<?php foreach ($events as $event_id => $event_name) { ?>
<ul class="lanorg-menu">
<li class="lanorg-menu-title">
<a href="<?php echo lanorg_get_user_profile_url(); ?>" class="lanorg-link"><?php echo htmlentities($event_name, NULL, 'UTF-8'); ?></a>
</li>
<li>
<a href="<?php echo lanorg_get_team_url($event_id); ?>" class="lanorg-link"><?php _e('Teams', 'lanorg'); ?></a>
</li>
<li>
<a href="<?php echo lanorg_get_tournament_url($event_id); ?>" class="lanorg-link"><?php _e('Tournaments', 'lanorg'); ?></a>
</li>
</ul>
<?php } ?>
</div>
</div>
<div class="lanorg-2col-right">
<?php
include($content_template);
?>
</div>