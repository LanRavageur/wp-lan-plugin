<?php
global $lanOrg, $page_title;

$lanOrg->require_login();

$page_title = __('Admission', 'lanorg');

?>
<h1 style="clear: none;" class="entry-title"><?php _e('Confirm check in', 'lanorg'); ?></h1>
<p><?php _e('Choose the event you wish to participate', 'lanorg'); ?></p>
<form class="lanorg-form" method="POST">
<?php echo lanorg_get_registration_form_markup(); ?>
<p>
<input type="submit" class="lanorg-button" value="<?php _e('Register', 'lanorg'); ?>" />
</p>
</form>