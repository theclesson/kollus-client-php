<?php

namespace Clesson\Kollus;

use App\Foundations\Logging;
use Clesson\Kollus\Foundations\Attributable;

class KollusMedia
{
    use Attributable, Logging;

    const TYPE_INTRO   = 'INTRO';
    const TYPE_FREE    = 'FREE';
    const TYPE_REGULAR = 'REGULAR';

    protected $attributes;
    protected $options;

    public function __construct( $attributes = [], $options = [] )
    {
        $this->setAttributes( $attributes );
        $this->options = $options;
    }

    public function toArray()
    {
        $mc['mckey']            = $this->video_key;
        $mc['intr']             = $this->video_type == static::TYPE_INTRO;
        $mc['seek']             = @$this->options['seek'] ?: true;
        $mc['seekable_end']     = @$this->options['seekable_end'] ?: -1;
        $mc['disable_playrate'] = @$this->options['disable_playrate'] ?: true;

        if ( $this->video_type == static::TYPE_REGULAR && $this->video_preview ) {
            $mc['play_section'] = [
                'start_time' => 0,
                'end_time'   => (int) $this->video_preview,
            ];
        }

        isset( $this->options['subtitle_policy'] ) and $mc['subtitle_policy'] = $this->options['subtitle_policy'];

        return $mc;
    }
}