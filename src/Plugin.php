<?php declare( strict_types=1 );
/**
 * Include Fussball.de Widgets
 *
 * @package   ITSB\IncludeFussballDeWidgets
 * @author    IT Service Böhm -- Alexander Böhm <ab@its-boehm.de>
 * @license   GPL2
 * @link      https://wordpress.org/plugins/include-fussball-de-widgets/
 * @copyright 2019 IT Service Böhm -- Alexander Böhm
 */

namespace ITSB\IFDW;

use ITSB\IFDW\Backend\BorlabsCookie;
use ITSB\IFDW\Blocks\Enqueue as BlockEnqueue;
use ITSB\IFDW\Frontend\Enqueue as FrontendEnqueue;
use ITSB\IFDW\Shortcodes\Fubade;
use ITSB\IFDW\Utils\{CheckHelper, PluginActions, Settings, Textdomain};
use ITSB\IFDW\Widgets\Widgets;

/**
 * The `Plugin` class is the composition root of the plugin.
 *
 * In here we assemble our infrastructure, configure it for the specific use
 * case the plugin is meant to solve and then kick off the services so that
 * they can hook themselves into the WordPress lifecycle.
 *
 * @since 3.1
 */
final class Plugin {
	/**
	 * Generate the plugin instance.
	 *
	 * @since 3.1
	 */
	public function __construct() {
		// Check the needed PHP version.
		CheckHelper::compareWpVersion( Settings::MIN_PHP );

		// Configure the one time set settings.
		$this->configureSettings();

		// Add the hooks.
		$this->addAdminActions();
		$this->addActions();
		$this->addFilter();
		$this->addShortcode();
	}

	/**
	 * Configure the one time set settings.
	 *
	 * @since 3.1
	 * @return void
	 */
	private function configureSettings(): void {
		$url = esc_url_raw( wp_unslash( $_SERVER['HTTP_HOST'] ?? '' ) );
		Settings::setHost( substr( $url, strpos( $url, ':' ) + 3 ) );
	}

	/**
	 * Adds all admin action hooks.
	 *
	 * @since 3.1
	 * @return void
	 */
	private function addAdminActions() {
		( new BorlabsCookie() )->addAction( 'admin_init' );
	}

	/**
	 * Adds all action hooks.
	 *
	 * @since 3.1
	 * @return void
	 */
	private function addActions() {
		( new BlockEnqueue() )->addAction( 'init' );
		( new FrontendEnqueue() )->addAction( 'init' );
		( new Textdomain() )->addAction( 'plugins_loaded' );
		( new Widgets() )->addAction( 'widgets_init' );
	}

	/**
	 * Adds all filter hooks.
	 *
	 * @since 3.1
	 * @return void
	 */
	private function addFilter() {
		( new PluginActions() )->addFilter( 'plugin_row_meta' );
	}

	/**
	 * Adds all shortcode hooks.
	 *
	 * @since 3.1
	 * @return void
	 */
	private function addShortcode() {
		( new Fubade() )->addShortcode( 'fubade' );
	}
}
