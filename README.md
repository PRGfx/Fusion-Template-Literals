# Prgfx.Fusion.TemplateLiterals

A [Neos Fusion](https://github.com/neos/typoscript) DSL implementation based on tagged template literals in javascript.

```
composer require prgfx/fusion-template-literals
```

## Usage
By default this package defines the DSL identifier `plain`, but you can alias it for more meaningful tags for your application.
```js
p = Some.Example:Prototype {
    value = plain`Some text with ${I18n.translate('interpolated')} values`
}
```

## Multiline blocks
Given a block-mode modifier in the first line (and nothing else in this line), multiline blocks may be interpreted differently.
The block-mode modifier can be configured in `Prgfx.Fusion.TemplateLiterals.blockDelimiters` to your preferences.
There are different multiline block-modes:

### default
only cut off surrounding empty lines, keeps the rest as is
```js
value = plain`
    line 1
    line 2
`
// will not contain the first and last "empty" line
```

### block
will trim all indentation
```js
value = plain`|
    line 1
        line 2
    line 3
`
// will return 'line 1\n    line 2\nline3'
```

### singleLine
will trim all indentation and join newlines with a single space.
```js
value = plain`>
    line 1
    line 2
`
// 'line 1 line 2'
```

### compress
compared to singleLine will remove *all* surrounding whitespace per line:
```js
value = plain`>>
if (foo) {
    console.log(foo);
}
`
// 'if (foo) { console.log(foo); }'
```

## Custom implementations
This package is implemented in a way that you can easily extend the `PlainTemplateLiterals` implementation and override the `generateCode` method.
This method receives `stringParts: string[]` and `...expressions: string[]` just like the javascript equivalent.
