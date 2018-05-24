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
    <article>
        <p>
          Text 1 <a href="#href_1" class="btn link">link</a>
          Text 2 <a href="#href_2" class="btn link"><span>link</span></a>
        </p>
        <div>
          Text 3 <a href="#href_3" class="btn link"><span>link</span></a>
          Text 4 <a href="#href_4" class="btn link">link</a>
        </div>
    </article>
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

        $frag = $nodes->item(0)->ownerDocument->createDocumentFragment();

        $frag->appendChild($nodes->item(0));
        $frag->appendChild($nodes->item(1));

        $t = $frag->childNodes->item(0) === $nodes->item(0);

        $nodes = Query::query($frag, 'section');

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

        $nodes = Query::query($doc, 'section .text:first-child');
        $this->assertEquals(2, $nodes->length);
        $this->assertIsNode($nodes, 'span');
        foreach ($nodes as $node) {
            $this->assertEquals(0, $node->attributes->getNamedItem('index')->nodeValue);
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

        $nodes = Query::query($doc, 'a:not(:has(span))');
        $this->assertEquals(2, $nodes->length);
        $this->assertIsNode($nodes, 'a');
        $this->assertEquals('#href_1', $nodes->item(0)->attributes->getNamedItem('href')->nodeValue);
        $this->assertEquals('#href_4', $nodes->item(1)->attributes->getNamedItem('href')->nodeValue);

        $nodes = Query::query($doc, 'a:not(:has(span)):first');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'a');
        $this->assertEquals('#href_1', $nodes->item(0)->attributes->getNamedItem('href')->nodeValue);
        $nodes = Query::query($doc, 'a:not(:has(span)):last');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'a');
        $this->assertEquals('#href_4', $nodes->item(0)->attributes->getNamedItem('href')->nodeValue);

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
