<?php
/**
 * Plugin Name:       Awesome Random Text
 * Description:       Display a random plain-text choice in supported core text blocks.
 * Version:           0.1.0
 * Requires at least: 6.9
 * Requires PHP:      7.4
 * Author:            eD! Thomas
 * License:           GPL-3.0-or-later
 * Text Domain:       awesome-random-text
 *
 * @package AwesomeRandomText
 */

defined( 'ABSPATH' ) || exit;

define( 'AWESOME_RANDOM_TEXT_SOURCE', 'awesome-random-text/choice' );

/**
 * Checks whether a value is blank according to JavaScript String.prototype.trim().
 *
 * @param string $value Value to check.
 * @return bool
 */
function awesome_random_text_is_blank( $value ) {
	return 1 === preg_match( '/^[\x09-\x0D\x20\p{Z}\x{FEFF}]*$/u', $value );
}

/**
 * Returns one safe, non-empty choice from a binding's arguments.
 *
 * @param array<string, mixed> $source_args    Binding arguments.
 * @param WP_Block|null        $block_instance Block instance.
 * @return string|null A choice, or null to retain the saved block content.
 */
function awesome_random_text_get_value( $source_args, $block_instance = null ) {
	if (
		$block_instance &&
		'core/list-item' === $block_instance->name &&
		count( $block_instance->inner_blocks ) > 0
	) {
		return null;
	}

	if ( empty( $source_args['choices'] ) || ! is_array( $source_args['choices'] ) ) {
		return null;
	}

	$choices = array_values(
		array_filter(
			$source_args['choices'],
			static function ( $choice ) {
				return is_string( $choice ) && ! awesome_random_text_is_blank( $choice );
			}
		)
	);

	if ( empty( $choices ) ) {
		return null;
	}

	return htmlspecialchars(
		$choices[ wp_rand( 0, count( $choices ) - 1 ) ],
		ENT_QUOTES | ENT_SUBSTITUTE,
		get_bloginfo( 'charset' ),
		true
	);
}

/**
 * Registers the binding source and the editor script.
 *
 * @return void
 */
function awesome_random_text_register() {
	register_block_bindings_source(
		AWESOME_RANDOM_TEXT_SOURCE,
		array(
			'label'              => __( 'Awesome Random Text', 'awesome-random-text' ),
			'get_value_callback' => 'awesome_random_text_get_value',
		)
	);
}
add_action( 'init', 'awesome_random_text_register' );

/**
 * Enqueues the editor-only integration script.
 *
 * @return void
 */
function awesome_random_text_enqueue_editor_assets() {
	$asset = require __DIR__ . '/build/index.asset.php';

	wp_enqueue_script(
		'awesome-random-text-editor',
		plugins_url( 'build/index.js', __FILE__ ),
		$asset['dependencies'],
		$asset['version'],
		true
	);
	wp_set_script_translations(
		'awesome-random-text-editor',
		'awesome-random-text',
		__DIR__ . '/languages'
	);
}
add_action( 'enqueue_block_editor_assets', 'awesome_random_text_enqueue_editor_assets' );

/**
 * Enables bindings for core blocks whose content attributes are safe plain text targets.
 *
 * @param string[] $attributes Supported block attributes.
 * @param string   $block_type Block name.
 * @return string[]
 */
function awesome_random_text_supported_attributes( $attributes, $block_type ) {
	if ( in_array( $block_type, array( 'core/list-item', 'core/verse', 'core/preformatted' ), true ) ) {
		$attributes[] = 'content';
	}

	return array_values( array_unique( $attributes ) );
}
add_filter( 'block_bindings_supported_attributes', 'awesome_random_text_supported_attributes', 10, 2 );
