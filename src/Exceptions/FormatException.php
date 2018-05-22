<?php

namespace XDOM\Exceptions;

use LogicException;
use Throwable;

/**
 * Class FormatException
 *
 * @package XDOM\Exceptions
 */
class FormatException extends LogicException
{

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct("Wrong format for $message", $code, $previous);
    }
}
