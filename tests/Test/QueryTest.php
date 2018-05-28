<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use XDOM\Wrap;
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
<body class="body-class" main>
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
        <input type="button" value="button"/>
        <input type="checkbox" value="checkbox"/>
        <input type="checkbox" value="checkbox_checked" checked/>
        <input type="file" value="file"/>
        <input type="password" value="password"/>
        <input type="radio" value="radio"/>
        <input type="radio" value="radio_checked" checked/>
        <input type="submit" value="submit"/>
        <input type="text" value="text"/>
        <input type="reset" value="reset"/>
        <select>
            <option value="opt_1">opt_1</option>
            <option value="opt_2">opt_2</option>
            <option value="opt_3" selected>opt_3</option>
            <option value="opt_4" selected>opt_4</option>
        </select>
        <select multiple>
            <option value="opt_1">opt_1</option>
            <option value="opt_2">opt_2</option>
            <option value="opt_3" selected>opt_3</option>
            <option value="opt_4" selected>opt_4</option>
        </select>
        <textarea></textarea>
        <button id="btn_1" value="button"></button>
        <button type="submit" id="btn_2" value="button_submit"></button>
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
    
    <attr attr="attr"></attr>
    <attr attr="attr-section-1"></attr>
    <attr attr="attr-section-2"></attr>
    <attr attr="attr-section-3"></attr>
    <attr attr="attr2"></attr>
    <attr attr="attr2-section-1"></attr>
    <attr attr="attr2-section-2"></attr>
    <attr attr="attr2-section-3"></attr>
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

        $nodes = XDOM::query($doc, 'section');
        $this->assertEquals(2, $nodes->length);
        $this->assertIsNode($nodes, 'section');

        $nodes = XDOM::query($doc, 'span');
        $this->assertEquals(14, $nodes->length);
        $this->assertIsNode($nodes, 'span');

        $nodes = XDOM::query($doc, 'section > span');
        $this->assertEquals(12, $nodes->length);

        $nodes = XDOM::query($doc, 'textarea ~ button');
        $this->assertEquals(2, $nodes->length);
        $this->assertIsNode($nodes, 'button');

        $nodes = XDOM::query($doc, 'input + select');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'select');

        $nodes = XDOM::query($doc, 'div + i');
        $this->assertEquals(3, $nodes->length);
        $this->assertIsNode($nodes, 'i');
        $this->assertAttrValue("i-1-1", $nodes->item(0), 'class');
        $this->assertAttrValue("i-2-1", $nodes->item(1), 'class');
        $this->assertAttrValue("i-3-1", $nodes->item(2), 'class');

        $nodes = XDOM::query($doc, 'div ~ i');
        $this->assertEquals(8, $nodes->length);
        $this->assertIsNode($nodes, 'i');

        $nodes = XDOM::query($doc, 'nav');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'nav');

        $nodes = XDOM::query($doc, 'body > nav');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'nav');

        $nodes = XDOM::query($doc, 'article');
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

        $nodes = XDOM::query($doc, ':has(body)');
        $this->assertEquals(1, $nodes->length);

        $nodes = XDOM::query($doc, 'section');
        $this->assertEquals(2, $nodes->length);
        $this->assertIsNode($nodes, 'section');

        $sections = XDOM::query($doc, 'section:has([index="1"])');
        $this->assertEquals(2, $nodes->length);
        $this->assertIsNode($nodes, 'section');

        $nodes = XDOM::query($sections, ':has([index="1"])');
        $this->assertEquals(0, $nodes->length);

        $nodes = XDOM::query($sections, '[index="1"]');
        $this->assertEquals(2, $nodes->length);
        $this->assertIsNode($nodes, 'span');

        $nodes = XDOM::query($doc, 'section:first-child');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'section');
        $this->assertAttrValue("1", $nodes, 'id');

        $nodes = XDOM::query($doc, '[index]');
        $this->assertEquals(12, $nodes->length);
        $this->assertIsNode($nodes, 'span');

        $nodes = XDOM::query($doc, '.text.bold');
        $this->assertEquals(6, $nodes->length);
        $this->assertIsNode($nodes, 'span');

        $nodes = XDOM::query($doc, 'section:first-child .text.bold');
        $this->assertEquals(3, $nodes->length);
        $this->assertIsNode($nodes, 'span');

        $nodes = XDOM::query($doc, 'section .text');
        $this->assertEquals(12, $nodes->length);
        $this->assertIsNode($nodes, 'span');

        $nodes = XDOM::query($doc, 'section .text:nth-child(odd)');
        $this->assertEquals(6, $nodes->length);
        $this->assertIsNode($nodes, 'span');
        foreach ($nodes as $idx => $node) {
            $this->assertAttrValue(($idx * 2) % 6, $node, 'index');
        }

        $nodes = XDOM::query($doc, 'section .text:first-child');
        $this->assertEquals(2, $nodes->length);
        $this->assertIsNode($nodes, 'span');
        foreach ($nodes as $node) {
            $this->assertAttrValue(0, $node, 'index');
        }

        $nodes = XDOM::query($doc, '.text:not(.bold)');
        $this->assertEquals(6, $nodes->length);
        $this->assertIsNode($nodes, 'span');
        foreach ($nodes as $idx => $node) {
            $this->assertAttrValue($idx % 3, $node, 'index');
        }

        $nodes = XDOM::query($doc, '.text:not(.bold):not(:contains(S1))');
        $this->assertEquals(3, $nodes->length);
        $this->assertIsNode($nodes, 'span');
        foreach ($nodes as $idx => $node) {
            $this->assertAttrValue($idx, $node, 'index');
            $this->assertStringStartsWith('S2', $node->textContent);
        }

        $nodes = XDOM::query($doc, 'a:not(:has(span))');
        $this->assertEquals(2, $nodes->length);
        $this->assertIsNode($nodes, 'a');
        $this->assertAttrValue('#href_1', $nodes->item(0), 'href');
        $this->assertAttrValue('#href_4', $nodes->item(1), 'href');

        $nodes = XDOM::query($doc, 'a:not(:has(span)):first');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'a');
        $this->assertAttrValue('#href_1', $nodes, 'href');

        $nodes = XDOM::query($doc, 'a:not(:has(span)):last');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'a');
        $this->assertAttrValue('#href_4', $nodes, 'href');

        $nodes = XDOM::query($doc, 'a:has(span) :first');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'span');
        $this->assertTextContent('span_link_1', $nodes);
        $nodes = XDOM::query($doc, 'a:has(span) :last');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'span');
        $this->assertTextContent('span_link_2', $nodes);

        $nodes = XDOM::query($doc, 'a:has(span):first :last');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'span');
        $this->assertTextContent('span_link_1', $nodes);
        $nodes = XDOM::query($doc, 'a:has(span):last :first');
        $this->assertEquals(1, $nodes->length);
        $this->assertIsNode($nodes, 'span');
        $this->assertTextContent('span_link_2', $nodes);

        $nodes = XDOM::query($doc, 'div a, p a');
        $this->assertEquals(4, $nodes->length);
        $this->assertIsNode($nodes, 'a');

        $nodes = XDOM::query($doc, 'div a:not(:has(span)), p a:not(:has(span))');
        $this->assertEquals(2, $nodes->length);
        $this->assertIsNode($nodes, 'a');

        $nodes = XDOM::query($doc, 'div a:first, p a:first');
        $this->assertEquals(2, $nodes->length);
        $this->assertIsNode($nodes, 'a');

        $nodes = XDOM::query($doc, 'div:first');
        $a = XDOM::query($nodes->item(0), 'a');
        $this->assertEquals(2, $a->length);
        $this->assertIsNode($a, 'a');
        $this->assertAttrValue("#href_3", $a->item(0), 'href');
        $this->assertAttrValue("#href_4", $a->item(1), 'href');

        $a = XDOM::query($nodes->item(0), 'a:first');
        $this->assertEquals(1, $a->length);
        $this->assertIsNode($a, 'a');
        $this->assertAttrValue("#href_3", $a->item(0), 'href');

        $a = XDOM::query($nodes->item(0), 'a:last');
        $this->assertEquals(1, $a->length);
        $this->assertIsNode($a, 'a');
        $this->assertAttrValue("#href_4", $a->item(0), 'href');

        $a = XDOM::query($nodes->item(0), '.link');
        $this->assertEquals(1, $a->length);
        $this->assertIsNode($a, 'a');
        $this->assertAttrValue("#href_4", $a->item(0), 'href');

        $a = XDOM::query($nodes->item(0), '> .link');
        $this->assertEquals(1, $a->length);
        $this->assertIsNode($a, 'a');
        $this->assertAttrValue("#href_4", $a->item(0), 'href');

        $a = XDOM::query($nodes->item(0), '> .link, > .btn');
        $this->assertEquals(2, $a->length);
        $this->assertIsNode($a, 'a');
        $this->assertAttrValue("#href_3", $a->item(0), 'href');
        $this->assertAttrValue("#href_4", $a->item(1), 'href');

        $btn = XDOM::query($doc, ':header');
        $this->assertEquals(6, $btn->length);
        $this->assertIsNode($btn, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']);

        $btn = XDOM::query($doc, 'nav:first :header');
        $this->assertEquals(6, $btn->length);
        $this->assertIsNode($btn, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']);

        $btn = XDOM::query($doc, 'nav:first :header:odd');
        $this->assertEquals(3, $btn->length);
        $this->assertIsNode($btn, ['h1', 'h3', 'h5']);

        $btn = XDOM::query($doc, 'form :input');
        $this->assertEquals(16, $btn->length);
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
          'select',
          'textarea',
          'button',
          'button',
        ]);

        $btn = XDOM::query($doc, 'form :button');
        $this->assertEquals(3, $btn->length);
        $this->assertIsNode($btn, ['input', 'button', 'button']);
        $this->assertAttrValue('button', $btn->item(0), 'type');

        $btn = XDOM::query($doc, 'form :input:button');
        $this->assertEquals(3, $btn->length);
        $this->assertIsNode($btn, ['input', 'button', 'button']);
        $this->assertAttrValue('button', $btn->item(0), 'type');

        $btn = XDOM::query($doc, 'form :submit');
        $this->assertEquals(3, $btn->length);
        $this->assertIsNode($btn, ['input', 'button', 'button']);

        foreach (['password', 'file'] as $type) {
            $inputs = XDOM::query($doc, 'form :' . $type);
            $this->assertEquals(1, $inputs->length);
            $this->assertIsNode($inputs, 'input');
            $this->assertAttrValue($type, $inputs, 'type');
        }

        $inputs = XDOM::query($doc, 'form :checkbox');
        $this->assertEquals(2, $inputs->length);
        $this->assertIsNode($inputs, 'input');
        $this->assertAttrValue('checkbox', $inputs->item(0), 'type');
        $this->assertAttrValue('checkbox', $inputs->item(1), 'type');

        $inputs = XDOM::query($doc, 'form :checkbox:checked');
        $this->assertEquals(1, $inputs->length);
        $this->assertIsNode($inputs, 'input');
        $this->assertAttrValue('checkbox', $inputs->item(0), 'type');
        $this->assertAttrValue('checked', $inputs->item(0), 'checked');

        $inputs = XDOM::query($doc, 'form :checkbox:unchecked');
        $this->assertEquals(1, $inputs->length);
        $this->assertIsNode($inputs, 'input');
        $this->assertAttrValue('checkbox', $inputs->item(0), 'type');
        $this->assertAttrValue(null, $inputs->item(0), 'checked');

        $inputs = XDOM::query($doc, 'form :radio:checked');
        $this->assertEquals(1, $inputs->length);
        $this->assertIsNode($inputs, 'input');
        $this->assertAttrValue('radio', $inputs->item(0), 'type');
        $this->assertAttrValue('checked', $inputs->item(0), 'checked');

        $inputs = XDOM::query($doc, 'form :radio:unchecked');
        $this->assertEquals(1, $inputs->length);
        $this->assertIsNode($inputs, 'input');
        $this->assertAttrValue('radio', $inputs->item(0), 'type');
        $this->assertAttrValue(null, $inputs->item(0), 'checked');

        $inputs = XDOM::query($doc, 'form :checked');
        $this->assertEquals(6, $inputs->length);
        $this->assertIsNode($inputs, ['input', 'input', 'option', 'option', 'option', 'option']);

        $inputs = XDOM::query($doc, 'form :selected');
        $this->assertEquals(4, $inputs->length);
        $this->assertIsNode($inputs, ['option', 'option']);
        $this->assertAttrValue('selected', $inputs->item(0), 'selected');
        $this->assertAttrValue('selected', $inputs->item(1), 'selected');
        $this->assertAttrValue('selected', $inputs->item(2), 'selected');
        $this->assertAttrValue('selected', $inputs->item(3), 'selected');

        $inputs = XDOM::query($doc, 'form option:unselected');
        $this->assertEquals(4, $inputs->length);
        $this->assertIsNode($inputs, ['option', 'option']);
        $this->assertAttrValue(null, $inputs->item(0), 'selected');
        $this->assertAttrValue(null, $inputs->item(1), 'selected');
        $this->assertAttrValue(null, $inputs->item(2), 'selected');
        $this->assertAttrValue(null, $inputs->item(3), 'selected');

        $attrs = XDOM::query($doc, '[attr]');
        $this->assertEquals(8, $attrs->length);
        $this->assertIsNode($attrs, 'attr');

        $attrs = XDOM::query($doc, '[attr=attr]');
        $this->assertEquals(1, $attrs->length);
        $this->assertIsNode($attrs, 'attr');

        $attrs = XDOM::query($doc, '[attr^=attr]');
        $this->assertEquals(8, $attrs->length);
        $this->assertIsNode($attrs, 'attr');

        $attrs = XDOM::query($doc, '[attr|=attr]');
        $this->assertEquals(4, $attrs->length);
        $this->assertIsNode($attrs, 'attr');

        $attrs = XDOM::query($doc, '[attr^=attr-section]');
        $this->assertEquals(3, $attrs->length);
        $this->assertIsNode($attrs, 'attr');

        $attrs = XDOM::query($doc, '[attr|=attr-section]');
        $this->assertEquals(3, $attrs->length);
        $this->assertIsNode($attrs, 'attr');

        $attrs = XDOM::query($doc, '[attr$=section-1]');
        $this->assertEquals(2, $attrs->length);
        $this->assertIsNode($attrs, 'attr');
    }

    /**
     * @throws \XDOM\Exceptions\Exception
     */
    public function testQueryNodeList()
    {
        $doc = $this->loadDoc();

        $nodes = XDOM::query($doc, 'section');

        $spans = XDOM::query($nodes, 'span');
        $this->assertEquals(12, $spans->length);
        $this->assertIsNode($spans, 'span');

        $spans = XDOM::query($nodes, 'span:first');
        $this->assertEquals(2, $spans->length);
        $this->assertIsNode($spans, 'span');
    }

    public function testWrapper()
    {
        $doc = $this->loadDoc();
        $xdoc = new XDOM($doc);

        $sections = $xdoc->find('section');

        $this->assertCount(2, $sections);

        $spans = $sections->find('span');

        $this->assertCount(12, $spans);

        foreach ($spans as $span) {
            $this->assertInstanceOf(XDOM::class, $span);
            $this->assertEquals(1, $span->count());
            $this->assertNotNull($span->attr('index'));
        }

        $pspans = $spans->prevSibling();
        $this->assertCount(10, $pspans);
        $nspans = $spans->nextSibling();
        $this->assertCount(10, $nspans);


        $spans = $sections->children();

        $this->assertCount(12, $spans);

        $sections = new XDOM(XDOM::query($doc, 'section'));

        $this->assertCount(2, $sections);

        foreach ($sections as $section) {
            $this->assertCount(6, $section->find('span'));
        }
    }

    public function testFilter()
    {
        $doc = $this->loadDoc();

        $xdom = new XDOM($doc);

        $nodes = $xdom->find('section');
        $this->assertCount(2, $nodes);

        $nodes = $nodes->filter(':last');
        $this->assertCount(1, $nodes);

        $nodes = $xdom->find('section span')->filter(':odd');
        $this->assertCount(6, $nodes);
        foreach ($nodes as $node) {
            $this->assertTrue($node->attr('index') % 2 === 0);
        }

        $nodes = $xdom->find('section span')->filter(':even');
        $this->assertCount(6, $nodes);
        foreach ($nodes as $node) {
            $this->assertTrue($node->attr('index') % 2 === 1);
        }

        $nodes = $xdom->find('section span, div i');
        $this->assertCount(20, $nodes);

        $i = $nodes->filter('i');
        $this->assertCount(8, $i);
        $i = $nodes->filter(function (XDOM $node) {
            return $node->node()->tagName === 'i';
        });
        $this->assertCount(8, $i);

        $div = $i->parent();
        $this->assertCount(3, $div);

        $span = $nodes->filter('span');
        $this->assertCount(12, $span);
        $span = $nodes->filter(function (XDOM $node) {
            return $node->node()->tagName === 'span';
        });
        $this->assertCount(12, $span);

        $sections = $span->parent();
        $this->assertCount(2, $sections);

        $nodes = $span->parents('section');
        $this->assertCount(2, $nodes);

        $nodes = $span->parents('body');
        $this->assertCount(1, $nodes);

        $this->assertTrue($nodes->has('section'));
        $this->assertTrue($nodes->has('span'));
        $this->assertFalse($nodes->has('table'));
        $this->assertFalse($nodes->has('body'));

        $this->assertTrue($nodes->is('body'));
        $this->assertTrue($nodes->is('.body-class'));
        $this->assertTrue($nodes->is('[main]'));
        $this->assertFalse($nodes->is('section'));
    }

    public function testValue()
    {
        $doc =$this->loadDoc();
        $xdom = new XDOM($doc);

        $inputs = $xdom->find(':input');
        $this->assertCount(16, $inputs);

        foreach ($inputs as $input) {
            switch ($input->attr('type')) {
                case 'checkbox':
                case 'radio':
                    $this->assertEquals(
                      $input->attr('type') . ($input->attr('checked') ? '_checked' : ''),
                      $input->value()
                    );
                    break;
                default:
                    if ($input->node()->tagName === 'select') {
                        if ($input->attr('multiple')) {
                            $this->assertEquals(['opt_3', 'opt_4'], $input->value());
                        } else {
                            $this->assertEquals('opt_3', $input->value());
                        }
                    } elseif ($input->node()->tagName === 'button') {
                        $this->assertEquals(
                          'button' . ($input->attr('type') ? '_' . $input->attr('type') : ''),
                          $input->value()
                        );
                    } else {
                        $this->assertEquals($input->attr('type'), $input->value());
                    }
            }
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

        $this->assertEquals($expected, (new XDOM($node))->attr($attr));
    }

}
