<form class="lanorg-form" method="POST">
<?php
global $lanOrg;
$error = NULL;
echo lanorg_get_login_form_markup($error);
?>
<?php if ($error) { ?>
<p><?php echo htmlentities($error, NULL, 'UTF-8'); ?></p>
<?php } ?>
<p>
<input type="submit" class="lanorg-button" value="Connexion" />
</p>
</form>