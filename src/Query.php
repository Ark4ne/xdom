<?php

namespace XDOM;

use XDOM\Exceptions\Exception;

/**
 * Class Query
 *
 * @package XDOM
 */
class Query
{

    public static function query(\DOMNode $node, string $query): ?\DOMNodeList
    {
        $xquery = Parser::parse($query);

        if (empty($xquery)) {
            return new \DOMNodeList();
        }

        if ($node instanceof \DOMDocument) {
            $xpath = new \DOMXPath($node);
        } else {
            $xpath = new \DOMXPath($node->ownerDocument);
            $xquery = $node->getNodePath() . $xquery;
        }

        $return = @$xpath->query($xquery);

        if (false === $return) {
            throw new Exception("Wrong convertion : '$query' => '$xquery'");
        }

        return $return;
    }
}
