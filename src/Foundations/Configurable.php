<?php

namespace Clesson\Kollus\Foundations;

trait Configurable
{
    public function setConfig( $config = [] )
    {
        $this->config = array_merge( (array) $this->config, (array) $config );
    }

    public function array_refine( array $array, array $value, $keep = true )
    {
        return array_reduce( array_keys( $array ), function ( $res, $idx ) use ( $array, $value, $keep ) {
            if ( $keep || ( isset( $value[$idx] ) && $value[$idx] !== null ) ) {
                $res[$idx] = @$value[$idx] ?: @$array[$idx];
            }
            return $res;
        } );
    }
}