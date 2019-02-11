<?php

namespace Clesson\Kollus;

class Kollus
{
    protected $API_URL;
    protected $VGATE_URL;

    protected $config = [];

    public function __construct( array $config = [] )
    {
        $this->setConfig( $config );
    }

    public function setConfig( $config = [] )
    {
        $default = [
            'api_url'      => 'http://api.kr.kollus.com',
            'api_version'  => '0',
            'api_token'    => 'at95fjc0ua8eg9n4', // api access_token
            'vgate_url'    => 'http://v.kr.kollus.com',
            'security_key' => 'clesson-test',
            'custom_key'   => '82819cfe20b8e468eb09c35a74116f80cee94de2784df07d079c896adf542bf6',
            'channel_key'  => '',
        ];

        $this->config    = array_merge( $default, $this->config, is_array( $config ) ? $config : [] );
        $this->API_URL   = $this->config['api_url'];
        $this->VGATE_URL = $this->config['vgate_url'];
    }

    public function setChannel( string $channel_key )
    {
        $this->config['channel_key'] = $channel_key;
    }

}