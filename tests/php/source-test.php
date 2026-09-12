<?php
/**
 * Run with: php tests/php/source-test.php
 */

define( 'ABSPATH', __DIR__ );
function __( $text ) { return $text; }
function add_action() {}
function add_filter() {}
function register_block_bindings_source() {}
function wp_enqueue_script() {}
function plugins_url() {}
function wp_rand( $min, $max ) { return $min; }
function get_bloginfo() { return 'UTF-8'; }

require dirname( __DIR__, 2 ) . '/awesome-random-text.php';

/**
 * Fail even when PHP has compiled out native assertions.
 *
 * @param bool   $condition Assertion condition.
 * @param string $message   Failure message.
 */
function awesome_random_text_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

awesome_random_text_assert(
	null === awesome_random_text_get_value( array( 'choices' => array( '', '   ' ) ) ),
	'Blank choices must return null.'
);
awesome_random_text_assert(
	'0' === awesome_random_text_get_value( array( 'choices' => array( '0' ) ) ),
	'The zero string must remain a valid choice.'
);
awesome_random_text_assert(
	'&lt;b&gt;safe&lt;/b&gt;' === awesome_random_text_get_value( array( 'choices' => array( '<b>safe</b>' ) ) ),
	'HTML choices must be escaped.'
);
awesome_random_text_assert(
	'&amp;lt;safe&amp;gt;' === awesome_random_text_get_value( array( 'choices' => array( '&lt;safe&gt;' ) ) ),
	'Literal entities must remain literal text.'
);
awesome_random_text_assert(
	null === awesome_random_text_get_value( array( 'choices' => array( "\xC2\xA0", "\xEF\xBB\xBF" ) ) ),
	'Non-breaking spaces and byte order marks must count as blank.'
);
awesome_random_text_assert(
	'0' === awesome_random_text_get_value( array( 'choices' => array( "\xC2\xA0", '0', "\xEF\xBB\xBF" ) ) ),
	'Blank Unicode choices must not hide a valid zero string.'
);
awesome_random_text_assert(
	"\xC2\x85" === awesome_random_text_get_value( array( 'choices' => array( "\xC2\x85" ) ) ),
	'The next-line character must remain a valid choice, matching JavaScript trim.'
);
awesome_random_text_assert(
	array( 'content' ) === awesome_random_text_supported_attributes( array(), 'core/verse' ),
	'Verse must support content bindings.'
);
