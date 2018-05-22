<?php

namespace XDOM;

use XDOM\Exceptions\FormatException;
use XDOM\Exceptions\UnknownPseudoException;
use XDOM\Exceptions\UnknownSelectorException;

/**
 * Class Parser
 *
 * @package XDOM
 */
class Parser
{

    // http://www.w3.org/TR/css3-selectors/#whitespace
    private const _x_whitespace = "[\\x20\\t\\r\\n\\f]";

    // http://www.w3.org/TR/CSS21/syndata.html#value-def-identifier
    private const _x_identifier = "(?:\\\\.|[\\w-])+";

    // Attribute selectors: http://www.w3.org/TR/selectors/#attribute-selectors
    private const _x_attributes = "\\[" . self::_x_whitespace . "*(" . self::_x_identifier . ")(?:" . self::_x_whitespace .
    // Operator (capture 2)
    "*([*^$|!~]?=)" . self::_x_whitespace .
    // "Attribute values must be CSS identifiers [capture 5] or strings [capture 3 or capture 4]"
    "*(?:'((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\"|(" . self::_x_identifier . "))|)" . self::_x_whitespace .
    "*\\]";

    private const _x_pseudos = ":(" . self::_x_identifier . ")(?:\\((" .
    // To reduce the number of selectors needing tokenize in the preFilter, prefer arguments:
    // 1. quoted (capture 3; capture 4 or capture 5)
    "('((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\")|" .
    // 2. simple (capture 6)
    "((?:\\\\.|[^\\\\()[\\]]|" . self::_x_attributes . ")*)|" .
    // 3. anything else (capture 2)
    ".*" .
    ")\\)|)";


    const X_AXES = "@^\s*( |\~|\>|\+)@";

    const X_ID = "@^#(" . self::_x_identifier . ")@";

    const X_CLASS = "@^\\.(" . self::_x_identifier . ")@";

    const X_TAG = "@^(" . self::_x_identifier . "|[*])@";

    const X_ATTR = "@^" . self::_x_attributes . "@";

    const X_PSEUDOS = "@^" . self::_x_pseudos . "@";

    const X_CHILD = "@^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\(" . self::_x_whitespace .
    "*(even|odd|(([+-]|)(\\d*)n|)" . self::_x_whitespace . "*(?:([+-]|)" . self::_x_whitespace .
    "*(\\d+)|))" . self::_x_whitespace . "*\\)|)@i";

    const EXPR = [
      "AXES" => self::X_AXES,
      "TAG" => self::X_TAG,
      "ID" => self::X_ID,
      "CLASS" => self::X_CLASS,
      "ATTR" => self::X_ATTR,
      "CHILD" => self::X_CHILD,
      "PSEUDOS" => self::X_PSEUDOS,
    ];

    private static function parseXAXES(array $match): ?array
    {
        switch ($match[1] ?? null) {
            case ' ':
                return ['//', ''];
            case '>':
                return ['/', ''];
            case '~':
                return ['following-sibling::', ''];
            case '+':
                return ['following-sibling::', '[1]'];
        }

        throw new FormatException($match[0]);
    }

    private static function parseXTAG(array $match): ?array
    {
        if (isset($match[1])) {
            return ['', $match[1], ''];
        }
        throw new FormatException($match[0]);
    }

    private static function parseXID(array $match): ?array
    {
        if (isset($match[1])) {
            return ['[', '@id="' . $match[1] . '"', ']'];
        }
        throw new FormatException($match[0]);
    }

    private static function parseXCLASS(array $match): ?array
    {
        if (isset($match[1])) {
            return ['[', 'contains(concat(" ", normalize-space(@class), " "), " ' . $match[1] . ' ")', ']'];
        }
        throw new FormatException($match[0]);
    }

    private static function parseXATTR(array $match): ?array
    {
        if (!isset($match[1])) {
            throw new FormatException($match[0]);
        }
        if (!isset($match[2])) {
            return ['[', '@' . $match[1], ']'];
        }
        switch ($match[2]) {
            case '=':
                return ['[', '@' . $match[1] . '="' . $match[4] . '"', ']'];
            case '*=':
                return ['[', 'contains(@' . $match[1] . ', "' . $match[4] . '")', ']'];
            case '^=':
                return ['[', 'starts-with(@' . $match[1] . ', "' . $match[4] . '")', ']'];
            case '$=':
                return ['[', 'ends-with(@' . $match[1] . ', "' . $match[4] . '")', ']'];
        }
        return null;
    }

    private static function parseXCHILD(array $match): ?array
    {
        if (substr($match[1], 0, 3) === 'nth') {
            if (empty($match[3])) {
                throw new FormatException($match[0]);
            }

            $match[4] = +(
              $match[4] ?? null
                ? intval($match[5]) + intval(empty($match[6]) ? 1 : $match[6])
                : 2 * ($match[3] === 'even' || $match[3] === 'odd')
            );

            $match[5] = +(
            empty(intval($match[7] ?? 0) + intval($match[8] ?? 0))
              ? $match[3] === 'odd'
              : intval($match[7] ?? 0) + intval($match[8] ?? 0)
            );

        } elseif (empty($match[2])) {
            throw new FormatException($match[0]);
        }

        switch (implode('-', array_filter([$match[1], $match[2]]))) {
            case 'only-child':
                return ['[', 'count(*)=1', ']'];
            case 'last-child':
                return ['[', 'last()', ']'];
            case 'first-child':
                return ['[', '(position() = 1)', ']'];
            case 'nth-child':
                if (empty($match[4])) {
                    return ['[', '(position() mod ' . $match[5] . ' = 1)', ']'];
                }
                return ['[', '((position() mod ' . $match[4] . ') = ' . $match[5] . ')', ']'];
            case 'nth-last-child':
                if (empty($match[4])) {
                    return ['[', '((count() - position()) mod ' . $match[5] . ' = 1)', ']'];
                }
                return ['[', '(((count() - position()) mod ' . $match[4] . ') = ' . $match[5] . ')', ']'];
            case 'only-of-type':
            case 'nth-of-type':
            case 'first-of-type':
            case 'nth-last-of-type':
            default:
                throw new UnknownPseudoException($match[0]);
        }
    }

    private static function parseXPSEUDOS(array $match): ?array
    {
        switch ($match[1]) {
            case 'parent':
                return ['', '..', ''];
            case 'contains':
                return ['', 'contains(@text, "' . $match[2] . '")', ''];
            case 'not':
                return ['[', 'not', ']', 'expr' => self::extractParts($match[2])];
            case 'has':
                return ['[', '.' . self::parseQuery($match[2]), ']'];
            case 'first':
                return ['', ')[1]', '', 'global_starts' => '('];
            case 'last':
                return ['', ')[last()]', '', 'global_starts' => '('];
            case 'even':
                return ['', ')[(position() mod 2 = 0)]', '', 'global_starts' => '('];
            case 'odd':
                return ['', ')[(position() mod 2 = 1)]', '', 'global_starts' => '('];
        }

        throw new UnknownPseudoException($match[1]);
    }

    private static function extractParts(string $part): array
    {
        foreach (self::EXPR as $type => $expr) {
            if (preg_match($expr, $part, $match)) {
                switch ($type) {
                    case 'AXES':
                        $xpart = self::parseXAXES($match);
                        $next = trim(substr($part, strlen($match[0])));
                        break;
                    case 'TAG':
                        $xpart = self::parseXTAG($match);
                        break;
                    case 'ID':
                        $xpart = self::parseXID($match);
                        break;
                    case 'CLASS':
                        $xpart = self::parseXCLASS($match);
                        break;
                    case 'ATTR':
                        $xpart = self::parseXATTR($match);
                        break;
                    case 'CHILD':
                        $xpart = self::parseXCHILD($match);
                        break;
                    case 'PSEUDOS':
                        $xpart = self::parseXPSEUDOS($match);
                }

                if (empty($xpart)) {
                    die($match[0]);
                }

                $xpath = ['type' => $type, 'part' => $xpart];

                if ($type !== 'AXES') {
                    $next = substr($part, strlen($match[0]));
                }

                if (!empty($next)) {
                    $xpath = array_merge([$xpath], self::extractParts($next));
                    if ($type === 'AXES') {
                        return [$xpath];
                    } else {
                        return $xpath;
                    }
                }

                return [$xpath];
            }
        }

        throw new UnknownSelectorException($part);
    }

    private static function renderParts(array $xparts, $type = null, $_ = 0): array
    {
        $global_starts = '';
        $sub = '';
        $parts = [];
        $prefix = '//';
        $suffix = '';
        $tag = '*';

        if ($type == 'PSEUDOS') {
            $prefix = '';
            $suffix = '';
            $tag = '';
        }

        foreach ($xparts as $xpart) {
            if (!isset($xpart['type'])) {
                $x = self::renderParts($xpart, $type, $_ + 1);
                $sub .= $x['x'];
                $global_starts .= $x['gs'];
                continue;
            }

            if (isset($xpart['part']['prefix'])) {
                $prefix = $xpart['part']['prefix'];
            }
            if (isset($xpart['part']['suffix'])) {
                $prefix = $xpart['part']['suffix'];
            }
            if (isset($xpart['part']['global_starts'])) {
                $global_starts .= $xpart['part']['global_starts'];
            }

            switch ($xpart['type']) {
                case 'AXES':
                    if ($type == 'PSEUDOS') {
                        die('wrong');
                    }
                    $prefix = $xpart['part'][0];
                    $suffix = $xpart['part'][1];
                    break;
                case 'TAG':
                    if ($type == 'PSEUDOS') {
                        $prefix = 'self::';
                        $type = null;
                    }
                    $tag = $xpart['part'][1];
                    break;
                case 'ID':
                case 'CLASS':
                case 'ATTR':
                case 'CHILD':
                    if ($type == 'PSEUDOS') {
                        $parts[] = $xpart['part'][1];
                    } else {
                        $parts[] = $xpart['part'][0] . $xpart['part'][1] . $xpart['part'][2];
                    }
                    break;
                case 'PSEUDOS':
                    if (isset($xpart['part']['expr'])) {
                        $x = self::renderParts($xpart['part']['expr'], 'PSEUDOS', 0);
                        $parts[] =
                          $x['gs'] .
                          $xpart['part'][0] .
                          $xpart['part'][1] .
                          '(' . $x['x'] . ')' .
                          $xpart['part'][2];
                    } elseif ($type == 'PSEUDOS') {
                        $parts[] = $xpart['part'][1];
                    } else {
                        $parts[] = $xpart['part'][0] . $xpart['part'][1] . $xpart['part'][2];
                    }
                    break;
            }
        }

        return ['x' => ($_ === 0 ? $global_starts : '') . $prefix . $tag . implode('', $parts) . $suffix . $sub, 'gs' => $global_starts];
    }

    public static function parseQuery(string $query): string
    {
        return self::renderParts(self::extractParts($query))['x'];
    }
}
