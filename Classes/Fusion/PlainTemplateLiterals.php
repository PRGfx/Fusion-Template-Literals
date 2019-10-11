<?php


namespace Prgfx\Fusion\TemplateLiterals\Fusion;


use Neos\Flow\Annotations as Flow;
use Neos\Fusion;
use Neos\Fusion\Core\DslInterface;

class PlainTemplateLiterals implements DslInterface
{

    const BLOCKMODE_DEFAULT = 'default';
    const BLOCKMODE_BLOCK = 'block';
    const BLOCKMODE_SINGLELINE = 'singleLine';
    const BLOCKMODE_COMPRESS = 'compress';

    /**
     * The Fusion prototype wrapping the rendered code
     * @Flow\InjectConfiguration("formatting.arrayPrototype")
     * @var string
     */
    protected $arrayPrototype = 'Neos.Fusion:Join';

    /**
     * @Flow\InjectConfiguration("formatting.indentation")
     * @var string
     */
    protected $indentation = '    ';

    /**
     * @Flow\InjectConfiguration("blockDelimiters")
     * @var array
     */
    protected $blockDelimiters;

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
        $code = $this->processMultilineBlock($code);
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
     * Process all lines as multiline block according to different block modes
     *
     * @param string $code
     * @return string
     */
    protected function processMultilineBlock($code)
    {
        if (strpos($code, PHP_EOL) === false) {
            return $code;
        }
        $lines = explode(PHP_EOL, $code);
        $blockMode = null;
        foreach ($this->blockDelimiters as $mode => $delimiter) {
            if ($lines[0] === $delimiter) {
                $blockMode = $mode;
                break;
            }
        }
        if ($blockMode === null) {
            return $code;
        }
        if (trim($lines[count($lines) - 1]) === '') {
            $lines = array_slice($lines, 1, -1);
        } else {
            $lines = array_slice($lines, 1);
        }
        if ($blockMode === self::BLOCKMODE_BLOCK || $blockMode === self::BLOCKMODE_SINGLELINE) {
            if (preg_match('/^(\s*)/', $lines[0], $matches) === 1) {
                $indentation = $matches[1];
                $indentationLength = strlen($indentation);
                $lines = array_map(function($line) use ($indentation, $indentationLength) {
                    if (substr($line, 0, $indentationLength) === $indentation) {
                        return substr($line, $indentationLength);
                    }
                    return $line;
                }, $lines);
                if ($blockMode === self::BLOCKMODE_SINGLELINE) {
                    return implode(' ', $lines);
                }
            }
        } elseif ($blockMode === self::BLOCKMODE_COMPRESS) {
            $lines = array_map('trim', $lines);
            return implode(' ', $lines);
        }
        return implode(PHP_EOL, $lines);
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
