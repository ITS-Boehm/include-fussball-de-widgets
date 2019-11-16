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
namespace IFDW\Utils\Logging;

defined( 'ABSPATH' ) || exit;

/**
 * Class SourceLogger
 * Logs information over the used system and plugin to the browser console.
 *
 * @since 3.0
 */
class SourceLogger extends Base {
	/**
	 * The instance.
	 *
	 * @var $instance
	 */
	private static $instance = null;

	/**
	 * True if the general informations are already logged.
	 *
	 * @var $instance
	 */
	private $isGeneralLogged = false;

	// phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
	/**
	 * Logger constructor.
	 *
	 * @since 3.0
	 */
	private function __construct() {
		parent::__construct();
	}
	// phpcs:enable

	/**
	 * Get the instance.
	 *
	 * @return self
	 * @since 3.0
	 */
	public static function getInstance(): self {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Generates a logging output.
	 *
	 * @param array $arr The arguments.
	 *
	 * @since 3.0
	 */
	public function log( array $arr ): void {
		if ( ! $this->isGeneralLogged ) {
			$this->logGeneralInfo();
		}

		$this->logWidgetInfo( $arr );
	}

	/**
	 * Logs the general information, for example from the plugin, WordPress and / or the server.
	 *
	 * @since 3.0
	 */
	protected function logGeneralInfo(): void {
		$message = '<!-- ' . PHP_EOL;

		foreach ( $this->generalInfoList as $item ) {
			$message .= $item . PHP_EOL;
		};

		$message .= ' -->' . PHP_EOL;

		print esc_html( $message );

		$this->isGeneralLogged = true;
	}

	/**
	 * Logs the information pertaining to a specific widget only.
	 *
	 * @param array $arr The arguments.
	 *
	 * @since 3.0
	 */
	protected function logWidgetInfo( array $arr ): void {
		if ( ! isset( $arr['id'] ) ) {
			return;
		}

		$message = '<!-- ' . PHP_EOL;

		foreach ( $arr as $key => $value ) {
			if ( 'id' === $key ) {
				continue;
			}

			$temp     = esc_html( $key ) . __( ': ', 'include-fussball-de-widgets' ) . esc_html( $value );
			$message .= '[' . $arr['id'] . '] ' . $temp . PHP_EOL;
		}

		$message .= ' -->' . PHP_EOL;

		print esc_html( $message );
	}
}
