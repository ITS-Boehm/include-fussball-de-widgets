<?php declare( strict_types=1 );
/**
 * Include Fussball.de Widgets
 *
 * @package   ITSB\IncludeFussballDeWidgets
 * @author    Alexander Böhm <ab@its-boehm.de>
 * @license   GPL2
 * @link      https://wordpress.org/plugins/include-fussball-de-widgets/
 * @copyright 2019 Alexander Böhm
 */

namespace ITSB\IFDW\Frontend;

use ITSB\IFDW\Utils\Logging\ConsoleLogger;
use ITSB\IFDW\Utils\Settings;
use ITSB\IFDW\Utils\StringHelper;

/**
 * The `Fubade` class used to create the output of the widget
 * from `fussball.de`.
 *
 * @since 3.0
 */
final class Fubade {
	private const ERROR = [
		'API_LENGTH' => 'api-length',
		'HTTP_HOST'  => 'no-server-name',
	];

	/**
	 * The attributes (`api`, `id`, `notice`, `fullwidth` and `devtools`).
	 *
	 * @since 3.0
	 * @var array
	 */
	private $attr = [];

	/**
	 * Creates the output to the sourcecode.
	 *
	 * @since 3.0
	 *
	 * @param array $attr The output attributes (`api`, `id`, `notice`,
	 *                    `fullwidth` and `devtools`).
	 * @return string The output to the sourcecode.
	 */
	public function output( array $attr ): string {
		// TODO [#33]: Configure default setting in the admin area.
		$this->setAttr( $attr );

		$this->attr = [
			'api'       => sanitize_text_field( strtoupper( preg_replace( '/[^\w]/', '', $this->attr['api'] ) ) ),
			'id'        => StringHelper::startsWith( $this->attr['id'], 'fubade-' )
										? sanitize_text_field( $this->attr['id'] )
										: 'fubade-' . random_int( 10, 99 ) . '-' . substr( $this->attr['api'], -5 ),
			'notice'    => empty( $this->attr['notice'] ) ? '' : sanitize_text_field( $this->attr['notice'] ),
			'fullwidth' => '1' === $this->attr['fullwidth']
										|| 'true' === $this->attr['fullwidth']
										|| true === $this->attr['fullwidth']
										? true : false,
			'devtools'  => '1' === $this->attr['devtools']
										|| 'true' === $this->attr['devtools']
										|| true === $this->attr['devtools']
										? true : false,
		];

		if ( ! wp_script_is( 'fubade-api' ) ) {
			wp_enqueue_script( 'fubade-api' );
		}

		if ( $this->attr['devtools'] ) {
			wp_localize_script( 'fubade-api', 'attr', [ 'devtools' => $this->attr['devtools'] ] );
		}

		wp_add_inline_script( 'fubade-api', 'new FussballdeWidgetAPI();', 'after' );

		if ( strlen( $this->attr['api'] ) !== 32 ) {
			ConsoleLogger::getInstance()->log( $this->attr );
			return $this->render( self::ERROR['API_LENGTH'] );
		}

		if ( strtolower( Settings::getHost() ) === strtolower( Settings::SERVER_NAME_DUMMY ) ) {
			ConsoleLogger::getInstance()->log( $this->attr );
			return $this->render( self::ERROR['HTTP_HOST'] );
		}

		return $this->render( null );
	}

	/**
	 * Set the attribute array.
	 *
	 * @since 3.0
	 * @param array $attr The attributes (`api`, `id`, `notice`, `fullwidth`
	 *                    and `devtools`) for the widget rendering.
	 * @return void
	 */
	public function setAttr( array $attr ): void {
		$this->attr = [
			'api'       => $attr['api'] ?? '',
			'id'        => $attr['id'] ?? 'ERROR_' . time(),
			'notice'    => $attr['notice'] ?? '',
			'fullwidth' => $attr['fullwidth'] ?? false,
			'devtools'  => $attr['devtools'] ?? false,
		];
	}

	/**
	 * Render all the output.
	 *
	 * @since 3.0
	 * @param string|null $error Potential errors.
	 * @return string The rendered the output.
	 */
	private function render( ?string $error ): string {
		$divAttributeString = 'id="' . esc_html( $this->attr['id'] ) . '" class="include-fussball-de-widgets"';

		if ( $error ) {
			$divAttributeString .=
			' style="padding:1rem;background-color:#f2dede;color:#a94442;border:1px solid #ebccd1;border-radius:4px"';

			switch ( $error ) {
				case self::ERROR['API_LENGTH']:
					$divContent  = __(
						'!!! The fussball.de API must have a length of exactly 32 characters. !!!',
						'include-fussball-de-widgets'
					) . PHP_EOL;
					$divContent .= sprintf( /* translators: %s: The length of the api. */
						esc_html__( 'Currently the API length is: %s', 'include-fussball-de-widgets' ),
						esc_html( strlen( $this->attr['api'] ) )
					) . PHP_EOL;
					break;
				case self::ERROR['HTTP_HOST']:
					$divContent = __(
						'The PHP variable <code>$_SERVER["HTTP_HOST"]</code> was not set by the server.',
						'include-fussball-de-widgets'
					) . PHP_EOL;
					break;
				default:
					$divContent = __( 'An undefined error has occurred.', 'include-fussball-de-widgets' ) . PHP_EOL;
			}
		} else {
			$divContent = $this->createIframe();
		}

		$output  = "<div $divAttributeString>" . PHP_EOL;
		$output .= $divContent;
		$output .= '</div>' . PHP_EOL;

		if ( $this->attr['devtools'] ) {
			ConsoleLogger::getInstance()->log( $this->attr );
		}

		return $output;
	}

	/**
	 * Creates the iframe needed from fussball.de.
	 *
	 * @since 3.0
	 * @return string The iframe.
	 */
	private function createIframe(): string {
		$src    = '//www.fussball.de/widget2/-/schluessel/' . $this->attr['api'];
		$src   .= '/target/' . $this->attr['id'];
		$src   .= '/caller/' . Settings::getHost();
		$width  = $this->attr['fullwidth'] ? '100%' : '900px';
		$height = '200px';
		$style  = 'border: 1px solid #CECECE; overflow: hidden';

		return "<iframe src='$src' width='$width' height='$height' scrolling='no' style='$style'></iframe>" . PHP_EOL;
	}
}
