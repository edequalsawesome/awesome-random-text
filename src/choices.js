export function choicesFromText( value ) {
	return value.replace( /\r\n/g, '\n' ).split( '\n' );
}

export function previewChoice( choices ) {
	const choice = Array.isArray( choices )
		? choices.find( ( value ) => typeof value === 'string' && value.trim() )
		: undefined;

	return choice ? choice.replace( /&/g, '&amp;' ) : undefined;
}
