<?php

global $page_title, $lanOrg;

$lanOrg->require_login();

$user = wp_get_current_user();

$page_title = 'Modifier son profile';

?>
<h1 style="clear: none;" class="entry-title">Modifier votre profile</h1>
<form class="lanorg-form" method="POST">
<?php
echo lanorg_get_profile_edit_form();
?>
<p>
<input type="submit" class="lanorg-button" value="Enregistrer" />
</p>
</form>