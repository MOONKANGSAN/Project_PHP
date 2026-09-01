<?php
namespace App\Controllers;

use App\Models\CartModel;

/**
 * 장바구니 컨트롤러 — 모든 엔드포인트 로그인 필요
 */
class Cart extends BaseController
{
    /**
     * 로그인 체크 — 미로그인 시 AJAX는 401 JSON, 일반 요청은 로그인 페이지 리다이렉트
     */
    private function requireLogin(): bool
    {
        if (!session()->get('user.idx')) {
            if ($this->request->isAJAX()) {
                $this->response->setStatusCode(401);
                echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
                return false;
            }
            redirect()->to('/auth/login')->send();
            exit;
        }
        return true;
    }

    /**
     * GET /cart — 장바구니 목록 페이지
     */
    public function index(): string
    {
        if (!$this->requireLogin()) exit;

        $model     = new CartModel();
        $userIdx   = (int) session()->get('user.idx');
        $cartItems = $model->getCartItems($userIdx);

        $total = array_sum(array_map(
            fn($i) => ($i['price'] + ($i['additional_price'] ?? 0)) * $i['quantity'],
            $cartItems
        ));

        return view('service/cart/index', [
            'cartItems' => $cartItems,
            'total'     => $total,
        ]);
    }

    /**
     * POST /cart/add — 장바구니 담기 (AJAX JSON)
     * body: { goods_idx, option_value_idx?, quantity }
     */
    public function add(): void
    {
        if (!$this->requireLogin()) return;

        $body           = $this->request->getJSON(true) ?? [];
        $goodsIdx       = (int) ($body['goods_idx'] ?? 0);
        $optionValueIdx = isset($body['option_value_idx']) && $body['option_value_idx']
                          ? (int) $body['option_value_idx'] : null;
        $quantity       = max(1, (int) ($body['quantity'] ?? 1));

        if ($goodsIdx === 0) {
            echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
            return;
        }

        $model = new CartModel();
        $model->addOrIncrement((int) session()->get('user.idx'), $goodsIdx, $optionValueIdx, $quantity);

        echo json_encode(['success' => true]);
    }

    /**
     * POST /cart/update — 수량 변경 (AJAX JSON)
     * body: { cart_idx, quantity }
     */
    public function update(): void
    {
        if (!$this->requireLogin()) return;

        $body     = $this->request->getJSON(true) ?? [];
        $cartIdx  = (int) ($body['cart_idx'] ?? 0);
        $quantity = max(1, (int) ($body['quantity'] ?? 1));

        $model = new CartModel();
        $item  = $model->find($cartIdx);

        if (!$item || (int) $item['user_idx'] !== (int) session()->get('user.idx')) {
            echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
            return;
        }

        $model->update($cartIdx, ['quantity' => $quantity]);
        echo json_encode(['success' => true]);
    }

    /**
     * POST /cart/remove — 항목 삭제 (AJAX JSON)
     * body: { cart_idx }
     */
    public function remove(): void
    {
        if (!$this->requireLogin()) return;

        $body    = $this->request->getJSON(true) ?? [];
        $cartIdx = (int) ($body['cart_idx'] ?? 0);

        $model = new CartModel();
        $item  = $model->find($cartIdx);

        if (!$item || (int) $item['user_idx'] !== (int) session()->get('user.idx')) {
            echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
            return;
        }

        $model->delete($cartIdx);
        echo json_encode(['success' => true]);
    }
}
