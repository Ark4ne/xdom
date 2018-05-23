<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use XDOM\Query;

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
</body>
</html>
HTML
        );

        return $doc;
    }

    public function testQuery()
    {
        $doc = $this->loadDoc();

        $nodes = Query::query($doc, 'section');
        $this->assertEquals(2, $nodes->length);
        $this->assertIsNode($nodes, 'section');

        $nodes = Query::query($doc, 'section:first-child');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'section');
        $this->assertEquals("1", $nodes->item(0)->attributes->getNamedItem('id')->textContent);

        $nodes = Query::query($doc, '[index]');
        $this->assertEquals(12, $nodes->length);
        $this->assertIsNode($nodes, 'span');

        $nodes = Query::query($doc, '.text.bold');
        $this->assertEquals(6, $nodes->length);
        $this->assertIsNode($nodes, 'span');

        $nodes = Query::query($doc, 'section:first-child .text.bold');
        $this->assertEquals(3, $nodes->length);
        $this->assertIsNode($nodes, 'span');

        $nodes = Query::query($doc, 'section .text');
        $this->assertEquals(12, $nodes->length);
        $this->assertIsNode($nodes, 'span');

        $nodes = Query::query($doc, 'section .text:nth-child(odd)');
        $this->assertEquals(6, $nodes->length);
        $this->assertIsNode($nodes, 'span');
        foreach ($nodes as $node) {
            $this->assertEquals(0, $node->attributes->getNamedItem('index')->nodeValue % 2);
        }

        $nodes = Query::query($doc, '.text:not(.bold)');
        $this->assertEquals(6, $nodes->length);
        $this->assertIsNode($nodes, 'span');
        foreach ($nodes as $node) {
            $this->assertTrue(intval($node->attributes->getNamedItem('index')->nodeValue) < 3);
        }

        $nodes = Query::query($doc, '.text:not(.bold):not(:contains(S1))');
        $this->assertEquals(3, $nodes->length);
        $this->assertIsNode($nodes, 'span');
        foreach ($nodes as $node) {
            $this->assertTrue(intval($node->attributes->getNamedItem('index')->nodeValue) < 3);
            $this->assertStringStartsWith('S2', $node->textContent);
        }
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
}
