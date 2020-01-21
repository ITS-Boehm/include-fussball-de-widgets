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

namespace ITSB\IFDW\Utils;

use ITSB\IFDW\Utils\Host;

/**
 * The `Settings` class hold all needed global variables and constants.
 *
 * @since 3.1
 */
class Settings {
	public const VERSION = '3.1.0';
	public const MIN_PHP = '7.2.0';
	public const PREFIX  = 'itsb.ifdw.';

	/**
	 * The name of this plugin.
	 *
	 * @since 3.1
	 * @var string
	 */
	private static $pluginName;

	/**
	 * The hostname of the WordPress running system.
	 *
	 * @since 3.1
	 * @var string
	 */
	private static $host;

	/**
	 * Get the the value of hostname.
	 *
	 * @since 3.1
	 * @return string
	 */
	public static function getHost(): string {
		return self::$host;
	}

	/**
	 * Set the the value of hostname.
	 *
	 * @since 3.1
	 *
	 * @param string $host The host.
	 *
	 * @return void
	 */
	public static function setHost( string $host ): void {
		self::$host = Host::cleanHost( $host ?? null );
	}

	/**
	 * Get the value of plugin name.
	 *
	 * @since 3.1
	 * @return string
	 */
	public static function getPluginName() {
		return self::$pluginName;
	}

	/**
	 * Set the value of plugin name.
	 *
	 * @since 3.1
	 *
	 * @param string $host The host.
	 *
	 * @return self
	 */
	public static function setPluginName( $pluginName ) {
		self::$pluginName = $pluginName;
	}
}