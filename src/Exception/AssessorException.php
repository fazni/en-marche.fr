<?php

namespace AppBundle\Exception;

class AssessorException extends \RuntimeException
{
    public function __construct($message = '', \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
