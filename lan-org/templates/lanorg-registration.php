<?php
global $lanOrg;

$lanOrg->require_login();

?>
<h1 style="clear: none;" class="entry-title">Confirmer sa présence</h1>
<p>Choisissez les événments que vous désirez participer.</p>
<form class="lanorg-form" method="POST">
<?php echo lanorg_get_registration_form_markup(); ?>
<p>
<input type="submit" class="lanorg-button" value="S'inscrire à l'événement" />
</p>
</form>