// .stylelintrc.cjs
module.exports = {
  extends: [
    'stylelint-config-standard-scss'
  ],
  customSyntax: 'postcss-scss',
  rules: {
    // Base rules
    'indentation': null,
    'string-quotes': null,
    'max-empty-lines': null,

    // Allow empty source files
    'no-empty-source': null,

    // SCSS specific
    'scss/dollar-variable-pattern': null,
    'scss/selector-no-union-class-name': null,
    'scss/load-no-partial-leading-underscore': null,

    // Allow CSS custom properties
    'property-no-vendor-prefix': true,
    'value-no-vendor-prefix': true,    // Color related rules
    'color-hex-length': null,
    'color-named': null,
    'color-function-notation': null,
    'alpha-value-notation': null,
    'color-function-alias-notation': null,

    // Import and URL rules
    'function-url-quotes': null,
    'selector-class-pattern': null,
    'import-notation': null,

    // Allowing Vuetify style nesting
    'selector-pseudo-class-no-unknown': [
      true,
      {
        'ignorePseudoClasses': ['deep', 'global']
      }
    ],
    'selector-pseudo-element-no-unknown': [
      true,
      {
        'ignorePseudoElements': ['v-deep', 'v-global', 'v-slotted']
      }
    ],

    // Font families
    'font-family-name-quotes': null,

    // SCSS specifics
    'at-rule-no-unknown': null,
    'scss/at-rule-no-unknown': null
  }
};
