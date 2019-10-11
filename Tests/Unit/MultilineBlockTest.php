<?php
namespace Prgfx\Fusion\TemplateLiterals\Tests\Unit;

use Neos\Flow\Tests\UnitTestCase;
use Neos\Utility\ObjectAccess;
use Prgfx\Fusion\TemplateLiterals\Fusion\PlainTemplateLiterals;

class MultilineBlockTest extends UnitTestCase
{
    /**
     * @var PlainTemplateLiterals
     */
    protected $dsl;

    public function setUp()
    {
        $this->dsl = new PlainTemplateLiterals();
        $blockDelimiters = array(
            'default' => '',
            'block' => '|',
            'singleLine' => '>',
            'compress' => '>>',
        );
        ObjectAccess::setProperty($this->dsl, 'blockDelimiters', $blockDelimiters, true);
    }

    /**
     * @test
     */
    public function testDefaultMode()
    {
        $code = <<<'FUSION'
        value = plain`
            this is default
            multiline text that
            should not be modified
        `
FUSION;
        $fusion = $this->dsl->transpile($this->getDslCode($code));
        $expectedValue = <<<'FUSION'
'            this is default
            multiline text that
            should not be modified'

FUSION;
        $this->assertEquals($expectedValue, $fusion);
    }

    /**
     * @test
     */
    public function testBlockMode()
    {
        $code = <<<'FUSION'
        value = plain`|
            this is block
            multiline text that
            should not be indented
        `
FUSION;
        $fusion = $this->dsl->transpile($this->getDslCode($code));
        $expectedValue = <<<'FUSION'
'this is block
multiline text that
should not be indented'

FUSION;
        $this->assertEquals($expectedValue, $fusion);
    }

    /**
     * @test
     */
    public function testDifferentlyIndentedBlock()
    {
        $code = <<<'FUSION'
        value = plain`|
            line 1
                line 2
            line 3
        line 4
        `
FUSION;
        $fusion = $this->dsl->transpile($this->getDslCode($code));
        $expectedValue = <<<'FUSION'
'line 1
    line 2
line 3
        line 4'

FUSION;
        $this->assertEquals($expectedValue, $fusion);
    }

    /**
     * @test
     */
    public function testSingleLineMode()
    {
        $code = <<<'FUSION'
        value = plain`>
            this is single-line
            multiline text that
            should be joined to a line
        `
FUSION;
        $fusion = $this->dsl->transpile($this->getDslCode($code));
        $expectedValue = '\'this is single-line multiline text that should be joined to a line\'' . PHP_EOL;
        $this->assertEquals($expectedValue, $fusion);
    }

    /**
     * @test
     */
    public function testDifferentlyIndentedSingleLineMode()
    {
        $code = <<<'FUSION'
        value = plain`>
            this is single-line
                multiline text that
            should be joined to a line
        `
FUSION;
        $fusion = $this->dsl->transpile($this->getDslCode($code));
        $expectedValue = '\'this is single-line     multiline text that should be joined to a line\'' . PHP_EOL;
        $this->assertEquals($expectedValue, $fusion);
    }

    /**
     * @test
     */
    public function testCompressedMode()
    {
        $code = <<<'FUSION'
        value = plain`>>
            this is single-line
            multiline text that
            should be joined to a line
        `
FUSION;
        $fusion = $this->dsl->transpile($this->getDslCode($code));
        $expectedValue = '\'this is single-line multiline text that should be joined to a line\'' . PHP_EOL;
        $this->assertEquals($expectedValue, $fusion);
    }

    /**
     * @test
     */
    public function testDifferentlyIndentedCompressedMode()
    {
        $code = <<<'FUSION'
        value = plain`>>
            this is single-line
                multiline text that
            should be joined to a line
        `
FUSION;
        $fusion = $this->dsl->transpile($this->getDslCode($code));
        $expectedValue = '\'this is single-line multiline text that should be joined to a line\'' . PHP_EOL;
        $this->assertEquals($expectedValue, $fusion);
    }

    protected function getDslCode($code)
    {
        return explode('`', $code)[1];
    }
}
