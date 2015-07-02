<?php
/*
Plugin Name: WP-DBIS
Plugin URI: #
Description: Browse DBIS (http://rzblx10.uni-regensburg.de/dbinfo/) within Wordpress
Version: 0.5
Author: Tobias Zeumer
Author URI: #
License: GPL2
*/

function shortcode_dbis( $atts ) {
	// Set Plugin URL
	$pluginUrl = WP_PLUGIN_URL . '/wp-dbis';
  //wp_enqueue_style( 'todo.css', plugin_dir_url(__FILE__ ).'/style/todo.css');
  //wp_enqueue_style( 'todo.css', $pluginUrl.'/style/todo.css');
  //wp_enqueue_script("jquery");
	//wp_enqueue_script("list_folding", "$pluginUrl/js/list_folding.js", array("jquery"));
	
  include 'inc/dbis/class.CloneDBIS.php';
  $dbis_id = get_option('dbis_id');
  $wp_dbis = new CloneDBIS();
  if ($dbis_id) $wp_dbis->dbis_id = $dbis_id;
  $wp_dbis->start_dbis();
}
add_shortcode( 'dbis', 'shortcode_dbis' );






/* What to do when the plugin is activated? */
register_activation_hook(__FILE__,'dbis_install');
function dbis_install() {
  /* Create a new database field */
  add_option("dbis_id", 'tuhh', 'DBIS ID of library', 'yes');
}


/* What to do when the plugin is deactivated? */
register_deactivation_hook( __FILE__, 'dbis_remove' );
function dbis_remove() {
  /* Delete the database field */
  delete_option('dbis_id');
}

/* Add option page for admins */
if ( is_admin() ){ // admin actions
  add_action('admin_menu', 'add_dbis_settings');
  add_action( 'admin_init', 'register_dbis_settings' );
  function add_dbis_settings() {
    add_options_page('Plugin Admin Options', 'DBIS-Einstellungen', 'manage_options',
    'wp-dbis-settings', 'plugin_admin_options_page');
  }
} else {
  // non-admin enqueues, actions, and filters
}


function register_dbis_settings() { // whitelist options
  register_setting( 'dbis_settings-group', 'dbis_id' );
}


function plugin_admin_options_page() {
?>
  <div class="wrap">
<?php screen_icon(); ?>
    <h2>DBIS-Einstellungen</h2>
    <p>
      <form method="post" action="options.php">
        <?php
          settings_fields( 'dbis_settings-group' );
          do_settings_sections( 'dbis_settings-group' );
        ?>
        DBIS-ID: <input name="dbis_id" type="text" id="dbis_id" value="<?php echo get_option('dbis_id'); ?>" />
        <?php submit_button(); ?>
      </form>
    </p>
  </div>
<?php
}
?>