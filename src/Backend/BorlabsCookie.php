<?php
/**
 * Include Fussball.de Widgets
 * Copyright (C) 2019 IT-Service Böhm - Alexander Böhm <ab@its-boehm.de>
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Include_Fussball_De_Widgets
 */

declare( strict_types=1 );
namespace IFDW\Backend;

defined( 'ABSPATH' ) || exit();

/**
 * Class BorlabsCookie provides functions to add "Borlabs-Cookie" (https://borlabs.io) support.
 *
 * @since 3.0
 */
class BorlabsCookie {
	const CB_ID = 'fubade';

	/**
	 * The instance.
	 *
	 * @since 3.0
	 * @var self
	 */
	private static $instance;

	/**
	 * The table cookies.
	 *
	 * @var string
	 */
	private $tableNameCookies;

	/**
	 * The table cookieGroups.
	 *
	 * @var string
	 */
	private $tableNameCookieGroups;

	/**
	 * BorlabsCookie constructor.
	 *
	 * @since 3.0
	 */
	private function __construct() { }

	/**
	 * Get the instance.
	 *
	 * @since 3.0
	 * @return self The instance of the class.
	 */
	public static function getInstance(): self {
		return self::$instance ?? new static();
	}

	/**
	 * Add the admin init action for creating the content Blocker.
	 *
	 * @since 3.0
	 * @return void
	 */
	public function addAdminInitAction(): void {
		add_action( 'admin_init', [ $this, 'createContentBlocker' ] );
	}

	/**
	 * Create the fubade content-blocker, if not exists already.
	 *
	 * @since 3.0
	 * @return void
	 */
	public function createContentBlocker(): void {
		if ( ! is_plugin_active( 'borlabs-cookie/borlabs-cookie.php' )
				|| BorlabsCookieHelper()->getContentBlockerData( self::CB_ID ) ) {
			return;
		}

		global $wpdb;

		$this->tableNameCookies      = $wpdb->base_prefix . 'borlabs_cookie_cookies';
		$this->tableNameCookieGroups = $wpdb->base_prefix . 'borlabs_cookie_groups';

		if ( ! $this->checkFubadeCookieExists() ) {
			$this->addCookie();
		}

		/* Setup variables */
		$cbHtml = '<div class="_brlbs-content-blocker">
	<div class="_brlbs-embed brlbs-ifdw">
		<img class="_brlbs-thumbnail" src="' .
			plugins_url( 'assets/images/cb-fubade.png', IFDW_URL ) . '" alt="%%name%%">
		<div class="_brlbs-caption">
			<p>
				' . __(
				'By loading the widget, you agree to the privacy policy of fussball.de.',
				'include-fussball-de-widgets'
			) . '<br>
				<a href="%%privacy_policy_url%%" target="_blank" rel="nofollow">' .
					__( 'Learn more', 'include-fussball-de-widgets' ) . '</a>
			</p>
			<p>
			<a class="_brlbs-btn" href="#" data-borlabs-cookie-unblock role="button">
					' . __( 'Load widget', 'include-fussball-de-widgets' ) . '
				</a>
			</p>
			<p>
				<label>
					<input type="checkbox" name="unblockAll" value="1" checked>
					<small>' . __( 'Always load fussball.de Widgets', 'include-fussball-de-widgets' ) . '</small>
				</label>
			</p>
		</div>
	</div>
</div>';

		$cbCss = '.BorlabsCookie ._brlbs-content-blocker .brlbs-ifdw ._brlbs-caption a {
	color: #aaa;
}

.BorlabsCookie ._brlbs-content-blocker .brlbs-ifdw ._brlbs-caption a._brlbs-btn {
	background: #0000a8;
	color: #fff;
	border-radius: 50px;
}

.BorlabsCookie ._brlbs-content-blocker .brlbs-ifdw ._brlbs-caption a._brlbs-btn:hover {
	background: #fff;
	color: #0000a8;
}';

		BorlabsCookieHelper()->addContentBlocker(
			self::CB_ID,
			__( 'Fussball.de Widget', 'include-fussball-de-widgets' ),
			'',
			'http://www.fussball.de/privacy/',
			[ 'fussball.de', 'www.fussball.de' ],
			$cbHtml,
			$cbCss,
			'',
			'',
			[],
			false,
			false
		);
	}

	/**
	 * Check if the `fubade` exists.
	 *
	 * @since 3.0
	 * @return bool If the fubade cookie exists it is true, otherwise false.
	 */
	private function checkFubadeCookieExists(): bool {
		global $wpdb;

		// TODO: use correct database caching.
    // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
    // phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
		$cookieId = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT `cookie_id`
        FROM `%s`
        WHERE `cookie_id` = %s
        LIMIT 1',
				$this->tableNameCookies,
				self::CB_ID
			)
		);
    // phpcs:enable

		if ( $cookieId > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Add the fubade cookie, if not exists already.
	 *
	 * @since 3.0
	 * @return void
	 */
	private function addCookie(): void {
		global $wpdb;

		$defaultBlogLanguage = substr( get_option( 'WPLANG', 'en_US' ), 0, 2 ) ?? 'en';
		$cookieGroupIds      = [];

		// TODO: use correct database caching.
    // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
    // phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
		$cookieGroups = $wpdb->get_results(
			'SELECT `id`, `group_id`
      FROM `%s`
      WHERE `language` = "' . esc_sql( $defaultBlogLanguage ) . '"',
			$this->tableNameCookieGroups
		);
    // phpcs:enable

		foreach ( $cookieGroups as $groupData ) {
			$cookieGroupIds[ $groupData->group_id ] = $groupData->id;
		}

		$sqlQuery = 'INSERT INTO `' . $this->tableNameCookies . "`
        (
          `cookie_id`,
          `language`,
          `cookie_group_id`,
          `service`,
          `name`,
          `provider`,
          `purpose`,
          `privacy_policy_url`,
          `hosts`,
          `cookie_name`,
          `cookie_expiry`,
          `opt_in_js`,
          `position`,
          `status`,
          `undeletable`
        )
        VALUES
        (
          '" . self::CB_ID . "',
          '" . esc_sql( $defaultBlogLanguage ) . "',
          '" . esc_sql( $cookieGroupIds['external-media'] ) . "',
          'Custom',
          'Fußball.de',
          'Fußball.de',
          '" . _x( 'Used to unblock Fußball.de content.', 'Cookie - Default Entry Fußball.de', 'borlabs-cookie' ) . "',
          '" . _x( 'http://www.fussball.de/privacy/', 'Cookie - Default Entry Fußball.de', 'borlabs-cookie' ) . "',
          '" . esc_sql( [ 'fussball.de', 'www.fussball.de' ] ) . "',
          '" . self::CB_ID . "',
        '" . _x( 'Unlimited', 'Cookie - Default Entry Fußball.de', 'borlabs-cookie' ) . "',
          '" . esc_sql(
			'<script>
				if("object" === typeof window.BorlabsCookie) {
					window.BorlabsCookie.unblockContentId("' . self::CB_ID . '");
				}
			</script>'
		) . "',
          82,
          1,
          0
        )
        ON DUPLICATE KEY UPDATE
            `undeletable` = VALUES(`undeletable`)
        ";

		// TODO: use correct database caching.
    // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
    // phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( '%s', $sqlQuery );
    // phpcs:enable

	}
}
