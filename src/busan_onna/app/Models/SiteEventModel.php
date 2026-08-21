<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 부산온나 자체 이벤트 모델
 */
class SiteEventModel extends Model
{
    protected $table      = 'site_event';
    protected $primaryKey = 'idx';

    protected $allowedFields = [
        'state', 'use_view_file',
        'title', 'sub_title', 'content', 'cta_text', 'cta_url',
        'thumb_url', 'view_file',
        'event_type', 'start_date', 'end_date',
        'reg_date', 'edit_date', 'view_cnt', 'like_cnt', 'reg_id',
    ];

    protected $useTimestamps = false;

    // 이벤트 유형 레이블
    public const TYPES = [
        1 => '방문인증',
        2 => '투표',
        3 => '공모전',
        4 => '기타',
    ];

    // 이벤트 유형별 이모지
    public const TYPE_EMOJI = [
        1 => '📍',
        2 => '🗳️',
        3 => '🏆',
        4 => '🎉',
    ];

    // 이벤트 유형별 배지 색상
    public const TYPE_COLOR = [
        1 => '#0984e3',
        2 => '#6c5ce7',
        3 => '#e17055',
        4 => '#fdcb6e',
    ];
}
