<?php


namespace Rocket\Footer\JS\LazyLoad;


use Rocket\Footer\JS\DOMElement;

class Tumbler extends LazyloadAbstract {
	protected $regex = '~\(\s*function\s*\(\s*d\s*,\s*s\s*,\s*id\s*\)\s*{.*\(\s*document\s*,\s*[\'"]script[\'"]\s*,\s*[\'"]tumblr-js[\'"]\s*(?:\)\);|\);)~';

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_lazyload( $content, $src ) {
		if ( preg_match( $this->regex, $content, $matches ) ) {
			$tag_content = $this->get_script_content();
			$this->lazyload_script( $tag_content, 'tumblr-share-button-widget' );
			/** @var DOMElement $tag */
			foreach ( $this->xpath->query( '//*[contains(concat(" ", normalize-space(@class), " "), " tumblr-share-button ")]' ) as $tag ) {
				$tag->setAttribute( 'data-lazy-widget', 'twitter-sdk' );
			}
		}
	}
}