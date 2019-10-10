<?php


namespace Prgfx\Fusion\TemplateLiterals\Fusion;


use Neos\Fusion;
use Neos\Fusion\Core\DslInterface;

class PlainTemplateLiterals implements DslInterface
{
    protected $arrayPrototype = 'Neos.Fusion:Join';

    protected $indentation = '    ';

    /**
     * Transpile the given dsl-code to fusion-code
     *
     * @param string $code
     * @return string
     * @throws Fusion\Exception
     */
    public function transpile($code)
    {
        list($stringParts, $expressionParts) = $this->parse($code);
        return $this->generateCode($stringParts, ...$expressionParts);
    }

    /**
     * @param string $code
     * @return array [stringParts: string[], expressionParts: string[]]
     */
    protected function parse($code)
    {
        if (strpos($code, '${') === false || strpos($code, '}') === false) {
            return [[$code], []];
        }
        $stringParts = [];
        $expressionParts = [];
        $readingStringPart = true;
        $currentPart = '';
        $braceCount = 0;
        $isString = false;
        $stringDelimiter = null;
        $cursor = 0;
        $codeLen = strlen($code);
        while ($cursor < $codeLen) {
            if ($readingStringPart) {
                if ($code[$cursor] === '$') {
                    // might be an expression part
                    if ($cursor + 1 < $codeLen && $code[$cursor + 1] === '{') {
                        $stringParts[] = $currentPart;
                        $currentPart = '';
                        $readingStringPart = false;
                        continue;
                    }
                }
                $currentPart .= $code[$cursor];
            } else {
                $currentPart .= $code[$cursor];
                // we want to ignore brackets in string expressions
                if (!$isString) {
                    if ($code[$cursor] === '{') {
                        $braceCount++;
                    } elseif ($code[$cursor] === '}') {
                        $braceCount--;
                        if ($braceCount === 0) {
                            if (empty($stringParts)) {
                                $stringParts[] = '';
                            }
                            $expressionParts[] = $currentPart;
                            $currentPart = '';
                            $readingStringPart = true;
                            $cursor++;
                            continue;
                        }
                    } elseif ($code[$cursor] === '\'' || $code[$cursor] === '"') {
                        $stringDelimiter = $code[$cursor];
                        $isString = true;
                    }
                } else {
                    if ($code[$cursor] === $stringDelimiter) {
                        $isString = false;
                    }
                }
            }
            $cursor++;
        }
        if (count($expressionParts) === count($stringParts)) {
            $stringParts[] = $currentPart;
        } else {
            $stringParts[count($stringParts) - 1] .= $currentPart;
        }
        // cut of the ${ and }
        $expressionParts = array_map(function($part) {
            return substr($part, 2, -1);
        }, $expressionParts);
        if (count($expressionParts) === count($stringParts)) {
            $stringParts[] = '';
        }
        return [$stringParts, $expressionParts];
    }

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

    /**
     * @param $string
     * @return string
     */
    protected function escapeString($string)
    {
        return str_replace('\'', '\\\'', $string);
    }
}
