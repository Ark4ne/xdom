<?php

namespace XDOM;

use XDOM\Exceptions\Exception;

/**
 * Class XDOM
 *
 * @package XDOM
 */
class XDOM
{
    public static function find(\DOMNode $node, string $query): \DOMNodeList
    {
        if ($node instanceof \DOMDocument) {
            $xpath = new \DOMXPath($node);
            $xquery = null;
        } else {
            $xpath = new \DOMXPath($node->ownerDocument);
            $xquery = $node->getNodePath();
        }

        $xquery = Parser::parse($query, $xquery);

        if (empty($xquery)) {
            return new \DOMNodeList();
        }

        $return = @$xpath->query($xquery);

        if (false === $return) {
            throw new Exception("Wrong convertion : '$query' => '$xquery'");
        }

        return $return;
    }

    public static function attr(\DOMNode $node, $attr, $default = null)
    {
        $nattr = $node->attributes->getNamedItem($attr);

        if (is_null($attr)) {
            return $default;
        }

        return $nattr->nodeValue;
    }
}
