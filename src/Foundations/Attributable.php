<?php

namespace Clesson\Kollus\Foundations;

trait Attributable
{
    public function setAttributes( $attributes )
    {
        $this->attributes = collect( $attributes );
    }

    public function __get( $name )
    {
        return isset( $this->attributes ) ? $this->attributes->get( $name ) : null;
    }

    public function __set( $name, $value )
    {
        isset( $this->attributes ) ? $this->attributes->put( $name, $value ) : $this->$name = $value;
    }

}