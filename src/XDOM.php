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
    /**
     * @param \DOMNode|\DOMNodeList $node
     * @param string                $query
     *
     * @return \DOMNodeList
     * @throws \XDOM\Exceptions\Exception
     * @throws \XDOM\Exceptions\FormatException
     */
    public static function find($node, string $query): \DOMNodeList
    {
        if ($node instanceof \DOMDocument) {
            $xpath = new \DOMXPath($node);
            $xquery = Parser::parse($query);
        } elseif ($node instanceof \DOMNode) {
            $xpath = new \DOMXPath($node->ownerDocument);
            $xquery = Parser::parse($query, $node->getNodePath());
        } elseif ($node instanceof \DOMNodeList) {
            switch ($node->length){
                case 0:
                    return new \DOMNodeList();
                case 1:
                    $node = $node->item(0);
                    $xpath = new \DOMXPath($node->ownerDocument);
                    $xquery = Parser::parse($query, $node->getNodePath());
                    break;
                default:
                    $xpath = new \DOMXPath($node->item(0)->ownerDocument);
                    foreach ($node as $item) {
                        $xqueries[] = Parser::parse($query, $item->getNodePath());
                    }

                    $xquery = '(' . implode(' | ', $xqueries) . ')';
            }
        } else {
            throw new Exception('Wrong argument type.');
        }


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

        if (is_null($nattr)) {
            return $default;
        }

        return $nattr->nodeValue;
    }
}
