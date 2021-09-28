<?php
/**
 * Plugin Name:       W4OS - OpenSimulator Web Interface
 * Description:       WordPress interface for OpenSimulator (w4os).
 * Version:           2.0.5
 * Author:            Speculoos World
 * Author URI:        https://speculoos.world
 * Plugin URI:        https://github.com/GuduleLapointe/w4os/
 * License:           AGPLv3
 * License URI:       https://www.gnu.org/licenses/agpl-3.0.txt
 * Text Domain:       w4os
 * Domain Path:       /languages/
 *
 * @package	w4os
 *
 * Icon1x: https://github.com/GuduleLapointe/w4os/raw/master/assets/icon-128x128.png
 * Icon2x: https://github.com/GuduleLapointe/w4os/raw/master/assets/icon-256x256.png
 * BannerHigh: https://github.com/GuduleLapointe/w4os/raw/master/assets/banner-1544x500.jpg
 * BannerLow: https://github.com/GuduleLapointe/w4os/raw/master/assets/banner-772x250.jpg
 *
 * Contributing: If you improve this software, please give back to the
 * community, by submitting your changes on the git repository or sending them
 * to the authors. That's one of the meanings of Affero GPL!
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$plugin_dir_check = basename(dirname(__FILE__));
if ( $plugin_dir_check != 'w4os-opensimulator-web-interface' && in_array( 'w4os-opensimulator-web-interface/w4os.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	add_action( 'admin_notices', function() {
		echo "<div class='notice notice-error'>";
		echo "<p><strong>W4OS:</strong> You already have installed the official release of <strong>W4OS - OpenSimulator Web Interface</strong>, from WordPress plugins directory. The developer version has been deactivated and should be uninstalled.</p>";
		echo "</div>";
	} );
	deactivate_plugins($plugin_dir_check . "/" . basename(__FILE__));
} else if ( ! defined( 'W4OS_SLUG' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/init.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/shortcodes.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/woocommerce-fix.php';
	if(W4OS_DB_CONNECTED) {
		// if($pagenow == "profile.php" || $pagenow == "user-edit.php")
		require_once plugin_dir_path( __FILE__ ) . 'includes/profile.php';
	}

	if(is_admin()) {
		require_once (plugin_dir_path(__FILE__) . 'admin/settings.php');
		if($pagenow == "index.php")
		require_once (plugin_dir_path(__FILE__) . 'admin/dashboard.php');
	}

	if(file_exists(plugin_dir_path( __FILE__ ) . 'updates.php'))
	include_once plugin_dir_path( __FILE__ ) . 'updates.php';
}
