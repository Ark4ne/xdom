<?php

namespace XDOM\Exceptions;

use LogicException;
use Throwable;

/**
 * Class UnknownException
 *
 * @package XDOM\Exceptions
 */
class UnknownSelectorException extends LogicException
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct("Unknown selector $message", $code, $previous);
    }
}
