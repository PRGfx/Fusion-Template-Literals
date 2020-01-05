<?php


namespace Prgfx\Fusion\TemplateLiterals\Tests\Unit;


use Neos\Flow\Tests\UnitTestCase;
use Prgfx\Fusion\TemplateLiterals\Fusion\PlainTemplateLiterals;

class EelRendererTest extends UnitTestCase
{
    /**
     * @var PlainTemplateLiterals()
     */
    protected $dsl;

    public function setUp()
    {
        $this->dsl = new PlainTemplateLiterals();
    }

    /**
     * @test
     */
    public function testStringOnly()
    {
        $code = 'Just plain text';
        $fusion = $this->dsl->transpile($code);
        $this->assertEquals("'$code'" . PHP_EOL, $fusion);
    }

    /**
     * @test
     */
    public function testBasicInterpolation()
    {
        $code = 'Basic string ${I18n.translate(\'Package:Source:key\')} and another ${1} interpolation';
        $fusion = $this->dsl->transpile($code);
        $expectedFusion = '${\'Basic string \' + (I18n.translate(\'Package:Source:key\')) + \' and another \' + (1) + \' interpolation\'}';
        $this->assertEquals($expectedFusion . PHP_EOL, $fusion);
    }

    /**
     * @test
     */
    public function testInterpolationAtEnd()
    {
        $code = 'Interpolation at end ${1}';
        $fusion = $this->dsl->transpile($code);
        $expectedFusion = '${\'Interpolation at end \' + (1)}';
        $this->assertEquals($expectedFusion . PHP_EOL, $fusion);
    }

    /**
     * @test
     */
    public function testInterpolationAtStart()
    {
        $code = '${0} Interpolation at start';
        $fusion = $this->dsl->transpile($code);
        $expectedFusion = '${(0) + \' Interpolation at start\'}';
        $this->assertEquals($expectedFusion . PHP_EOL, $fusion);
    }

    /**
     * @test
     */
    public function testAdjacentInterpolations()
    {
        $code = '${0}${1}';
        $fusion = $this->dsl->transpile($code);
        $expectedFusion = '${(0) + (1)}';
        $this->assertEquals($expectedFusion . PHP_EOL, $fusion);
    }

}
