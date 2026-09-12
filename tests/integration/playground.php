<?php
/**
 * Real WordPress rendering checks run by the Playground blueprint.
 *
 * @package AwesomeRandomText
 */

require '/wordpress/wp-load.php';
require_once WP_PLUGIN_DIR . '/awesome-random-text/awesome-random-text.php';

/**
 * Fails the Blueprint step with an actionable message.
 *
 * @param bool   $condition Assertion condition.
 * @param string $message   Failure message.
 * @return void
 */
function awesome_random_text_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

/**
 * Builds serialized block markup for rendering checks.
 *
 * @param string               $name       Block name.
 * @param array<string, mixed> $attributes Block attributes.
 * @param string               $content    Saved fallback markup.
 * @return string
 */
function awesome_random_text_block( $name, $attributes, $content ) {
	return sprintf(
		'<!-- wp:%1$s %2$s -->%3$s<!-- /wp:%1$s -->',
		$name,
		wp_json_encode( $attributes ),
		$content
	);
}

$source  = AWESOME_RANDOM_TEXT_SOURCE;
$binding = array(
	'metadata' => array(
		'bindings' => array(
			'content' => array(
				'source' => $source,
				'args'   => array( 'choices' => array( 'Bound text' ) ),
			),
		),
	),
);

// The source is intentionally unavailable before registration: native fallback wins.
$fallback = awesome_random_text_block(
	'core/paragraph',
	$binding,
	'<p>Saved fallback</p>'
);
awesome_random_text_assert(
	false !== strpos( do_blocks( $fallback ), 'Saved fallback' ),
	'An unavailable source must retain saved paragraph content.'
);

awesome_random_text_register();

foreach (
	array(
		'core/paragraph'    => '<p>Saved fallback</p>',
		'core/heading'      => '<h2>Saved fallback</h2>',
		'core/list-item'    => '<li>Saved fallback</li>',
		'core/verse'        => '<pre class="wp-block-verse">Saved fallback</pre>',
		'core/preformatted' => '<pre class="wp-block-preformatted">Saved fallback</pre>',
	) as $name => $fallback_content
) {
	awesome_random_text_assert(
		false !== strpos( do_blocks( awesome_random_text_block( $name, $binding, $fallback_content ) ), 'Bound text' ),
		$name . ' did not render its bound plain-text choice.'
	);
}

$button_binding                                  = $binding;
$button_binding['metadata']['bindings']['text'] = $button_binding['metadata']['bindings']['content'];
unset( $button_binding['metadata']['bindings']['content'] );
awesome_random_text_assert(
	false !== strpos(
		do_blocks( awesome_random_text_block( 'core/button', $button_binding, '<div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Saved fallback</a></div>' ) ),
		'Bound text'
	),
	'core/button did not render its bound text choice.'
);

$empty_binding = $binding;
$empty_binding['metadata']['bindings']['content']['args']['choices'] = array( '', '  ', "\u{00A0}", "\u{FEFF}" );
awesome_random_text_assert(
	false !== strpos( do_blocks( awesome_random_text_block( 'core/paragraph', $empty_binding, '<p>Saved fallback</p>' ) ), 'Saved fallback' ),
	'Empty choices must retain saved fallback content.'
);

$mixed_binding = $binding;
$mixed_binding['metadata']['bindings']['content']['args']['choices'] = array( "\u{00A0}", '0', "\u{FEFF}" );
awesome_random_text_assert(
	false !== strpos( do_blocks( awesome_random_text_block( 'core/paragraph', $mixed_binding, '<p>Saved fallback</p>' ) ), '>0<' ),
	'Mixed choices must retain a valid zero string.'
);

$malformed_binding = $binding;
$malformed_binding['metadata']['bindings']['content']['args']['choices'] = 'not an array';
awesome_random_text_assert(
	false !== strpos( do_blocks( awesome_random_text_block( 'core/paragraph', $malformed_binding, '<p>Saved fallback</p>' ) ), 'Saved fallback' ),
	'Malformed choices must retain saved fallback content.'
);

$special_binding = $binding;
$special_binding['metadata']['bindings']['content']['args']['choices'] = array( '<script> $1 &lt; 0 \\ café 🐶' );
$special = do_blocks( awesome_random_text_block( 'core/paragraph', $special_binding, '<p>Saved fallback</p>' ) );
awesome_random_text_assert( false === strpos( $special, '<script>' ), 'Choices must not render HTML.' );
awesome_random_text_assert( false !== strpos( $special, '&amp;lt;' ), 'Literal entities must remain literal text.' );
awesome_random_text_assert( false !== strpos( $special, '$1' ), 'Dollar signs must remain literal text.' );
awesome_random_text_assert( false !== strpos( $special, '🐶' ), 'Unicode must remain intact.' );

$nested = awesome_random_text_block(
	'core/list-item',
	$binding,
	'<li>Parent<!-- wp:list --><ul><!-- wp:list-item --><li>Child</li><!-- /wp:list-item --></ul><!-- /wp:list --></li>'
);
$nested_rendered = do_blocks( $nested );
awesome_random_text_assert( false !== strpos( $nested_rendered, 'Parent' ), 'Nested list fallback parent was lost.' );
awesome_random_text_assert( false !== strpos( $nested_rendered, 'Child' ), 'Nested list child was lost.' );

echo "Awesome Random Text Playground checks passed.\n";
