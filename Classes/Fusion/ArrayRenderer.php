<?php


namespace Prgfx\Fusion\TemplateLiterals\Fusion;

use Neos\Flow\Annotations as Flow;

class ArrayRenderer extends PlainTemplateLiterals
{

    /**
     * The Fusion prototype wrapping the rendered code
     * @Flow\InjectConfiguration("formatting.arrayRenderer.arrayPrototype")
     * @var string
     */
    protected $arrayPrototype = 'Neos.Fusion:Join';

    /**
     * @Flow\InjectConfiguration("formatting.arrayRenderer.indentation")
     * @var string
     */
    protected $indentation = '    ';

    /**
     * Generate fusion code given arguments the same way tagged template literals in javascript present their values
     * @param string[] $stringParts
     * @param string ...$expressions
     * @return string
     */
    protected function generateCode(array $stringParts, ...$expressions)
    {
        if (empty($stringParts)) {
            return "''";
        }
        if (count($stringParts) === 1) {
            return '\'' . $this->escapeString($stringParts[0]) . '\'' . PHP_EOL;
        }
        if (count($expressions) === 1 && $stringParts[0] === '' && $stringParts[1] === '') {
            return '${' . $expressions[0] . '}' . PHP_EOL;
        }
        $code = $this->arrayPrototype . ' {' . PHP_EOL;
        for ($i = 0; $i < count($stringParts); $i++) {
            $stringValue = $this->escapeString($stringParts[$i]);
            if ($stringValue !== '') {
                $code .= $this->indentation . $i . 's = \'' . $stringValue . '\'' . PHP_EOL;
            }
            if ($i < count($stringParts) && isset($expressions[$i])) {
                $code .= $this->indentation . $i . 'v = ${' . $expressions[$i] . '}' . PHP_EOL;
            }
        }
        $code .= '}' . PHP_EOL;
        return $code;
    }
}
