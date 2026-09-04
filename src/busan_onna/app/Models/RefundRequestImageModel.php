<?php
namespace App\Models;
use CodeIgniter\Model;

/**
 * 환불 요청 첨부 이미지 모델 — file_path는 '/uploads/refunds/{filename}' 형태
 */
class RefundRequestImageModel extends Model
{
    protected $table      = 'refund_request_images';
    protected $primaryKey = 'idx';
    protected $useTimestamps = false;

    protected $allowedFields = ['refund_request_idx', 'file_path'];

    /**
     * 환불 요청별 이미지 목록
     */
    public function getByRefundRequest(int $refundRequestIdx): array
    {
        return $this->where('refund_request_idx', $refundRequestIdx)->findAll();
    }
}
