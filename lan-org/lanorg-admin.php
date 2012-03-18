<?php

// Display admin tabs
function lanorg_get_admin_tabs($current) {
	$tabs = array(
		'lanorg' => __('Configuration'),
		'lanorg-tournaments' => __('Tournaments')
	);

	echo '<h2 class="nav-tab-wrapper">';
	foreach ($tabs as $tab => $label) {
		$css_class = ($tab == $current) ? ' nav-tab-active' : '';
		echo "<a class='nav-tab$css_class' href='?page=$tab'>$label</a>";
	}
	echo '</h2>';
}

function lanorg_admin_settings() {
	lanorg_get_admin_tabs('lanorg');
}

function lanorg_admin_tournaments() {
	lanorg_get_admin_tabs('lanorg-tournaments');
}

?>