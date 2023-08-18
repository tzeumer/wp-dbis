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

/**
 * Fix 2023-08-18: Always return (otherwise breaks e.g. saving in Gutenberg); https://developer.wordpress.org/plugins/shortcodes/
 */
function shortcode_dbis( $atts ) {
	// Set Plugin URL
	$pluginUrl = WP_PLUGIN_URL . '/wp-dbis';
  //wp_enqueue_style( 'vplan-style.css', plugin_dir_url(__FILE__ ).'/scripts/vplan-style.css');
  //wp_enqueue_style( 'vplan-style.css', $pluginUrl.'/scripts/vplan-style.css');
    wp_enqueue_script("jquery");
	//wp_enqueue_script("list_folding", "$pluginUrl/js/list_folding.js", array("jquery"));

    // Parameters (or defaults) that can be used in shortcode
    $atts = shortcode_atts(
        array(
            'lng'  => 'DE',
            'sort' => 'alph',
        ), $atts, 'bartag' );
	
    include_once 'inc/dbis/class.CloneDBIS.php';
    //$dbis_id = get_option('dbis_id'); // 2016-10-18 TZ: erst mal raus
    $dbis_id = 'tuhh';
    $bla = new CloneDBIS($atts);
    if ($dbis_id) $bla->dbis_id = $dbis_id;
    return $bla->start_dbis();
}
add_shortcode( 'dbis', 'shortcode_dbis' );






/* What to do when the plugin is activated? */
register_activation_hook(__FILE__,'dbis_install');
function dbis_install() {
  // guzzle needs 5.4
//  $compatible = (PHP_MAJOR_VERSION >= 4 && PHP_MINOR_VERSION >= 3) ? true : false;

$compatible = true;
  if (!$compatible) {
    trigger_error('<p><strong>Dieses Plugin benötigt mindestens PHP 4.3, besser PHP 5.4. Ihre Version: '.PHP_VERSION."</strong></p><br>\n", E_USER_ERROR );
  }
  
  /* Create a new database field */
  add_option("dbis_id", 'Testing !! My Plugin is Working Fine.', 'This is my first plugin panel data.', 'yes');
}


/* What to do when the plugin is deactivated? */
register_deactivation_hook( __FILE__, 'dbis_remove' );
function dbis_remove() {
  /* Delete the database field */
  delete_option('dbis_id');
}

add_action('admin_menu', 'dbis_admin_menu');
function dbis_admin_menu() {
  add_options_page('Plugin Admin Options', 'DBIS-Einstellungen', 'manage_options',
  'my-first', 'plugin_admin_options_page');
}


function plugin_admin_options_page() {
?>
  <div class="wrap">
<?php screen_icon(); ?>
    <h2>DBIS-Einstellungen</h2>
    <p>
      <form method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>
        DBIS-ID: <input name="dbis_id" type="text" id="dbis_id" value="<?php echo get_option('dbis_id'); ?>" />
        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="page_options" value="tuhh" />
        <input type="submit" value="Save Changes" />
      </form>
    </p>
  </div>
<?php
}
?>