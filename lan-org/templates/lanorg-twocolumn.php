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
<a href="#">Confirmer sa présence</a>
</li>
<li>
<a href="#">Équipes</a>
</li>
<li>
<a href="#">Tournois</a>
</li>
<li>
<a href="#">Déconnexion</a>
</li>
</ul>
</div>
<div class="lanorg-2col-right">
<?php
include($content_template);
?>
</div>