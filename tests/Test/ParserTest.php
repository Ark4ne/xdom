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

    public function data()
    {
        $data = [
          [
            '#mw-content-text > table :not(a)',
            '//*[@id="mw-content-text"]/table//*[not(self::a)]',
          ],
          [
            '[name]:not(food:nth-child(even))',
            '//*[@name][not(self::food[((position() mod 2) = 0)])]',
          ],
          [
            '[name]:not(:nth-child(even))',
            '//*[@name][not(((position() mod 2) = 0))]',
          ],
          [
            '[name]:has(food:nth-child(even))',
            '//*[@name][.//food[((position() mod 2) = 0)]]',
          ],
          [
            '[name]:has(food:nth-child(even))',
            '//*[@name][.//food[((position() mod 2) = 0)]]',
          ],
          [
            'tr',
            '//tr',
          ],
          [
            'tr.test',
            '//tr[contains(concat(" ", normalize-space(@class), " "), " test ")]',
          ],
          [
            '#mw-content-text table',
            '//*[@id="mw-content-text"]//table',
          ],
          [
            '#mw-content-text table.test a',
            '//*[@id="mw-content-text"]//table[contains(concat(" ", normalize-space(@class), " "), " test ")]//a',
          ],
          [
            '#mw-content-text > table.test > a',
            '//*[@id="mw-content-text"]/table[contains(concat(" ", normalize-space(@class), " "), " test ")]/a',
          ],
          [
            '#mw-content-text > a :not(span)',
            '//*[@id="mw-content-text"]/a//*[not(self::span)]',
          ],
          [
            '.foo .bar > [baz]',
            '//*[contains(concat(" ", normalize-space(@class), " "), " foo ")]//*[contains(concat(" ", normalize-space(@class), " "), " bar ")]/*[@baz]',
          ],
          [
            '.foo:nth-child(odd)',
            '//*[contains(concat(" ", normalize-space(@class), " "), " foo ")][((position() mod 2) = 1)]',
          ],
          [
            '.foo:nth-child(even)',
            '//*[contains(concat(" ", normalize-space(@class), " "), " foo ")][((position() mod 2) = 0)]',
          ],
            //exit;
          [
            '#mw-content-text.foo.bar table#gt[attr*="test"].odd',
            '//*[@id="mw-content-text"][contains(concat(" ", normalize-space(@class), " "), " foo ")][contains(concat(" ", normalize-space(@class), " "), " bar ")]//table[@id="gt"][contains(@attr, "test")][contains(concat(" ", normalize-space(@class), " "), " odd ")]',
          ],
          [
            '.foo .bar > [baz]',
            '//*[contains(concat(" ", normalize-space(@class), " "), " foo ")]//*[contains(concat(" ", normalize-space(@class), " "), " bar ")]/*[@baz]',
          ],
          [
            '.foo:nth-child(odd)',
            '//*[contains(concat(" ", normalize-space(@class), " "), " foo ")][((position() mod 2) = 1)]',
          ],
          [
            '.foo:nth-child(even)',
            '//*[contains(concat(" ", normalize-space(@class), " "), " foo ")][((position() mod 2) = 0)]',
          ],
          [
            '.foo:nth-child(3)',
            '//*[contains(concat(" ", normalize-space(@class), " "), " foo ")][(position() mod 3 = 1)]',
          ],
          [
            '.foo:not(a.id.foo)',
            '//*[contains(concat(" ", normalize-space(@class), " "), " foo ")][not(self::a[contains(concat(" ", normalize-space(@class), " "), " id ")][contains(concat(" ", normalize-space(@class), " "), " foo ")])]',
          ],
          [
            '.foo:not(:has(a))',
            '//*[contains(concat(" ", normalize-space(@class), " "), " foo ")][not(.//a)]',
          ],
          [
            '.foo:not(:has(a#id))',
            '//*[contains(concat(" ", normalize-space(@class), " "), " foo ")][not(.//a[@id="id"])]',
          ],
          [
            '.foo:not(:has(a#id)):first',
            '(//*[contains(concat(" ", normalize-space(@class), " "), " foo ")][not(.//a[@id="id"])])[1]',
          ],
          [
            '.foo:not(:has(a#id)):last',
            '(//*[contains(concat(" ", normalize-space(@class), " "), " foo ")][not(.//a[@id="id"])])[last()]',
          ],
          [
            '.foo:not(:has(a#id)) :first',
            '(//*[contains(concat(" ", normalize-space(@class), " "), " foo ")][not(.//a[@id="id"])]//*)[1]',
          ],
          [
            '.foo:not(:has(a#id)) :last',
            '(//*[contains(concat(" ", normalize-space(@class), " "), " foo ")][not(.//a[@id="id"])]//*)[last()]',
          ],
          [
            '.foo:not(:has(a#id)) > :first',
            '(//*[contains(concat(" ", normalize-space(@class), " "), " foo ")][not(.//a[@id="id"])]/*)[1]',
          ],
          [
            '.foo:not(:has(a#id)) > :last',
            '(//*[contains(concat(" ", normalize-space(@class), " "), " foo ")][not(.//a[@id="id"])]/*)[last()]',
          ],
        ];

        $_ = [];

        foreach ($data as $datum) {
            $_[$datum[0]] = $datum;
        }

        return $_;
    }

    /**
     * @dataProvider data
     */
    public function test($query, $expected)
    {
        $this->assertEquals($expected, Parser::parseQuery($query));
    }
}
