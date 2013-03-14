<?php

namespace Dewdrop\Cli\Renderer;

use Dewdrop\Cli\Renderer\Markdown;

class MarkdownTest extends \PHPUnit_Framework_TestCase
{
    protected $renderer;

    public function setUp()
    {
        $this->renderer = new Markdown();
    }

    public function testTitle()
    {
        ob_start();
        $this->renderer->title('XXXXX');
        $out = ob_get_clean();

        $this->assertContains('XXXXX', $out);
        $this->assertContains('=====', $out);
    }

    public function testSubhead()
    {
        ob_start();
        $this->renderer->subhead('XXXXX');
        $out = ob_get_clean();

        $this->assertContains('XXXXX', $out);
        $this->assertContains('-----', $out);
    }

    public function testShortText()
    {
        ob_start();
        $this->renderer->text('Short text');
        $out = ob_get_clean();

        $this->assertEquals('Short text' . PHP_EOL, $out);
    }

    public function testTableAlignsLabels()
    {
        $rows = array(
            'title'       => 'description',
            'longertitle' => 'description'
        );

        ob_start();
        $this->renderer->table($rows);
        $out = ob_get_clean();

        $this->assertContains('title:      ', $out);
    }

    public function testLongTextHasNewlinesFromWrapping()
    {
        ob_start();
        $this->renderer->text(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas ante ipsum, pharetra eu imperdiet vitae, ultrices sit amet quam. Nam in felis dolor, in sodales nibh. Nunc accumsan sollicitudin varius. Integer purus nulla, tristique vel cursus id, vehicula vel ipsum. Vestibulum non nunc sed nisl tristique luctus a vitae nibh. Pellentesque scelerisque fringilla rutrum. Maecenas in massa semper tortor faucibus vulputate. Nulla ut odio nisi, in aliquam ligula. Suspendisse potenti. Pellentesque varius consectetur tellus, id mollis velit tristique id. Suspendisse condimentum mi nec lectus tincidunt scelerisque. Pellentesque tincidunt neque vitae sapien convallis auctor. Duis accumsan molestie imperdiet.'
        );
        $out = ob_get_clean();

        $this->assertEquals(10, substr_count($out, PHP_EOL));
    }

    public function testErrorMessage()
    {
        ob_start();
        $this->renderer->error('Test message');
        $out = ob_get_clean();

        $this->assertContains('ERROR:', $out);
        $this->assertContains('Test message', $out);
    }

    public function testNewline()
    {
        ob_start();
        $this->renderer->newline();
        $out = ob_get_clean();

        $this->assertEquals(PHP_EOL, $out);
    }
}
