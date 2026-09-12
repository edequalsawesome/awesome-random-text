# Awesome Random Text

Awesome Random Text adds a **Random text** panel to supported core text blocks. Add one plain-text choice per line and a choice is selected whenever WordPress renders the block. The block keeps its native markup, styling, and design controls.

## Supported blocks

Paragraph, Heading, Button text, List Item, Verse, and Preformatted blocks on WordPress 6.9 or newer.

List Items that contain a nested list stay unavailable because replacing their text would risk removing that nested structure.

## Use

Select a supported block, open its Settings sidebar, enable **Use random text**, and add choices. The editor previews the first non-empty choice. The saved block content remains its fallback: disabling the feature or deactivating this plugin restores it. Paragraph-to-Heading and Heading-to-Paragraph transforms preserve active bindings. Other transforms, and disabled choice lists, are not guaranteed to preserve them.

Choices are plain text. HTML is not rendered. A cached response can reuse its already-rendered choice, and repeated choices are possible.

## Development

```sh
npm install
composer install
npm run build
npm run lint:js
composer lint
npm run test:js
php -d zend.assertions=1 -d assert.exception=1 tests/php/source-test.php
npm run test:playground
npm run plugin-zip
```

Use `npm run plugin-zip` to make an installable archive.

The CI run validates both supported WordPress/PHP lanes and uploads the installable ZIP as an artifact.

## License

GPL-3.0-or-later. See [LICENSE.txt](LICENSE.txt).
