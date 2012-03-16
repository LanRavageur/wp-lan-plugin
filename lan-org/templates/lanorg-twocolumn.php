<?php
global $lanOrg, $content_template;

$user = wp_get_current_user();

?>
<div class="lanorg-2col-left">
<h3><?php echo htmlentities($user->user_login, NULL, 'UTF-8'); ?></h3>
<ul>
<li>
<a href="#">S'inscrire</a>
</li>
</ul>
</div>
<div class="lanorg-2col-right">
<?php
include($content_template);
?>
</div>