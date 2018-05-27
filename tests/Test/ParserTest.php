<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use XDOM\Parser;

/**
 * Class ParserTest
 *
 * @package Test
 */
class ParserTest extends TestCase
{

    public function dataParseQuery()
    {
        $data = [
            [
                '#mw-content-text > table :not(a)',
                '//*[@id="mw-content-text"]/table//*[not(a)]',
            ],
            [
                '[name]:not(food:nth-child(even))',
                '//*[@name and not(self::food and (position() mod 2 = 0))]',
            ],
            [
                '[name]:not(:nth-child(even))',
                '//*[@name and not((position() mod 2 = 0))]',
            ],
            [
                '[name]:has(food:nth-child(even))',
                '//*[@name and .//*[self::food and (position() mod 2 = 0)]]',
            ],
            [
                '[name]:has(food:nth-child(odd))',
                '//*[@name and .//*[self::food and (position() mod 2 = 1)]]',
            ],
            [
                'tr',
                '//tr',
            ],
            [
                'tr.test',
                '//*[self::tr and contains(concat(" ", normalize-space(@class), " "), " test ")]',
            ],
            [
                '#mw-content-text table',
                '//*[@id="mw-content-text"]//table',
            ],
            [
                '#mw-content-text table.test a',
                '//*[@id="mw-content-text"]//*[self::table and contains(concat(" ", normalize-space(@class), " "), " test ")]//a',
            ],
            [
                '#mw-content-text > table.test > a',
                '//*[@id="mw-content-text"]/*[self::table and contains(concat(" ", normalize-space(@class), " "), " test ")]/a',
            ],
            [
                '#mw-content-text > a :not(span)',
                '//*[@id="mw-content-text"]/a//*[not(span)]',
            ],
            [
                '.foo .bar > [baz]',
                '//*[contains(concat(" ", normalize-space(@class), " "), " foo ")]//*[contains(concat(" ", normalize-space(@class), " "), " bar ")]/*[@baz]',
            ],
            [
                '.foo:nth-child(even)',
                '//*[contains(concat(" ", normalize-space(@class), " "), " foo ") and (position() mod 2 = 0)]',
            ],
            [
                '.foo:nth-child(odd)',
                '//*[contains(concat(" ", normalize-space(@class), " "), " foo ") and (position() mod 2 = 1)]',
            ],
            [
                '.foo:nth-child(3)',
                '//*[contains(concat(" ", normalize-space(@class), " "), " foo ") and (position() mod 3 = 1)]',
            ],
            [
                '#mw-content-text.foo.bar table#gt[attr*="test"].odd',
                '//*[@id="mw-content-text" and contains(concat(" ", normalize-space(@class), " "), " foo ") and contains(concat(" ", normalize-space(@class), " "), " bar ")]//*[self::table and @id="gt" and contains(@attr, "test") and contains(concat(" ", normalize-space(@class), " "), " odd ")]',
            ],
            [
                '.foo:not(a.id.foo)',
                '//*[contains(concat(" ", normalize-space(@class), " "), " foo ") and not(self::a and contains(concat(" ", normalize-space(@class), " "), " id ") and contains(concat(" ", normalize-space(@class), " "), " foo "))]',
            ],
            [
                '.foo:not(:has(a))',
                '//*[contains(concat(" ", normalize-space(@class), " "), " foo ") and not(.//a)]',
            ],
            [
                '.foo:not(:has(a#id))',
                '//*[contains(concat(" ", normalize-space(@class), " "), " foo ") and not(.//*[self::a and @id="id"])]',
            ],
            [
                '.foo:not(:has(a#id)):first',
                '(//*[contains(concat(" ", normalize-space(@class), " "), " foo ") and not(.//*[self::a and @id="id"])])[1]',
            ],
            [
                '.foo:not(:has(a#id)):last',
                '(//*[contains(concat(" ", normalize-space(@class), " "), " foo ") and not(.//*[self::a and @id="id"])])[last()]',
            ],
            [
                '.foo:not(:has(a#id)) :first',
                '(//*[contains(concat(" ", normalize-space(@class), " "), " foo ") and not(.//*[self::a and @id="id"])]//*)[1]',
            ],
            [
                '.foo:not(:has(a#id)) :last',
                '(//*[contains(concat(" ", normalize-space(@class), " "), " foo ") and not(.//*[self::a and @id="id"])]//*)[last()]',
            ],
            [
                '.foo:not(:has(a#id)) > :first',
                '(//*[contains(concat(" ", normalize-space(@class), " "), " foo ") and not(.//*[self::a and @id="id"])]/*)[1]',
            ],
            [
                '.foo:not(:has(a#id)) > :last',
                '(//*[contains(concat(" ", normalize-space(@class), " "), " foo ") and not(.//*[self::a and @id="id"])]/*)[last()]',
            ],
            [
                '.foo:first > :not(:has(a#id))',
                '(//*[contains(concat(" ", normalize-space(@class), " "), " foo ")])[1]/*[not(.//*[self::a and @id="id"])]',
            ],
            [
                '.foo:not(:has(a > b))',
                '//*[contains(concat(" ", normalize-space(@class), " "), " foo ") and not(.//a/b)]',
            ],
            [
                'main span:first-child',
                '//main//*[self::span and (position() = 1)]',
            ],
            [
                'main ~ span:first-child',
                '//main/following-sibling::*[self::span and (position() = 1)]',
            ],
            [
                'main ~ span:first',
                '(//main/following-sibling::span)[1]',
            ],
            [
                'main ~ *:first',
                '(//main/following-sibling::*)[1]',
            ],
            [
                'main + span:first-child',
                '//main/following-sibling::*[self::span and (position() = 1) and position() = 1]',
            ],
            [
                'main + span:first',
                '(//main/following-sibling::span[position() = 1])[1]',
            ],
            [
                'main + *:first',
                '(//main/following-sibling::*[position() = 1])[1]',
            ],
            [
                'a:not(:has(span)):first',
                '(//*[self::a and not(.//span)])[1]',
            ],
            [
                'section .text:first-child',
                '//section//*[contains(concat(" ", normalize-space(@class), " "), " text ") and (position() = 1)]',
            ],
            [
                'section .text:first',
                '(//section//*[contains(concat(" ", normalize-space(@class), " "), " text ")])[1]',
            ],
            [
                '.text:not(.bold)',
                '//*[contains(concat(" ", normalize-space(@class), " "), " text ") and not(contains(concat(" ", normalize-space(@class), " "), " bold "))]'
            ],
            [
                'span, a',
                '(//span|//a)'
            ],
            [
                'span:first, a',
                '((//span)[1]|//a)'
            ],
            [
                'span:first, a:not(:contains(text))',
                '((//span)[1]|//*[self::a and not(contains(text(), "text"))])'
            ],
        ];

        $_ = [];

        foreach ($data as $datum) {
            $_[$datum[0]] = $datum;
        }

        return $_;
    }

    /**
     * @dataProvider dataParseQuery
     */
    public function testParseQuery($query, $expected)
    {
        $xpath = Parser::parse($query);

        $this->assertEquals($expected, $xpath);

        $this->assertInstanceOf(\DOMNodeList::class, (new \DOMXPath(new \DOMDocument()))->query($xpath));
    }
}
