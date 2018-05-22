<?php

namespace XDOM\Exceptions;

use LogicException;
use Throwable;

/**
 * Class UnknownPseudoException
 *
 * @package XDOM\Exceptions
 */
class UnknownPseudoException extends LogicException
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct("Unknown pseudo $message", $code, $previous);
    }
}
