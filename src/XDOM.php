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
     * @param \DOMDocument|\DOMElement|\DOMNode[]|\DOMNodeList $node
     * @param string $query
     *
     * @return \DOMNodeList
     * @throws \XDOM\Exceptions\Exception
     * @throws \XDOM\Exceptions\FormatException
     * @throws \InvalidArgumentException
     */
    public static function query($node, string $query): \DOMNodeList
    {
        if ($node instanceof \DOMDocument) {
            $xpath = new \DOMXPath($node);
            $xquery = Parser::parse($query);
        } elseif ($node instanceof \DOMElement) {
            $xpath = new \DOMXPath($node->ownerDocument);
            $xquery = Parser::parse($query, $node->getNodePath());
        } elseif ($node instanceof \DOMNodeList || is_array($node)) {
            $xpath = new \DOMXPath($node[0]->ownerDocument ?? $node[0]);
            foreach ($node as $item) {
                /** @var \DOMElement $item */
                $xqueries[] = Parser::parse($query, $item->getNodePath());
            }

            if (empty($xqueries)) {
                return new \DOMNodeList();
            }

            $xquery = '(' . implode(' | ', $xqueries) . ')';
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

    /** @var \DOMElement[]|\DOMNode[]|\DOMNodeList */
    private $ctx;

    /** @var int */
    private $idx = 0;

    /**
     * XDOM constructor.
     *
     * @param \DOMNode|\DOMNode[]|\DOMNodeList $ctx
     */
    public function __construct($ctx)
    {
        if ($ctx instanceof \DOMNodeList || is_array($ctx)) {
            $this->ctx = $ctx;
        } elseif ($ctx instanceof \DOMElement || $ctx instanceof \DOMDocument) {
            $this->ctx = [$ctx];
        } else {
            throw new \InvalidArgumentException('Wrong arugment type');
        }
    }

    public function find($selector): self
    {
        return new self(XDOM::query($this->ctx, $selector));
    }

    public function filter($selector): self
    {
        if (!$this->count()) {
            return new self([]);
        }

        if ($selector instanceof \Closure) {
            foreach ($this->ctx as $idx => $ctx) {
                if (true === $selector(new self($ctx), $idx)) {
                    $nodes[] = $ctx;
                }
            }
        } else {
            foreach ($this->ctx as $ctx) {
                $xqueries[] = $ctx->getNodePath();
            }

            $xpath = new \DOMXPath($this->ctx[0]->ownerDocument ?? $this->ctx[0]);

            $xquery = Parser::parse('self::' . $selector, '(' . implode('|', $xqueries ?? []) . ')');

            $nodes = $xpath->query($xquery);
        }

        return new self($nodes ?? []);
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

    public function parent(): self
    {
        $nodes = [];

        foreach ($this->ctx as $ctx) {
            $parent = $ctx->parentNode;
            if (!is_null($parent) && !isset($nodes[$path = $parent->getNodePath()])) {
                $nodes[$path] = $parent;
            }
        }

        return new self($nodes);
    }

    public function parents($selector): self
    {
        $nodes = [];

        foreach ($this->ctx as $ctx) {
            if ($ctx instanceof \DOMDocument) {
                continue;
            }

            $xpath = new \DOMXPath($ctx->ownerDocument);

            $xquery = Parser::parse('ancestor::' . $selector);
            $result = @$xpath->query($xquery, $ctx);

            if (false === $result) {
                throw new Exception("Wrong convertion : '$selector' => '$xquery'");
            }

            foreach ($result as $item) {
                $path = $item->getNodePath();
                if (!isset($nodes[$path])) {
                    $nodes[$path] = $item;
                }
            }
        }

        return new self($nodes);
    }

    public function has($selector): bool
    {
        foreach ($this->ctx as $ctx) {
            $xpath = new \DOMXPath($ctx->ownerDocument ?? $ctx);

            $xquery = Parser::parse('descendant::' . $selector);
            $result = @$xpath->query($xquery, $ctx);

            if (false === $result) {
                throw new Exception("Wrong convertion : '$selector' => '$xquery'");
            }

            if ($result->length > 0) {
                return true;
            }
        }

        return false;
    }

    public function is($selector)
    {
        foreach ($this->ctx as $ctx) {
            $xpath = new \DOMXPath($ctx->ownerDocument ?? $ctx);

            $xquery = Parser::parse('self::' . $selector, $ctx->getNodePath());
            $result = @$xpath->query($xquery);

            if (false === $result) {
                throw new Exception("Wrong convertion : '$selector' => '$xquery'");
            }

            if ($result->length > 0) {
                return true;
            }
        }

        return false;
    }

    public function node($idx = 0)
    {
        return $this->ctx[$idx] ?? null;
    }

    public function attr($attr)
    {
        $node = $this->node();

        if (is_null($node)) {
            return null;
        }

        $nattr = $node->attributes->getNamedItem($attr);

        if (is_null($nattr)) {
            return null;
        }

        return $nattr->nodeValue;
    }

    public function value($multiple = false)
    {
        $values = [];

        foreach ($this->ctx as $node) {
            $tagName = $node->tagName;
            if ($tagName == 'select') {
                $select = (new self($node));
                $values[] = $select->find('option:selected')->value($select->attr('multiple') === 'multiple');
            } elseif ($tagName == 'textarea') {
                $values[] = $node->nodeValue;
            }

            $values[] = (new self($node))->attr('value');
        }

        if ($multiple) {
            return $values;
        }

        return $values[0] ?? null;
    }

    public function text()
    {
        $node = $this->node();

        if (is_null($node)) {
            return null;
        }

        return $node->textContent;
    }

    public function html()
    {
        $node = $this->node();

        if (is_null($node)) {
            return null;
        }

        return $node->ownerDocument->saveHTML($node);
    }

    public function innerHtml()
    {
        $node = $this->node();

        if (is_null($node)) {
            return null;
        }

        $html = '';
        $document = $node->ownerDocument;
        foreach ($node->childNodes as $child) {
            $html .= $document->saveHTML($child);
        }

        return $html;
    }

    /**
     * Whether a offset exists
     *
     * @param int $offset
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->ctx[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @param int $offset
     *
     * @return self|null
     */
    public function offsetGet($offset)
    {
        return isset($this->ctx[$offset])
          ? new self($this->ctx[$offset])
          : null;
    }

    /**
     * @param $offset
     * @param $value
     * @return void
     *
     * @throws \LogicException
     */
    public function offsetSet($offset, $value)
    {
        throw new \LogicException("\DomNodeList are immutable.");
    }

    /**
     * @param $offset
     * @return void
     *
     * @throws \LogicException
     */
    public function offsetUnset($offset)
    {
        throw new \LogicException("\DomNodeList are immutable.");
    }


    /**
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return self
     * @since 5.0.0
     */
    public function current()
    {
        return new self($this->ctx[$this->idx]);
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
        $this->idx++;
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
        return $this->idx;
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
        return isset($this->ctx[$this->idx]);
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
        $this->idx = 0;
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
