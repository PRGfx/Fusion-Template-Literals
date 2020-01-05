<?php
namespace Prgfx\Fusion\TemplateLiterals\Tests\Unit;

use Neos\Flow\Tests\UnitTestCase;
use Prgfx\Fusion\TemplateLiterals\Fusion\ArrayRenderer;

class ArrayRendererTest extends UnitTestCase
{
    /**
     * @var ArrayRenderer
     */
    protected $dsl;

    public function setUp()
    {
        $this->dsl = new ArrayRenderer();
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
        $expectedFusion = <<<'FUSION'
Neos.Fusion:Join {
    0s = 'Basic string '
    0v = ${I18n.translate('Package:Source:key')}
    1s = ' and another '
    1v = ${1}
    2s = ' interpolation'
}

FUSION;
        $this->assertEquals($expectedFusion, $fusion);
    }

    /**
     * @test
     */
    public function testInterpolationAtEnd()
    {
        $code = 'Interpolation at end ${1}';
        $fusion = $this->dsl->transpile($code);
        $expectedFusion = <<<'FUSION'
Neos.Fusion:Join {
    0s = 'Interpolation at end '
    0v = ${1}
}

FUSION;
        $this->assertEquals($expectedFusion, $fusion);
    }

    /**
     * @test
     */
    public function testInterpolationAtStart()
    {
        $code = '${0} Interpolation at start';
        $fusion = $this->dsl->transpile($code);
        $expectedFusion = <<<'FUSION'
Neos.Fusion:Join {
    0v = ${0}
    1s = ' Interpolation at start'
}

FUSION;
        $this->assertEquals($expectedFusion, $fusion);
    }

    /**
     * @test
     */
    public function testInterpolationBracesInString()
    {
        $code = 'Closing bracket in ${\'interpolation}\'}';
        $fusion = $this->dsl->transpile($code);
        $expectedFusion = <<<'FUSION'
Neos.Fusion:Join {
    0s = 'Closing bracket in '
    0v = ${'interpolation}'}
}

FUSION;
        $this->assertEquals($expectedFusion, $fusion);
    }

    /**
     * @test
     */
    public function testUnterminatedPattern()
    {
        $code = 'Missing closing bracket ${\'at interpolation}\'';
        $fusion = $this->dsl->transpile($code);
        $expectedFusion = str_replace('\'', '\\\'', $code);
        $this->assertEquals("'$expectedFusion'" . PHP_EOL, $fusion);
    }

    /**
     * @test
     */
    public function testIgnoreBracesInStrings()
    {
        $code = 'Correctly terminated ${\'string { with brace\'}';
        $fusion = $this->dsl->transpile($code);
        $expectedFusion = <<<'FUSION'
Neos.Fusion:Join {
    0s = 'Correctly terminated '
    0v = ${'string { with brace'}
}

FUSION;
        $this->assertEquals($expectedFusion, $fusion);
    }

    /**
     * @test
     */
    public function testNestedBraces()
    {
        $code = '${{x: 4}[\'x\']}';
        $fusion = $this->dsl->transpile($code);
        $expectedFusion = '${{x: 4}[\'x\']}' . PHP_EOL;
        $this->assertEquals($expectedFusion, $fusion);
    }
}
