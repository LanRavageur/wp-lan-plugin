<?php
global $lanOrg, $content_template;

$user = wp_get_current_user();

?>
<div class="lanorg-2col-left">
<ul class="lanorg-menu">
<li class="lanorg-menu-title">
<a href="<?php echo lanorg_get_user_profile_url(); ?>"><?php echo htmlentities($user->user_login, NULL, 'UTF-8'); ?></a>
</li>
<li>
<a href="<?php echo lanorg_get_registration_url(); ?>"><?php _e('Participate', 'lanorg'); ?></a>
</li>
<li>
<a href="<?php echo lanorg_get_team_url(); ?>"><?php _e('Teams', 'lanorg'); ?></a>
</li>
<li>
<a href="<?php echo lanorg_get_tournament_url(); ?>"><?php _e('Tournaments', 'lanorg'); ?></a>
</li>
<li>
<a href="<?php echo wp_logout_url( home_url() ); ?>" title="Logout"><?php _e('Logout', 'lanorg'); ?></a>
</li>
</ul>
</div>
<div class="lanorg-2col-right">
<?php
include($content_template);
?>
</div>