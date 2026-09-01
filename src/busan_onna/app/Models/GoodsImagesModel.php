<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 굿즈 이미지(goods_images) 모델
 * 상품당 최대 3장 이미지를 sort_order 순으로 관리
 */
class GoodsImagesModel extends Model
{
    protected $table      = 'goods_images';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'goods_idx', 'image_path', 'sort_order', 'state', 'reg_date',
    ];

    /**
     * 특정 상품의 활성 이미지를 sort_order 오름차순으로 반환
     */
    public function getByGoods(int $goodsIdx): array
    {
        return $this->where('goods_idx', $goodsIdx)
                    ->where('state', 1)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * 남은 이미지를 sort_order 1부터 재번호 부여
     */
    public function reorderByGoods(int $goodsIdx): void
    {
        $rows = $this->getByGoods($goodsIdx);
        foreach ($rows as $i => $row) {
            $this->update($row['idx'], ['sort_order' => $i + 1]);
        }
    }

    /**
     * DB 레코드 + 서버 파일을 함께 삭제
     */
    public function deleteWithFile(int $idx): void
    {
        $row = $this->find($idx);
        if (!$row) return;

        $filePath = FCPATH . ltrim($row['image_path'], '/');
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        $this->delete($idx);
    }
}
