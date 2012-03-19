<?php

// Plugin Name: LAN Party Organization
// Plugin URI:  http://lanravageur.github.com/wp-lan-plugin
// Description: Wordpress LAN plugin is used to organize LAN Party.
// Version:     0.0
// Text Domain: lanorg
// Domain Path: /lan-org/

define('LANORG_TEMPLATE_DIR_NAME', 'templates');

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}


if (!class_exists('lanOrg')) :

require('lanorg-form.php');
require('lanorg-account.php');
require('lanorg-registration.php');
require('lanorg-admin.php');
require('lanorg-contactMethods.php');
require('lanorg-profile.php');

// Main Object
class LanOrg {
	public $form_prefix = 'lanorg-';

	// Store the content for custom page
	public $page_content = '';
	public $page_title = '';

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

		// path to templates directory
		$this->template_dir = $this->plugin_dir . LANORG_TEMPLATE_DIR_NAME;
	}

	// Includes plugin files
	private function includes() {
	}

	// Set-up plugin actions
	private function setup_actions() {
		register_activation_hook(__FILE__, array($this, 'activate'));
		register_deactivation_hook(__FILE__, array($this, 'deactivate'));

		add_action('init', array($this, 'setup_rewrite_tags'));
		add_action('init', array($this, 'setup_post_types'));

		// Adds Translation support
		load_plugin_textdomain( 'lanorg', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		add_action('template_redirect', array($this, 'redirect_template'));

		add_shortcode('lanorg-register', 'lanorg_shortcode_registration_form');

		add_action('generate_rewrite_rules', array($this, 'add_rewrite_rules'));
		add_action('admin_menu', array($this, 'add_admin_menus'));
		add_filter('query_vars', array($this, 'get_query_vars'));
	}

	function load_static_files() {
		wp_register_style('lanorg-form', plugins_url('css/form.css', __FILE__));
		wp_register_style('lanorg-style', plugins_url('css/style.css', __FILE__));

	}

	function setup_rewrite_tags() {
		add_rewrite_tag('%lanorg%','([^&]+)');
	}

	function add_admin_menus() {
		add_menu_page('LAN Organization', 'LAN Organization', 'manage_options',
			'lanorg');

		add_submenu_page('lanorg', 'Configuration', 'Configuration', 'manage_options',
			'lanorg', 'lanorg_admin_settings');

		add_submenu_page('lanorg', 'Events', 'Events', 'manage_options',
			'lanorg-events', 'lanorg_admin_events');

		add_submenu_page('lanorg', 'Tournaments', 'Tournaments', 'manage_options',
			'lanorg-tournaments', 'lanorg_admin_tournaments');
	}

	function setup_post_types() {
	}

	function get_query_vars($query_vars) {
		$query_vars[] = 'lanorg_page';
		$query_vars[] = 'user_id';
		return $query_vars;
	}

	public function add_rewrite_rules($wp_rewrite) {
		$new_rules = array(
			'login/?$' => 'index.php?lanorg_page=login',
			'registration/?$' => 'index.php?lanorg_page=registration',
			'live/?$' => 'index.php?lanorg_page=live',
			'profile/?$' => 'index.php?lanorg_page=profile',
			'profile/?([0-9]{1,})/?$' => 'index.php?lanorg_page=profile&user_id=' . $wp_rewrite->preg_index(1),
		);
		$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
	}

	// Plugin activation code
	public function activate() {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		global $wpdb;

		$table_name = $wpdb->prefix . 'lanorg_events';
		$stmt =
"CREATE TABLE $table_name (
  id SMALLINT(5) NOT NULL AUTO_INCREMENT,
	title TINYTEXT NOT NULL,
	date DATE NOT NULL,
	location TINYTEXT NOT NULL,
	UNIQUE KEY id (id)
);";
		dbDelta($stmt);

		$table_name = $wpdb->prefix . 'lanorg_tournaments';
		$stmt =
"CREATE TABLE $table_name (
  id SMALLINT(5) NOT NULL AUTO_INCREMENT,
  event_id SMALLINT(5) NOT NULL,
	game TINYTEXT NOT NULL,
	publisher TINYTEXT NOT NULL,
	platform TINYTEXT NOT NULL,
	allow_teams TINYINT(1) NOT NULL,
	UNIQUE KEY id (id)
);";
		dbDelta($stmt);


	}

	// Called when the plugin is desactivated
	public function deactivate() {
	}

	public function redirect_template() {
		global $wp_query;

		$this->load_static_files();

		if (isset($wp_query->query_vars['lanorg_page']))
		{
			switch ($wp_query->query_vars['lanorg_page']) {
			case 'registration':
				$this->render_two_column_page('lanorg-registration.php');
				break ;
			case 'live':
				$this->render_two_column_page('lanorg-live.php');
				break ;
			case 'login':
				$this->render_custom_page('lanorg-login.php');
				break ;
			case 'profile':
				$user_id = isset($wp_query->query_vars['user_id']) ? $wp_query->query_vars['user_id'] : 0;
				lanorg_profile_page($user_id);
				break ;
			}
		}

		lanorg_process_registration_form();
	}

	// Lookup first in the theme directory for $template_file, if not found then
	// fallback to the template directory in plugin directory.
	public function resolve_template_file($template_file) {
		$template_file_path = TEMPLATEPATH . '/' . $template_file;

		if (!file_exists($template_file_path)) {
			$template_file_path = $this->template_dir . '/' . $template_file;
		}
		return $template_file_path;
	}

	// Render a given template, overwriting current template
	public function render_template($template_file) {
		$template_file_path = $this->resolve_template_file($template_file);
		include($template_file_path);
	}

	public function get_custom_page_title($title, $sep=' | ', $seplocation='') {
		return $this->page_title . $sep;
	}

	public function get_custom_page_content() {
		return $this->page_content;
	}

	// Render a given page with a two column template
	public function render_two_column_page($page_file) {
		$GLOBALS['content_template'] = $this->resolve_template_file($page_file);

		wp_enqueue_style('lanorg-style');

		$this->render_custom_page('lanorg-twocolumn.php');
	}

	function get_body_class($classes='') {
		$classes[] = 'singular';
		return $classes;
	}

	// Render a given page
	public function render_custom_page($page_file) {
		global $wp_query;
		$wp_query->is_home = FALSE; // Not homepage
		$wp_query->is_single = TRUE; // Single page

		// Add our custom class to force single column
		add_filter('body_class', array($this, 'get_body_class'));

		//add_filter('the_title', array($this, 'get_custom_page_title'));
		add_filter('wp_title', array($this, get_custom_page_title));
		add_filter('the_content', array($this, 'get_custom_page_content'));

		$GLOBALS['page_title'] = ''; // This variable is set by the template

		// Turn on output buffering to capture page content
		ob_start();

		$this->render_template($page_file);

		// Get buffered content
		$this->page_content = ob_get_clean();

		$this->page_title = $GLOBALS['page_title'];

		$this->render_template('lanorg-page.php');

		exit ;
	}

	// Require the user to be logged in or redirects to the login page
	public function require_login($url = '') {
		global $wp_rewrite;

		if (!is_user_logged_in()) {
			$redirect_to = !empty($url) ? $url : $wp_rewrite->root . 'login/';
			wp_safe_redirect(home_url($redirect_to));
			exit ;
		}
	}
}

$GLOBALS['lanOrg'] = new LanOrg();

endif;

?>