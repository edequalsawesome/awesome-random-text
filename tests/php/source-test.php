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

assert( null === awesome_random_text_get_value( array( 'choices' => array( '', '   ' ) ) ) );
assert( '0' === awesome_random_text_get_value( array( 'choices' => array( '0' ) ) ) );
assert( '&lt;b&gt;safe&lt;/b&gt;' === awesome_random_text_get_value( array( 'choices' => array( '<b>safe</b>' ) ) ) );
assert( '&amp;lt;safe&amp;gt;' === awesome_random_text_get_value( array( 'choices' => array( '&lt;safe&gt;' ) ) ) );
assert( null === awesome_random_text_get_value( array( 'choices' => array( "\xC2\xA0", "\xEF\xBB\xBF" ) ) ) );
assert( '0' === awesome_random_text_get_value( array( 'choices' => array( "\xC2\xA0", '0', "\xEF\xBB\xBF" ) ) ) );
assert( "\xC2\x85" === awesome_random_text_get_value( array( 'choices' => array( "\xC2\x85" ) ) ) );
assert(
	array( 'content' ) === awesome_random_text_supported_attributes( array(), 'core/verse' )
);
