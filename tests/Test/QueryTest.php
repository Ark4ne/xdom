<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use XDOM\XDOM;

/**
 * Class QueryTest
 *
 * @package Test
 */
class QueryTest extends TestCase
{
    private function loadDoc()
    {
        $doc = new \DOMDocument();

        @$doc->loadHTML(
            <<<HTML
<html>
<head></head>
<body>
    <section id="1">
        <span index="0" class="text">S1 Bale</span>
        <span index="1" class="text">S1 Neeson</span>
        <span index="2" class="text">S1 Caine</span>
        <span index="3" class="text bold">S1 Sign</span>
        <span index="4" class="text bold">S1 Citizen</span>
        <span index="5" class="text bold">S1 Trust</span>
    </section>
    <section id="2">
        <span index="0" class="text">S2 Bale</span>
        <span index="1" class="text">S2 Neeson</span>
        <span index="2" class="text">S2 Caine</span>
        <span index="3" class="text bold">S2 Sign</span>
        <span index="4" class="text bold">S2 Citizen</span>
        <span index="5" class="text bold">S2 Trust</span>
    </section>
    <article>
        <p>
          Text 1 <a href="#href_1" class="btn link">link</a>
          Text 2 <a href="#href_2" class="btn link"><span>span_link_1</span></a>
        </p>
        <div>
          Text 3 <a href="#href_3" class="btn"><span>span_link_2</span></a>
          Text 4 <a href="#href_4" class="link">link</a>
        </div>
    </article>
</body>
</html>
HTML
        );

        return $doc;
    }

    /**
     * @throws \PHPUnit_Framework_AssertionFailedError
     * @throws \PHPUnit_Framework_Exception
     * @throws \XDOM\Exceptions\Exception
     */
    public function testQuery()
    {
        $doc = $this->loadDoc();

        $nodes = XDOM::find($doc, 'section');
        $this->assertEquals(2, $nodes->length);
        $this->assertIsNode($nodes, 'section');

        $nodes = XDOM::find($doc, 'section:first-child');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'section');
        $this->assertAttrValue("1", $nodes, 'id');

        $nodes = XDOM::find($doc, '[index]');
        $this->assertEquals(12, $nodes->length);
        $this->assertIsNode($nodes, 'span');

        $nodes = XDOM::find($doc, '.text.bold');
        $this->assertEquals(6, $nodes->length);
        $this->assertIsNode($nodes, 'span');

        $nodes = XDOM::find($doc, 'section:first-child .text.bold');
        $this->assertEquals(3, $nodes->length);
        $this->assertIsNode($nodes, 'span');

        $nodes = XDOM::find($doc, 'section .text');
        $this->assertEquals(12, $nodes->length);
        $this->assertIsNode($nodes, 'span');

        $nodes = XDOM::find($doc, 'section .text:nth-child(odd)');
        $this->assertEquals(6, $nodes->length);
        $this->assertIsNode($nodes, 'span');
        foreach ($nodes as $idx => $node) {
            $this->assertAttrValue(($idx * 2) % 6, $node, 'index');
        }

        $nodes = XDOM::find($doc, 'section .text:first-child');
        $this->assertEquals(2, $nodes->length);
        $this->assertIsNode($nodes, 'span');
        foreach ($nodes as $node) {
            $this->assertAttrValue(0, $node, 'index');
        }

        $nodes = XDOM::find($doc, '.text:not(.bold)');
        $this->assertEquals(6, $nodes->length);
        $this->assertIsNode($nodes, 'span');
        foreach ($nodes as $idx => $node) {
            $this->assertAttrValue($idx % 3, $node, 'index');
        }

        $nodes = XDOM::find($doc, '.text:not(.bold):not(:contains(S1))');
        $this->assertEquals(3, $nodes->length);
        $this->assertIsNode($nodes, 'span');
        foreach ($nodes as $idx => $node) {
            $this->assertAttrValue($idx, $node, 'index');
            $this->assertStringStartsWith('S2', $node->textContent);
        }

        $nodes = XDOM::find($doc, 'a:not(:has(span))');
        $this->assertEquals(2, $nodes->length);
        $this->assertIsNode($nodes, 'a');
        $this->assertAttrValue('#href_1', $nodes->item(0), 'href');
        $this->assertAttrValue('#href_4', $nodes->item(1), 'href');

        $nodes = XDOM::find($doc, 'a:not(:has(span)):first');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'a');
        $this->assertAttrValue('#href_1', $nodes, 'href');

        $nodes = XDOM::find($doc, 'a:not(:has(span)):last');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'a');
        $this->assertAttrValue('#href_4', $nodes, 'href');

        $nodes = XDOM::find($doc, 'a:has(span) :first');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'span');
        $this->assertTextContent('span_link_1', $nodes);
        $nodes = XDOM::find($doc, 'a:has(span) :last');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'span');
        $this->assertTextContent('span_link_2', $nodes);

        $nodes = XDOM::find($doc, 'a:has(span):first :last');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'span');
        $this->assertTextContent('span_link_1', $nodes);
        $nodes = XDOM::find($doc, 'a:has(span):last :first');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'span');
        $this->assertTextContent('span_link_2', $nodes);

        $nodes = XDOM::find($doc, 'div a, p a');
        $this->assertEquals(4, $nodes->length);
        $this->assertIsNode($nodes, 'a');

        $nodes = XDOM::find($doc, 'div a:not(:has(span)), p a:not(:has(span))');
        $this->assertEquals(2, $nodes->length);
        $this->assertIsNode($nodes, 'a');

        $nodes = XDOM::find($doc, 'div a:first, p a:first');
        $this->assertEquals(2, $nodes->length);
        $this->assertIsNode($nodes, 'a');

        $nodes = XDOM::find($doc, 'div:first');
        $a = XDOM::find($nodes->item(0), 'a');
        $this->assertEquals(2, $a->length);
        $this->assertIsNode($a, 'a');
        $this->assertAttrValue("#href_3", $a->item(0), 'href');
        $this->assertAttrValue("#href_4", $a->item(1), 'href');

        $a = XDOM::find($nodes->item(0), 'a:first');
        $this->assertEquals(1, $a->length);
        $this->assertIsNode($a, 'a');
        $this->assertAttrValue("#href_3", $a->item(0), 'href');

        $a = XDOM::find($nodes->item(0), 'a:last');
        $this->assertEquals(1, $a->length);
        $this->assertIsNode($a, 'a');
        $this->assertAttrValue("#href_4", $a->item(0), 'href');

        $a = XDOM::find($nodes->item(0), '.link');
        $this->assertEquals(1, $a->length);
        $this->assertIsNode($a, 'a');
        $this->assertAttrValue("#href_4", $a->item(0), 'href');

        $a = XDOM::find($nodes->item(0), '> .link');
        $this->assertEquals(1, $a->length);
        $this->assertIsNode($a, 'a');
        $this->assertAttrValue("#href_4", $a->item(0), 'href');

        $a = XDOM::find($nodes->item(0), '> .link, > .btn');
        $this->assertEquals(2, $a->length);
        $this->assertIsNode($a, 'a');
        $this->assertAttrValue("#href_3", $a->item(0), 'href');
        $this->assertAttrValue("#href_4", $a->item(1), 'href');
    }

    private function assertIsNode(\DOMNodeList $nodes, $type)
    {
        if (is_string($type)) {
            foreach ($nodes as $node) {
                /** @var \DOMElement $node */
                $this->assertEquals($type, $node->tagName);
            }
        } else {
            foreach ($type as $k => $value) {
                $this->assertEquals($value, $nodes[$k]->tagName);
            }
        }
    }

    private function assertTextContent($expected, $node)
    {
        if ($node instanceof \DOMNodeList) {
            $node = $node->item(0);
        }

        $this->assertEquals($expected, $node->textContent);
    }

    private function assertAttrValue($expected, $node, $attr)
    {
        if ($node instanceof \DOMNodeList) {
            $node = $node->item(0);
        }

        $this->assertEquals($expected, XDOM::attr($node, $attr));
    }

}
