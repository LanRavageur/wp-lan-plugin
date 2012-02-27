<?php

// Plugin Name: LAN Party Organization
// Plugin URI:  http://lanravageur.github.com/wp-lan-plugin
// Description: Wordpress LAN plugin is used to organize LAN Party.
// Version:     0.0
// Text Domain: lanorg
// Domain Path: /lan-org/

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('lanOrg')) :

// Main Object
class lanOrg {

	// Setup the LAN Party Organization plugin
	public function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->setup_actions();
	}

	private function setup_globals() {

		// Main plugin file
		$this->file	= __FILE__;

		// Gets the plugin name based from the file path (e.g., lan-org/lan-org.php)
		$this->basename = plugin_basename($this->file);

		// Plugin directory (e.g., /wwwroot/wp/wp-content/plugins/lan-org/)
		$this->plugin_dir = plugin_dir_path($this->file);

		// Absolute plugin URL (e.g., http://127.0.0.1/wp/wp-content/plugins/lan-org/)
		$this->plugin_url = plugin_dir_url($this->file);
	}

	// Includes plugin files
	private function includes() {
		// require(...)
	}

	// Set-up plguin actions
	private function setup_actions() {
		register_activation_hook(__FILE__, array($this, 'activate'));
		register_deactivation_hook(__FILE__, array($this, 'deactivate'));

	}

	// Called when the plugin is activated
	public function activate() {
	}

	// Called when the plugin is desactivated
	public function deactivate() {
	}
}

$GLOBALS['lanOrg'] = new lanOrg();

endif;
