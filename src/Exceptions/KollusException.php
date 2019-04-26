<?php

namespace Clesson\Kollus\Exceptions;

class KollusException extends \Exception
{
    public function __construct( $message = '', $code = 0, \Exception $previous = null )
    {
        if ( $message instanceof \Exception ) {
            parent::__construct( $message->getMessage(), $code ?: $message->getCode(), $previous ?: $message );
        } else {
            parent::__construct( $message, $code, $previous );
        }
    }
}