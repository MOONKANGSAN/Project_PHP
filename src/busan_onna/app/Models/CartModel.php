<?php
namespace App\Models;
use CodeIgniter\Model;

/**
 * 장바구니 모델
 */
class CartModel extends Model
{
    protected $table      = 'cart';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = ['user_idx', 'goods_idx', 'option_value_idx', 'quantity'];

    /**
     * 사용자 장바구니 전체 조회 (상품·옵션 JOIN)
     */
    public function getCartItems(int $userIdx): array
    {
        return $this->db->table('cart c')
            ->select('c.idx, c.goods_idx, c.option_value_idx, c.quantity,
                      g.name AS goods_name, g.price, g.thumbnail, g.stock,
                      g.delivery_type,
                      gov.value AS option_value, gov.additional_price,
                      go.option_name')
            ->join('goods g', 'g.idx = c.goods_idx')
            ->join('goods_option_values gov', 'gov.idx = c.option_value_idx', 'left')
            ->join('goods_options go', 'go.idx = gov.option_idx', 'left')
            ->where('c.user_idx', $userIdx)
            ->where('g.state', 1)
            ->orderBy('c.idx', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * 동일 상품+옵션 조합이 있으면 수량 증가, 없으면 신규 삽입 — cart idx 반환
     */
    public function addOrIncrement(int $userIdx, int $goodsIdx, ?int $optionValueIdx, int $qty): int
    {
        /* option_value_idx = null 일 때 'col = NULL'(항상 false) 방지 — IS NULL 사용 */
        $query = $this->where('user_idx', $userIdx)->where('goods_idx', $goodsIdx);
        if ($optionValueIdx === null) {
            $query = $query->where('option_value_idx IS NULL', null, false);
        } else {
            $query = $query->where('option_value_idx', $optionValueIdx);
        }
        $existing = $query->first();

        if ($existing) {
            $this->update($existing['idx'], ['quantity' => $existing['quantity'] + $qty]);
            return (int) $existing['idx'];
        } else {
            $this->insert([
                'user_idx'         => $userIdx,
                'goods_idx'        => $goodsIdx,
                'option_value_idx' => $optionValueIdx,
                'quantity'         => $qty,
            ]);
            return (int) $this->getInsertID();
        }
    }

    /**
     * 사용자의 장바구니 전체 삭제 (결제 완료 후 호출)
     */
    public function clearByUser(int $userIdx): void
    {
        $this->where('user_idx', $userIdx)->delete();
    }
}
