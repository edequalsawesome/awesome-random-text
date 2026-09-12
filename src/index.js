import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import {
	InspectorControls,
	useBlockBindingsUtils,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextareaControl,
	ToggleControl,
	Notice,
} from '@wordpress/components';
import { registerBlockBindingsSource } from '@wordpress/blocks';
import { useSelect } from '@wordpress/data';
import { escapeHTML } from '@wordpress/escape-html';
import { __ } from '@wordpress/i18n';
import { choicesFromText, previewChoice } from './choices';

export const SOURCE = 'awesome-random-text/choice';

export const attributesByBlock = {
	'core/paragraph': 'content',
	'core/heading': 'content',
	'core/button': 'text',
	'core/list-item': 'content',
	'core/verse': 'content',
	'core/preformatted': 'content',
};

registerBlockBindingsSource( {
	name: SOURCE,
	label: __( 'Awesome Random Text', 'awesome-random-text' ),
	getValues( { bindings, select, clientId } ) {
		const block = select( blockEditorStore ).getBlock( clientId );

		if ( block?.name === 'core/list-item' && block.innerBlocks.length ) {
			return {};
		}

		return Object.entries( bindings ).reduce(
			( values, [ attribute, binding ] ) => {
				const choice = previewChoice( binding?.args?.choices );

				if ( choice ) {
					values[ attribute ] = escapeHTML( choice );
				}

				return values;
			},
			{}
		);
	},
} );

const withRandomTextControls = createHigherOrderComponent(
	( BlockEdit ) => ( props ) => {
		const attribute = attributesByBlock[ props.name ];
		const { updateBlockBindings } = useBlockBindingsUtils( props.clientId );
		const hasInnerBlocks = useSelect(
			( select ) =>
				select( blockEditorStore ).getBlock( props.clientId )
					?.innerBlocks?.length > 0,
			[ props.clientId ]
		);

		if ( ! attribute ) {
			return <BlockEdit { ...props } />;
		}

		const binding = props.attributes.metadata?.bindings?.[ attribute ];
		const defaultBinding = props.attributes.metadata?.bindings?.__default;
		const enabled = binding?.source === SOURCE;
		const conflict =
			( binding && ! enabled ) ||
			( ! binding &&
				defaultBinding?.source === 'core/pattern-overrides' );
		const storedChoices = Array.isArray(
			props.attributes.metadata?.awesomeRandomText?.choices
		)
			? props.attributes.metadata.awesomeRandomText.choices
			: [];
		const choices =
			enabled && Array.isArray( binding.args?.choices )
				? binding.args.choices
				: storedChoices;
		const saveChoices = ( nextChoices ) =>
			props.setAttributes( {
				metadata: {
					...props.attributes.metadata,
					awesomeRandomText: { choices: nextChoices },
				},
			} );

		const updateChoices = ( value ) => {
			const nextChoices = choicesFromText( value );
			saveChoices( nextChoices );
			updateBlockBindings( {
				[ attribute ]: {
					source: SOURCE,
					args: { choices: nextChoices },
				},
			} );
		};
		let panelContent;

		if ( hasInnerBlocks ) {
			panelContent = (
				<>
					<Notice status="warning" isDismissible={ false }>
						{ __(
							'Random text cannot replace a list item that contains a nested list.',
							'awesome-random-text'
						) }
					</Notice>
					{ enabled && (
						<ToggleControl
							label={ __(
								'Use random text',
								'awesome-random-text'
							) }
							checked={ true }
							onChange={ () => {
								saveChoices( choices );
								updateBlockBindings( {
									[ attribute ]: undefined,
								} );
							} }
						/>
					) }
				</>
			);
		} else if ( conflict ) {
			panelContent = (
				<Notice status="warning" isDismissible={ false }>
					{ __(
						'This text is already connected to another binding.',
						'awesome-random-text'
					) }
				</Notice>
			);
		} else {
			panelContent = (
				<>
					<ToggleControl
						label={ __( 'Use random text', 'awesome-random-text' ) }
						checked={ enabled }
						onChange={ ( nextEnabled ) => {
							const nextChoices = choices.length
								? choices
								: [ '' ];
							saveChoices( nextChoices );
							updateBlockBindings( {
								[ attribute ]: nextEnabled
									? {
											source: SOURCE,
											args: { choices: nextChoices },
									  }
									: undefined,
							} );
						} }
					/>
					{ enabled && (
						<TextareaControl
							label={ __( 'Choices', 'awesome-random-text' ) }
							help={ __(
								'One plain-text choice per line. The editor shows the first choice; cached pages may reuse a choice.',
								'awesome-random-text'
							) }
							value={ choices.join( '\n' ) }
							onChange={ updateChoices }
						/>
					) }
				</>
			);
		}

		return (
			<>
				<BlockEdit { ...props } />
				{ props.isSelected && (
					<InspectorControls>
						<PanelBody
							title={ __( 'Random text', 'awesome-random-text' ) }
						>
							{ panelContent }
						</PanelBody>
					</InspectorControls>
				) }
			</>
		);
	},
	'withRandomTextControls'
);

addFilter(
	'editor.BlockEdit',
	'awesome-random-text/controls',
	withRandomTextControls
);
