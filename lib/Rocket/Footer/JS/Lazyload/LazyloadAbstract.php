<?php


namespace Rocket\Footer\JS\Lazyload;


use Rocket\Footer\JS\DOMCollection;
use Rocket\Footer\JS\DOMDocument;
use Rocket\Footer\JS\DOMElement;
use Rocket\Footer\JS\TagHelperTrait;

/**
 * Class LazyloadAbstract
 *
 * @package Rocket\Footer\JS\Lazyload
 */
abstract class LazyloadAbstract implements LazyloadInterface {
	use TagHelperTrait;
	/**
	 * @var DOMCollection
	 */
	protected $tags;
	/**
	 * @var \Rocket\Footer\JS\DOMDocument
	 */
	protected $document;
	/**
	 * @var \Rocket\Footer\JS\DOMDocument
	 */
	protected $content_document;
	/**
	 * @var \DOMXPath
	 */
	protected $xpath;

	/**
	 * @var int
	 */
	protected $instance = 0;

	protected $regex;

	/**
	 *
	 */
	public function init() {
		add_action( 'rocket_footer_js_do_lazyload', [ $this, 'lazyload' ] );
	}

	/**
	 * @param DOMDocument $document
	 * @param DOMDocument $content_document
	 */
	public function lazyload( $document = null, $content_document = null ) {
		if ( ! $document ) {
			/** @noinspection CallableParameterUseCaseInTypeContextInspection */
			$document = rocket_footer_js()->get_document();
		}
		if ( ! $content_document ) {
			/** @noinspection CallableParameterUseCaseInTypeContextInspection */
			$content_document = $document;
		}
		$this->document         = $document;
		$this->content_document = $content_document;
		$this->tags             = $this->get_script_collection();
		$this->xpath            = new \DOMXPath( $content_document );
		$this->before_do_lazyload();
		while ( $this->tags->valid() ) {
			$tag = $this->tags->current();
			$src = $tag->getAttribute( 'src' );
			if ( ! empty( $src ) ) {
				$src = rocket_add_url_protocol( $src );
			}
			$content = str_replace( [ "\n", "\r" ], '', $tag->textContent );
			$content = trim( $content, '/' );
			if ( ! $this->is_enabled() || $this->is_no_lazyload() ) {
				$this->do_lazyload_off( $content, $src );
				$this->tags->next();
				continue;
			}
			$this->do_lazyload( $content, $src );
			$this->tags->next();
		}
		$this->after_do_lazyload();
	}

	/**
	 *
	 */
	protected function before_do_lazyload() {

	}

	/**
	 * @return bool
	 */
	protected function is_enabled() {
		return rocket_footer_js()->get_lazyload_manager()->is_enabled();
	}

	/**
	 * @param string $content
	 * @param string $src
	 *
	 * @return void
	 */
	protected function do_lazyload_off( $content, $src ) {
		if ( ( empty( $this->regex ) && ! $this->is_no_minify() ) || ( ! empty( $this->regex ) && preg_match( $this->regex, $content ) ) ) {
			$this->set_no_minify();
		}
	}

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	abstract protected function do_lazyload( $content, $src );

	protected function after_do_lazyload() {

	}

	protected function lazyload_script( $html, $id, $tag = null ) {
		/** @var DOMElement $external_tag */
		$collection = false;
		if ( ! $tag ) {
			/** @noinspection CallableParameterUseCaseInTypeContextInspection */
			$tag        = $this->tags->current();
			$collection = true;
		}
		if ( get_rocket_option( 'minify_html' ) && ! is_rocket_post_excluded_option( 'minify_html' ) ) {
			$external_tag = $this->content_document->createElement( 'div' );
			$external_tag->appendChild( $this->content_document->createElement( 'WP_ROCKET_FOOTER_JS_LAZYLOAD_START' ) );
			$external_tag->appendChild( $this->content_document->createTextNode( $html ) );
			$external_tag->appendChild( $this->content_document->createElement( 'WP_ROCKET_FOOTER_JS_LAZYLOAD_END' ) );
		} else {
			$comment_tag  = $this->content_document->createComment( $html );
			$external_tag = $this->content_document->createElement( 'div' );
			$external_tag->appendChild( $comment_tag );
		}
		$external_tag->setAttribute( 'id', $id );
		if ( $this->content_document->isSameNode( $this->document ) ) {
			$tag->parentNode->insertBefore( $external_tag, $tag );
		} else {
			$this->content_document->getElementsByTagName( 'body' )->item( 0 )->appendChild( $external_tag );
		}
		if ( $collection ) {
			$this->tags->remove();

			return;
		}
		$tag->parentNode->removeChild( $tag );
	}

	/**
	 * @return DOMElement
	 */
	protected function create_pixel_image() {
		$img = $this->create_tag( 'img' );
		$img->setAttribute( 'src', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=' );

		return $img;
	}

	/**
	 * @return DOMCollection
	 */
}