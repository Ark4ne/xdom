<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use XDOM\XDOM;

class FuncTest extends TestCase
{

    private function loadDoc()
    {
        $doc = new \DOMDocument();

        @$doc->loadHTML(
          <<<HTML
<html>
<head></head>
<body>
    <div class="bio">
        <div class="title">Bio Title</div>
        <div class="text">Lorem ipsum dolor sit amet.
            <p>P1 Lorem ipsum dolor sit amet.</p>
            <p>P2 Lorem ipsum dolor sit amet.</p>
        </div>
    </div>
</body>
</html>
HTML
        );

        return $doc;
    }

    public function testText()
    {
        $doc = $this->loadDoc();

        $xdom = new XDOM($doc);

        $this->assertEquals('Bio Title', $xdom->find('.title')->text());
        $this->assertEquals('P1 Lorem ipsum dolor sit amet.', $xdom->find('.text p')->text());
        $this->assertEquals('P2 Lorem ipsum dolor sit amet.', $xdom->find('.text p:last')->text());
        $this->assertEquals('Lorem ipsum dolor sit amet.
            P1 Lorem ipsum dolor sit amet.
            P2 Lorem ipsum dolor sit amet.
        ', $xdom->find('.text')->text());
        $this->assertEquals('
        Bio Title
        Lorem ipsum dolor sit amet.
            P1 Lorem ipsum dolor sit amet.
            P2 Lorem ipsum dolor sit amet.
        
    ', $xdom->find('.bio')->text());
    }

    public function testHtml()
    {
        $doc = $this->loadDoc();

        $xdom = new XDOM($doc);

        $this->assertEquals('<div class="title">Bio Title</div>', $xdom->find('.title')->html());
        $this->assertEquals('<div class="text">Lorem ipsum dolor sit amet.
            <p>P1 Lorem ipsum dolor sit amet.</p>
            <p>P2 Lorem ipsum dolor sit amet.</p>
        </div>', $xdom->find('.text')->html());
        $this->assertEquals('<p>P1 Lorem ipsum dolor sit amet.</p>', $xdom->find('.text p')->html());
    }

    public function testInnerHtml()
    {
        $doc = $this->loadDoc();

        $xdom = new XDOM($doc);

        $this->assertEquals('Bio Title', $xdom->find('.title')->innerHtml());
        $this->assertEquals('Lorem ipsum dolor sit amet.
            <p>P1 Lorem ipsum dolor sit amet.</p>
            <p>P2 Lorem ipsum dolor sit amet.</p>
        ', $xdom->find('.text')->innerHtml());
    }
}
