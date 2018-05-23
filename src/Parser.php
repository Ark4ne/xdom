<?php

namespace XDOM;

use XDOM\Exceptions\Exception;
use XDOM\Exceptions\FormatException;

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
    private const _x_identifier = "(?:\\\\.|[\\w-]|[^\\0-\\xa0])+";

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

    private const x_comma = "@^" . self::_x_whitespace . "*," . self::_x_whitespace . "*@";

    private const x_combinators = "@^" . self::_x_whitespace . "*([>+~]|" . self::_x_whitespace . ")" . self::_x_whitespace . "*@";

    const X_ID = "@^#(" . self::_x_identifier . ")@";

    const X_CLASS = "@^\\.(" . self::_x_identifier . ")@";

    const X_TAG = "@^(" . self::_x_identifier . "|[*])@";

    const X_ATTR = "@^" . self::_x_attributes . "@";

    const X_PSEUDOS = "@^" . self::_x_pseudos . "@";

    const X_CHILD = "@^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\(" . self::_x_whitespace .
    "*(even|odd|(([+-]|)(\\d*)n|)" . self::_x_whitespace . "*(?:([+-]|)" . self::_x_whitespace .
    "*(\\d+)|))" . self::_x_whitespace . "*\\)|)@i";

    const MATCHERS = [
        "TAG"     => self::X_TAG,
        "ID"      => self::X_ID,
        "CLASS"   => self::X_CLASS,
        "ATTR"    => self::X_ATTR,
        "CHILD"   => self::X_CHILD,
        "PSEUDOS" => self::X_PSEUDOS,
    ];

    /**
     * @param array $match
     *
     * @return array
     * @throws \XDOM\Exceptions\FormatException
     */
    private static function tokenizeATTR(array $match)
    {
        $match = array_filter($match);

        if (!isset($match[1])) {
            throw new FormatException($match[0]);
        }

        $match[2] = $match[2] ?? null;
        $match[3] = $match[3] ?? $match[4] ?? $match[5] ?? '';

        return [
            $match[0],
            $match[1],
            $match[2],
            $match[3],
        ];
    }

    /**
     * @param array $match
     *
     * @return array
     * @throws \XDOM\Exceptions\FormatException
     */
    private static function tokenizeCHILD(array $match)
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

        return $match;
    }

    /**
     * @param array $match
     *
     * @return array|null
     * @throws \XDOM\Exceptions\Exception
     * @throws \XDOM\Exceptions\FormatException
     */
    private static function tokenizePSEUDOS(array $match)
    {
        if (preg_match(self::X_CHILD, $match[0])) {
            return null;
        }

        $match = array_filter($match);

        $match[2] = $match[2] ?? null;

        $unquote = !isset($match[6]) ? $match[2] : null;

        if (isset($match[3])) {
            $match[2] = $match[4] ?? $match[5] ?? '';
        } elseif ($unquote && preg_match(self::X_PSEUDOS, $unquote)) {
            $token = self::tokenize($unquote, true);

            if ($token['excess']) {
                $token['excess'] = strpos($unquote, ')', strlen($unquote) - $token['excess']) - strlen($unquote);
                if ($token['excess']) {
                    $match[0] = substr($match[0], 0, $token['excess']);
                    $match[2] = substr($unquote, 0, $token['excess']);
                }
            }
        }

        return array_slice($match, 0, 3);
    }

    /**
     * @param string $selector
     * @param bool   $parseOnly
     *
     * @return array
     * @throws \XDOM\Exceptions\Exception
     * @throws \XDOM\Exceptions\FormatException
     */
    public static function tokenize(string $selector, $parseOnly = false): array
    {
        static $cache;

        if (isset($cache[$selector])) {
            return $parseOnly ? ['tokens' => $cache[$selector], 'excess' => 0] : $cache[$selector];
        }

        $matched = null;
        $groups = [];

        $query = $selector;

        while ($query) {
            if (!$matched || preg_match(self::x_comma, $query, $match)) {
                if (isset($match)) {
                    $q = $query;
                    $query = substr($query, strlen($match[0]));

                    if (empty($query)) {
                        $query = $q;
                    }
                }
                $groups[] = [];
                $token = &$groups[count($groups) - 1];
            }

            $matched = false;

            if (preg_match(self::x_combinators, $query, $match)) {
                $token[] = [
                    'type'    => 'COMBINATOR',
                    'matched' => $matched = array_shift($match),
                    'value'   => trim($match[0]),
                ];

                $query = substr($query, strlen($matched));
            }

            foreach (self::MATCHERS as $type => $expr) {
                if (preg_match($expr, $query, $match)) {
                    switch ($type) {
                        case "ATTR":
                            $match = self::tokenizeATTR($match);
                            break;
                        case "CHILD":
                            $match = self::tokenizeCHILD($match);
                            break;
                        case "PSEUDOS":
                            $match = self::tokenizePSEUDOS($match);
                            break;
                    }

                    $matched = array_shift($match);

                    $token[] = [
                        'value'   => $matched,
                        'type'    => $type,
                        'matched' => $match,
                    ];

                    $query = substr($query, strlen($matched));
                }
            }


            if (empty($matched)) {
                break;
            }
        }

        if ($parseOnly) {
            return ['tokens' => $groups, 'excess' => strlen($query)];
        }

        if (!empty($query)) {
            throw new Exception($query);
        }

        return $cache[$selector] = $groups;
    }

    private static function renderCombinator(array $token)
    {
        switch ($token['value']) {
            case '~':
                return null;
            case '+':
                return '/following-sibling::*';
            case '>':
                return '/*';
            case ' ':
            default:
                return '//*';
        }
    }

    private static function renderTag(array $token)
    {
        return 'self::' . $token['value'];
    }

    private static function renderId(array $token)
    {
        return '@id="' . $token['matched'][0] . '"';
    }

    private static function renderClass(array $token)
    {
        return 'contains(concat(" ", normalize-space(@class), " "), " ' . $token['matched'][0] . ' ")';
    }

    private static function renderAttr(array $token)
    {
        $matched = $token['matched'];

        if (empty($matched[1])) {
            return '@' . $matched[0];
        }

        switch ($matched[1]) {
            case '=':
                return '@' . $matched[0] . '="' . $matched[2] . '"';
            case '!=':
                return '@' . $matched[0] . '!="' . $matched[2] . '"';
            case '*=':
                return 'contains(@' . $matched[0] . ', "' . $matched[2] . '")';
            case '~=':
                return 'contains(concat(" ", @' . $matched[0] . ', " "), " ' . $matched[2] . ' ")';
            case '|=':
                return 'starts-with(@' . $matched[0] . ', "' . $matched[2] . '-")';
            case '^=':
                return 'starts-with(@' . $matched[0] . ', "' . $matched[2] . '")';
            case '$=':
                return 'ends-with(@' . $matched[0] . ', "' . $matched[2] . '")';
        }

        throw new Exception("WTF:" . $matched[1]);
    }

    private static function renderChild(array $token)
    {
        $matched = $token['matched'];

        switch (implode('-', array_filter([$matched[0], $matched[1]]))) {
            case 'only-child':
                return '(count(*)=1)';
            case 'last-child':
                return '(last())';
            case 'first-child':
                return '(position() = 1)';
            case 'nth-child':
                if (empty($matched[3])) {
                    return '(position() mod ' . $matched[4] . ' = 1)';
                }

                return '(position() mod ' . $matched[3] . ' = ' . $matched[4] . ')';
            case 'nth-last-child':
                if (empty($matched[3])) {
                    return '((count() - position()) mod ' . $matched[4] . ' = 1)';
                }

                return '((count() - position()) mod ' . $matched[3] . ' = ' . $matched[4] . ')';
            //case 'only-of-type':
            //case 'nth-of-type':
            //case 'first-of-type':
            //case 'nth-last-of-type':
            //default:
        }

        throw new Exception("");
    }

    private static function renderPseudos(array $token)
    {
        $matched = $token['matched'];

        switch ($matched[0]) {
            case 'not':
                return 'not(' . self::render(self::tokenize($matched[1]), true) . ')';
            case 'has':
                return '.' . self::render(self::tokenize($matched[1]));
            case 'contains':
                return 'contains(text(), "' . $matched[1] . '")';
        }
    }

    private static function render(array $tokens, $boolean = false): string
    {
        foreach ($tokens as $token) {
            if (!isset($token['type'])) {
                $groups[] = self::render($token, $boolean);
                continue;
            }

            if (isset($groups)) {
                var_dump($groups);
                throw new Exception("...");
            }

            switch ($token['type']) {
                case 'COMBINATOR':
                    if (!isset($parts)) {
                        throw new Exception("...");
                    }
                    $xpath = ($xpath ?? '') . '[' . implode(' and ', $parts) . ']' . self::renderCombinator($token);
                    $parts = [];
                    break;
                case 'TAG':
                    $parts[] = self::renderTag($token);
                    break;
                case 'ID':
                    $parts[] = self::renderId($token);
                    break;
                case 'CLASS':
                    $parts[] = self::renderClass($token);
                    break;
                case 'ATTR':
                    $parts[] = self::renderAttr($token);
                    break;
                case 'CHILD':
                    $parts[] = self::renderChild($token);
                    break;
                case 'PSEUDOS':
                    $parts[] = self::renderPseudos($token);
                    break;
                default:
                    throw new Exception('WTF:' . $token['type']);
            }
        }

        if (isset($groups)) {
            if (count($groups) === 1) {
                return $groups[0];
            }

            return '(' . implode('|', $groups) . ')';
        }

        if (empty($xpath)) {
            return ($boolean ? '(' : '//*[') . implode(' and ', $parts) . ($boolean ? ')' : ']');
        }

        $xpath = '//*' . $xpath;

        if (empty($parts)) {
            return $xpath;
        }

        return $xpath . '[' . implode(' and ', $parts ?? []) . ']';
    }

    /**
     * @param string $selector
     *
     * @return string
     * @throws \XDOM\Exceptions\Exception
     * @throws \XDOM\Exceptions\FormatException
     */
    public static function parse(string $selector): string
    {
        return self::render(self::tokenize($selector));
    }
}
