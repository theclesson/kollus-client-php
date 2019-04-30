<?php

namespace Clesson\Kollus;

use Clesson\Kollus\Foundations\Configurable;
use Firebase\JWT\JWT;

class KollusClient
{
    use Configurable;

    const PLAYER_PC = 'V3h';

    const JWT_PAYLOAD = [
        'cuid'                           => true,
        'awtc'                           => null,
        'video_watermarking_code_policy' => null,
        'expt'                           => null,
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
        'pc_player_version' => null,
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

    protected $logger;
    protected $config;

    public function __construct( $config = [], $logger = null )
    {
        $this->logger = $logger;
        $this->setConfig( $config );
        $this->debug( $this->config );
    }

    public function getPlayUrl( $videos = [], $userId = null, $options = [] )
    {
        $video = @$videos[1] ?: $videos[0];
        $mode  = @$options['kind'] ?: 's';

        $params = array_refine( static::VG_QUERY, array_merge( $this->config['player_params'], $options ), false );

        if ( isset( $video['video_type'] ) && $video['video_type'] == KollusMedia::TYPE_REGULAR ) {
            $params['pc_player_version'] = static::PLAYER_PC;
            $params['player_version']    = $params['pc_player_version'];
        }

        if ( $title = ( @$options['title'] ?: @$video['video_title'] ) ) {
            $params['title'] = $title;
        }

        if ( isset( $video['play_position'] ) && $video['play_position'] ) {
            $params['t'] = $video['play_position'];
        }

        $this->debug( $params, $options );

        return $this->getVideoGateWay( $mode )
        . '?jwt=' . $this->makeMediaToken( $videos, $userId, $options )
        . '&custom_key=' . $this->getCustomKey()
        . '&' . http_build_query( $params );
    }

    public function makeMediaToken( $videos = [], $userId = null, $options = [] )
    {
        $payload         = $this->makePayload( $userId, $options );
        $payload['mc']   = $this->makeMedia( $videos, $options );
        $payload['expt'] = time() + $payload['expt'];
        $this->debug( $payload, $options );

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

    public function getVideoGateWay( $mode )
    {
        return ( @$_SERVER['REQUEST_SCHEME'] ?: 'http' ) . '://v.' . rtrim( @$this->config['domain'], '/' ) . "/{$mode}";
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

    public function debug( ...$params )
    {
        $this->logger && $this->logger::debug( static::class . ' ' . print_r( $params, 1 ) );
    }
}