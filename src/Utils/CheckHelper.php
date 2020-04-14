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

/**
 * The `CheckHelper` class hold some global checks.
 *
 * Global test methods are defined here. These should help to make the right
 * decisions in the right moments.
 *
 * @since 3.1
 */
final class CheckHelper {
	/**
	 * The minimum WP version.
	 *
	 * @since 3.2
	 * @var string
	 */
	private static $wpVersion;

	/**
	 * The minimum PHP version.
	 *
	 * @since 3.1
	 * @var string
	 */
	private static $phpVersion;

	/**
	 * Checks the needed WordPress and PHP version is used.
	 *
	 * @since 3.2
	 * @param string $wpVersion     The minimum WordPress version.
	 * @param string $phpVersion    The minimum PHP version.
	 * @return boolean              True, if the minimum WordPress and
	 *                              PHP version is used; otherwise false.
	 */
	public static function versionsInvalid( string $wpVersion, string $phpVersion ): bool {
		global $wp_version;
		self::$wpVersion  = $wpVersion;
		self::$phpVersion = $phpVersion;

		if ( version_compare( phpversion(), self::$phpVersion, '<' )
			&& version_compare( $wp_version, self::$wpVersion, '<' ) ) {
				return false;
		}

		return true;
	}

	/**
	 * The callback creates the admin notice if the PHP version is not compatible.
	 *
	 * @since 3.1
	 * @return void
	 */
	public static function createNotice() {
		echo '<div class="notice notice-error"><p>';
		printf(
			/* phpcs:disable Generic.Files.LineLength */
			/* translators: %1$s: The required WP version - %2$s: The required PHP version */
			esc_html__( 'Include Fussball.de Widgets requires WP %1$s and PHP %2$s or higher.', 'include-fussball-de-widgets' ),
			esc_html( self::$wpVersion ),
			esc_html( self::$phpVersion )
			/* phpcs:enable */
		);
		echo '</p></div>';
	}
}
