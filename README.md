# Prgfx.Fusion.TemplateLiterals

A [Neos Fusion](https://github.com/neos/typoscript) DSL implementation based on tagged template literals in javascript.

## Usage
By default this package defines the DSL identifier `plain`, but you can alias it for more meaningful tags for your application.
```js
p = Some.Example:Prototype {
    value = plain`Some text with ${I18n.translate('interpolated')} values`
}
```

## Custom implementations
This package is implemented in a way that you can easily extend the `PlainTemplateLiterals` implementation and override the `generateCode` method.
This method receives `stringParts: string[]` and `...expressions: string[]` just like the javascript equivalent.
