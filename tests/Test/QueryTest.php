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
    <nav>
        <h1>h1</h1>
        <h2>h2</h2>
        <h3>h3</h3>
        <h4>h4</h4>
        <h5>h5</h5>
        <h6>h6</h6>
    </nav>
    <form>
        <input/>
        <input type="button"/>
        <input type="checkbox"/>
        <input type="checkbox" checked/>
        <input type="file"/>
        <input type="password"/>
        <input type="radio"/>
        <input type="radio" checked/>
        <input type="submit"/>
        <input type="text"/>
        <input type="reset"/>
        <select>
            <option>opt_1</option>
            <option>opt_2</option>
            <option selected>opt_3</option>
            <option selected>opt_4</option>
        </select>
        <textarea></textarea>
        <button id="btn_1"></button>
        <button type="submit" id="btn_1"></button>
    </form>
    
    <div class="col-lg-1">
        <div></div>
        <i class="i-1-1"></i>
        <i class="i-1-2"></i>
    </div>
    <div class="col-lg-2">
        <div></div>
        <i class="i-2-1"></i>
        <i class="i-2-2"></i>
    </div>
    <div class="col-lg-3">
        <div></div>
        <i class="i-3-1"></i>
        <i class="i-3-2"></i>
        <p></p>
        <i class="i-3-4"></i>
        <i class="i-3-5"></i>
    </div>
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
    public function testFindTag()
    {
        $doc = $this->loadDoc();

        $nodes = XDOM::find($doc, 'section');
        $this->assertEquals(2, $nodes->length);
        $this->assertIsNode($nodes, 'section');

        $nodes = XDOM::find($doc, 'span');
        $this->assertEquals(14, $nodes->length);
        $this->assertIsNode($nodes, 'span');

        $nodes = XDOM::find($doc, 'section > span');
        $this->assertEquals(12, $nodes->length);

        $nodes = XDOM::find($doc, 'textarea ~ button');
        $this->assertEquals(2, $nodes->length);
        $this->assertIsNode($nodes, 'button');

        $nodes = XDOM::find($doc, 'input + select');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'select');

        $nodes = XDOM::find($doc, 'div + i');
        $this->assertEquals(3, $nodes->length);
        $this->assertIsNode($nodes, 'i');
        $this->assertAttrValue("i-1-1", $nodes->item(0), 'class');
        $this->assertAttrValue("i-2-1", $nodes->item(1), 'class');
        $this->assertAttrValue("i-3-1", $nodes->item(2), 'class');

        $nodes = XDOM::find($doc, 'div ~ i');
        $this->assertEquals(6, $nodes->length);
        $this->assertIsNode($nodes, 'i');

        $nodes = XDOM::find($doc, 'nav');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'nav');

        $nodes = XDOM::find($doc, 'body > nav');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'nav');

        $nodes = XDOM::find($doc, 'article');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'article');
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

        $btn = XDOM::find($doc, ':header');
        $this->assertEquals(6, $btn->length);
        $this->assertIsNode($btn, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']);

        $btn = XDOM::find($doc, 'nav:first :header');
        $this->assertEquals(6, $btn->length);
        $this->assertIsNode($btn, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']);

        $btn = XDOM::find($doc, 'nav:first :header:odd');
        $this->assertEquals(3, $btn->length);
        $this->assertIsNode($btn, ['h1', 'h3', 'h5']);

        $btn = XDOM::find($doc, 'form :input');
        $this->assertEquals(15, $btn->length);
        $this->assertIsNode($btn, [
          'input',
          'input',
          'input',
          'input',
          'input',
          'input',
          'input',
          'input',
          'input',
          'input',
          'input',
          'select',
          'textarea',
          'button',
          'button'
        ]);

        $btn = XDOM::find($doc, 'form :button');
        $this->assertEquals(3, $btn->length);
        $this->assertIsNode($btn, ['input', 'button', 'button']);
        $this->assertAttrValue('button', $btn->item(0), 'type');

        $btn = XDOM::find($doc, 'form :input:button');
        $this->assertEquals(3, $btn->length);
        $this->assertIsNode($btn, ['input', 'button', 'button']);
        $this->assertAttrValue('button', $btn->item(0), 'type');

        $btn = XDOM::find($doc, 'form :submit');
        $this->assertEquals(3, $btn->length);
        $this->assertIsNode($btn, ['input', 'button', 'button']);

        foreach (['password', 'file'] as $type) {
            $inputs = XDOM::find($doc, 'form :' . $type);
            $this->assertEquals(1, $inputs->length);
            $this->assertIsNode($inputs, 'input');
            $this->assertAttrValue($type, $inputs, 'type');
        }

        $inputs = XDOM::find($doc, 'form :checkbox');
        $this->assertEquals(2, $inputs->length);
        $this->assertIsNode($inputs, 'input');
        $this->assertAttrValue('checkbox', $inputs->item(0), 'type');
        $this->assertAttrValue('checkbox', $inputs->item(1), 'type');

        $inputs = XDOM::find($doc, 'form :checkbox:checked');
        $this->assertEquals(1, $inputs->length);
        $this->assertIsNode($inputs, 'input');
        $this->assertAttrValue('checkbox', $inputs->item(0), 'type');
        $this->assertAttrValue('checked', $inputs->item(0), 'checked');

        $inputs = XDOM::find($doc, 'form :checkbox:unchecked');
        $this->assertEquals(1, $inputs->length);
        $this->assertIsNode($inputs, 'input');
        $this->assertAttrValue('checkbox', $inputs->item(0), 'type');
        $this->assertAttrValue(null, $inputs->item(0), 'checked');

        $inputs = XDOM::find($doc, 'form :radio:checked');
        $this->assertEquals(1, $inputs->length);
        $this->assertIsNode($inputs, 'input');
        $this->assertAttrValue('radio', $inputs->item(0), 'type');
        $this->assertAttrValue('checked', $inputs->item(0), 'checked');

        $inputs = XDOM::find($doc, 'form :radio:unchecked');
        $this->assertEquals(1, $inputs->length);
        $this->assertIsNode($inputs, 'input');
        $this->assertAttrValue('radio', $inputs->item(0), 'type');
        $this->assertAttrValue(null, $inputs->item(0), 'checked');

        $inputs = XDOM::find($doc, 'form :checked');
        $this->assertEquals(4, $inputs->length);
        $this->assertIsNode($inputs, ['input', 'input', 'option', 'option']);

        $inputs = XDOM::find($doc, 'form :selected');
        $this->assertEquals(2, $inputs->length);
        $this->assertIsNode($inputs, ['option', 'option']);
        $this->assertAttrValue('selected', $inputs->item(0), 'selected');
        $this->assertAttrValue('selected', $inputs->item(1), 'selected');

        $inputs = XDOM::find($doc, 'form option:unselected');
        $this->assertEquals(2, $inputs->length);
        $this->assertIsNode($inputs, ['option', 'option']);
        $this->assertAttrValue(null, $inputs->item(0), 'selected');
        $this->assertAttrValue(null, $inputs->item(1), 'selected');
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
