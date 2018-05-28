<?php

namespace XDOM;

use XDOM\Exceptions\Exception;

/**
 * Class XDOM
 *
 * @package XDOM
 */
class XDOM implements \Iterator, \Countable, \ArrayAccess
{
    /**
     * @param \DOMNode|\DOMNode[]|\DOMNodeList $node
     * @param string                $query
     *
     * @return \DOMNodeList
     * @throws \XDOM\Exceptions\Exception
     * @throws \XDOM\Exceptions\FormatException
     */
    public static function query($node, string $query): \DOMNodeList
    {
        if ($node instanceof \DOMDocument) {
            $xpath = new \DOMXPath($node);
            $xquery = Parser::parse($query);
        } elseif ($node instanceof \DOMNode) {
            $xpath = new \DOMXPath($node->ownerDocument);
            $xquery = Parser::parse($query, $node->getNodePath());
        } elseif ($node instanceof \DOMNodeList || is_array($node)) {
            if ($node instanceof \DOMNodeList ? $node->length == 0 : empty($node)) {
                return new \DOMNodeList();
            } else {
                $first = $node[0];
                $xpath = new \DOMXPath($first instanceof \DOMDocument ? $first : $first->ownerDocument);
                foreach ($node as $item) {
                    /** @var \DOMNode $item */
                    $xqueries[] = Parser::parse($query, $item->getNodePath());
                }

                $xquery = '(' . implode(' | ', $xqueries) . ')';
            }
        } else {
            throw new \InvalidArgumentException('Wrong argument type.');
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

    /** @var \DOMNode[]|\DOMNodeList */
    private $ctx;

    public function __construct($ctx)
    {
        if ($ctx instanceof \DOMNodeList || is_array($ctx)) {
            $this->ctx = $ctx;
        } elseif ($ctx instanceof \DOMNode) {
            $this->ctx = [$ctx];
        } else {
            throw new \InvalidArgumentException('Wrong arugment type');
        }
    }

    public function find($selector): self
    {
        return new self(XDOM::query($this->ctx, $selector));
    }

    public function attr($attr)
    {
        $node = $this->getFirstNode();

        if (is_null($node)) {
            return null;
        }

        $nattr = $node->attributes->getNamedItem($attr);

        if (is_null($nattr)) {
            return null;
        }

        return $nattr->nodeValue;
    }

    public function value()
    {
        return $this->_value(false);
    }

    private function _value($multiple = false)
    {
        $values = [];

        foreach ($this->ctx as $node) {
            $tagName = $node->tagName;
            if ($tagName == 'select') {
                $select = (new self($node));
                $values[] = $select->find('option:selected')->_value($select->attr('multiple') === 'multiple');
            } elseif ($tagName == 'textarea') {
                $values[] = $node->nodeValue;
            }

            $values[] = $this->attr('value');
        }

        if ($multiple) {
            return $values;
        }

        return $values[0] ?? null;
    }

    public function text()
    {
        $node = $this->getFirstNode();

        if (is_null($node)) {
            return null;
        }

        return $node->textContent;
    }

    public function prevSibling(): self
    {
        $nodes = [];

        foreach ($this->ctx as $ctx) {
            $prev = $ctx->previousSibling;
            while (!is_null($prev) && ($prev instanceof \DOMCharacterData)) {
                $prev = $prev->previousSibling;
            }
            if (!is_null($prev)) {
                $nodes[] = $prev;
            };
        }

        return new self($nodes);
    }


    public function nextSibling(): self
    {
        $nodes = [];

        foreach ($this->ctx as $ctx) {
            $next = $ctx->nextSibling;
            while (!is_null($next) && ($next instanceof \DOMCharacterData)) {
                $next = $next->nextSibling;
            }
            if (!is_null($next)) {
                $nodes[] = $next;
            };
        }

        return new self($nodes);
    }

    public function children(): self
    {
        $nodes = [];

        foreach ($this->ctx as $ctx) {
            foreach ($ctx->childNodes as $childNode) {
                if (!($childNode instanceof \DOMCharacterData)) {
                    $nodes[] = $childNode;
                };
            }
        }

        return new self($nodes);
    }

    private function getFirstNode()
    {
        return $this->ctx instanceof \DOMNodeList ? $this->ctx->item(0) : $this->ctx[0] ?? null;
    }

    /**
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        if ($this->ctx instanceof \DOMNodeList) {
            return $offset < $this->ctx->length;
        }

        return isset($offset[0]);
    }

    /**
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     *
     * @return self|null Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        if ($this->ctx instanceof \DOMNodeList) {
            $node = $this->ctx->item($offset);

            return is_null($node) ? null : new self($node);
        }

        return new self($this->getFirstNode());
    }

    /**
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        throw new \LogicException("\DomNodeList are immutable.");
    }

    /**
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        throw new \LogicException("\DomNodeList are immutable.");
    }


    /**
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return new self(current($this->ctx));
    }

    /**
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        next($this->ctx);
    }

    /**
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return key($this->ctx);
    }

    /**
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return !is_null(key($this->ctx));
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        reset($this->ctx);
    }

    /**
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        if ($this->ctx instanceof \DOMNodeList) {
            return $this->ctx->length;
        }

        return count($this->ctx);
    }
}
