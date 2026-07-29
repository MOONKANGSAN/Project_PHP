<?php

namespace App\Controllers;

use App\Models\ReactionModel;

/**
 * 추천/비추천 토글 API 컨트롤러
 */
class Reaction extends BaseController
{
    /**
     * 반응 토글 처리
     * POST /api/reaction/toggle
     * Body (JSON): { target_type, target_idx, type }
     * Response: { success, action, like_count, dislike_count, user_reaction }
     */
    public function toggle(): \CodeIgniter\HTTP\ResponseInterface
    {
        // 로그인 체크
        $userIdx = (int) session()->get('user.idx');
        if (!$userIdx) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => '로그인이 필요합니다.',
            ]);
        }

        $json       = $this->request->getJSON(true) ?? [];
        $targetType = $json['target_type'] ?? '';
        $targetIdx  = (int) ($json['target_idx'] ?? 0);
        $type       = $json['type'] ?? '';

        // 허용 값 검증
        if (!in_array($targetType, ['spot', 'restaurant', 'festival'], true)
            || $targetIdx <= 0
            || !in_array($type, ['like', 'dislike'], true)
        ) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => '잘못된 요청입니다.',
            ]);
        }

        $model  = new ReactionModel();
        $result = $model->toggle($userIdx, $targetType, $targetIdx, $type);
        $counts = $model->getCounts($targetType, $targetIdx);

        return $this->response->setJSON([
            'success'       => true,
            'action'        => $result['action'],
            'like_count'    => $counts['like'],
            'dislike_count' => $counts['dislike'],
            'user_reaction' => $result['user_reaction'],
        ]);
    }
}
