import { choicesFromText, previewChoice } from './choices';

describe( 'choicesFromText', () => {
	it( 'retains blank trailing lines while editing', () => {
		expect( choicesFromText( 'First\r\nSecond\n' ) ).toEqual( [
			'First',
			'Second',
			'',
		] );
	} );

	it( 'preserves literal entities in the plain-text preview', () => {
		expect( previewChoice( [ '<b>Rocket</b> &lt; $1 🐶' ] ) ).toBe(
			'<b>Rocket</b> &amp;lt; $1 🐶'
		);
	} );

	it( 'omits a preview for malformed or empty choices', () => {
		expect( previewChoice( [ '', '  ' ] ) ).toBeUndefined();
		expect( previewChoice( [ '\u00a0', '\ufeff' ] ) ).toBeUndefined();
		expect( previewChoice( 'not an array' ) ).toBeUndefined();
	} );

	it( 'retains zero as a choice', () => {
		expect( previewChoice( [ '0' ] ) ).toBe( '0' );
	} );

	it( 'retains NEL because JavaScript does not trim it', () => {
		expect( previewChoice( [ '\u0085' ] ) ).toBe( '\u0085' );
	} );
} );
