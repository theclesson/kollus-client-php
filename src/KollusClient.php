<?php

namespace Clesson\Kollus;

use App\Foundations\Logging;
use Clesson\Kollus\Foundations\Configurable;
use Firebase\JWT\JWT;

class KollusClient
{
    use Configurable, Logging;

    const JWT_PAYLOAD = [
        'cuid'                           => true,
        'awtc'                           => null,
        'video_watermarking_code_policy' => null,
        'expt'                           => 86400,
        'pc_skin'                        => false,
        'mc'                             => [],
    ];

    const MEDIA_CONTENT = [
        'mckey'                         => true,
        'mcpf'                          => null,
        'title'                         => null,
        'intr'                          => false,
        'seek'                          => true,
        'seekable_end'                  => -1,
        'disable_playrate'              => true,
        'thumbnail'                     => null,
        'video_watermaring_code_policy' => null,
    ];

    const VG_QUERY = [
        'pf'                => null,
        'autoplay'          => true,
        'mute'              => null,
        'pc_player_version' => 'v3e',
        'player_version'    => null,
        'uservalue0'        => null, // class_pid
        'uservalue1'        => null, // video_pid
        'uservalue2'        => null, // mem_pid
        'uservalue3'        => null,
        'uservalue4'        => null,
        'uservalue5'        => null,
        'uservalue6'        => null,
        'uservalue7'        => null,
        'uservalue8'        => null,
        'uservalue9'        => null,
    ];

    protected $config;

    public function __construct( $config = [] )
    {
        $this->setConfig( $config );
    }

    public function getPlayUrl( $videos = [], $userId = null, $options = [] )
    {
        $video = @$videos[0]['video_type'] == KollusMedia::TYPE_INTRO && isset( $videos[1] ) ? @$videos[1] : @$videos[0];
        $mode  = @$options['kind'] ?: 's';

        $params                                   = array_refine( static::VG_QUERY, array_merge( $this->config['player_params'], $options ), false );
        $params['title']                          = @$options['title'] ?: @$video['video_title'];
        @$video['play_position'] and $params['t'] = @$video['play_position'];
        $this->debug( $params );

        return $this->getVideoGateWay() . $mode
        . '?jwt=' . $this->makeMediaToken( $videos, $userId, $options )
        . '&custom_key=' . $this->getCustomKey()
        . '&' . http_build_query( $params );
    }

    public function makeMediaToken( $videos = [], $userId = null, $options = [] )
    {
        $payload         = $this->makePayload( $userId, $options );
        $payload['mc']   = $this->makeMedia( $videos, $options );
        $payload['expt'] = time() + $payload['expt'];
        $this->debug( [$this->config, $payload, $options] );
        return JWT::encode( $payload, $this->getSecurityKey() );
    }

    public function makePayload( $userId, $options = [] )
    {
        $payload         = $this->array_refine( static::JWT_PAYLOAD, array_merge( $this->config['player_options'], $options ), false );
        $payload['cuid'] = $userId;
        return $payload;
    }

    public function makeMedia( $videos = [], $options = [] )
    {
        $mc = [];
        foreach ( $videos as $video ) {
            if ( $video && $video['video_key'] ) {
                $mc[] = ( new KollusMedia( $video, array_merge( $this->config['media_options'], $options ) ) )->toArray();
            }
        }
        return $mc;
    }

    public function getVideoGateWay()
    {
        return ( @$_SERVER['REQUEST_SCHEME'] ?: 'http' ) . '://v.' . rtrim( @$this->config['domain'], '/' ) . '/';
    }

    private function getAccountKey()
    {
        return @$this->config['account']['key'];
    }

    private function getAccessToken()
    {
        return @$this->config['account']['api_access_token'];
    }

    private function getCustomKey()
    {
        return @$this->config['account']['custom_key'];
    }

    public function getSecurityKey()
    {
        return @$this->config['account']['security_key'];
    }
}