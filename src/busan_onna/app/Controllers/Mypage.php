<?php

namespace App\Controllers;

use App\Models\UserInfoModel;

/**
 * 마이페이지 컨트롤러
 */
class Mypage extends BaseController
{
    /**
     * 마이페이지 메인
     * - 로그인 유저의 프로필 정보 (이름, 프로필 이미지)
     * - 좋아요 누른 부산 명소 최근 5개 (reactions + v_region_content + busan_thumbnail)
     */
    public function index(): string
    {
        $userIdx = (int) session()->get('user.idx');

        // 비로그인 시 메인으로
        if (! $userIdx) {
            return redirect()->to('/');
        }

        $model = new UserInfoModel();
        $user  = $model->find($userIdx);

        // reactions.target_type → v_region_content.content_type 매핑을 SQL CASE로 처리
        // 썸네일은 busan_thumbnail에서 img_order ASC 기준 첫 번째 이미지 사용
        $db = \Config\Database::connect();
        $likedPlaces = $db->query("
            SELECT
                r.target_type,
                r.target_idx,
                vc.name,
                vc.content_type,
                (
                    SELECT bt.img_url
                    FROM   busan_thumbnail bt
                    WHERE  bt.state = 1
                      AND  (
                              (vc.content_type = 'restaurant' AND bt.restaurant_idx = vc.idx)
                           OR (vc.content_type = 'place'      AND bt.place_idx      = vc.idx)
                           OR (vc.content_type = 'event'      AND bt.event_idx      = vc.idx)
                           )
                    ORDER  BY bt.img_order ASC
                    LIMIT  1
                ) AS thumbnail
            FROM   reactions r
            JOIN   v_region_content vc
                   ON  vc.idx = r.target_idx
                   AND vc.content_type = CASE r.target_type
                                             WHEN 'spot'       THEN 'place'
                                             WHEN 'restaurant' THEN 'restaurant'
                                             WHEN 'festival'   THEN 'event'
                                         END
            WHERE  r.user_idx = ?
              AND  r.type  = 'like'
              AND  r.state != 9
              AND  vc.state = 1
            ORDER  BY r.created_at DESC
            LIMIT  5
        ", [$userIdx])->getResultArray();

        return view('service/mypage/index', [
            'activeNav'   => 'mypage',
            'user'        => $user,
            'likedPlaces' => $likedPlaces,
        ]);
    }

    /**
     * 프로필 수정 페이지 (GET)
     */
    public function profileEdit(): string
    {
        // 로그인 체크
        if (! session()->get('user.idx')) {
            return redirect()->to('/')->with('error', '로그인이 필요합니다.');
        }

        $model = new UserInfoModel();
        $user  = $model->find((int) session()->get('user.idx'));

        return view('service/mypage/profile_edit', [
            'activeNav' => 'mypage',
            'user'      => $user,
        ]);
    }

    /**
     * 프로필 정보 수정 처리 (POST) — 이름·이메일·프로필 이미지
     */
    public function profileUpdate(): \CodeIgniter\HTTP\RedirectResponse
    {
        // 로그인 체크
        $userIdx = (int) session()->get('user.idx');
        if (! $userIdx) {
            return redirect()->to('/')->with('error', '로그인이 필요합니다.');
        }

        $model = new UserInfoModel();
        $user  = $model->find($userIdx);

        $updateData = [];

        // 이름 업데이트
        $name = trim($this->request->getPost('name') ?? '');
        if ($name !== '') {
            $updateData['name'] = $name;
        }

        // 이메일 업데이트 (중복 체크: 본인 제외)
        $email = trim($this->request->getPost('email') ?? '');
        if ($email !== '' && $email !== $user['email']) {
            $exists = $model->where('email', $email)->where('idx !=', $userIdx)->first();
            if ($exists) {
                return redirect()->back()->with('error', '이미 사용 중인 이메일입니다.');
            }
            $updateData['email'] = $email;
        }

        // 프로필 이미지 업로드 처리
        $file = $this->request->getFile('profile_image');
        if ($file && $file->isValid() && ! $file->hasMoved()) {

            // 허용 확장자: jpg, jpeg, png, webp
            $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
            $ext          = strtolower($file->getClientExtension());

            if (! in_array($ext, $allowedTypes)) {
                return redirect()->back()->with('error', '프로필 이미지는 JPG, PNG, WEBP 형식만 허용됩니다.');
            }

            // 최대 6MB
            if ($file->getSize() > 6 * 1024 * 1024) {
                return redirect()->back()->with('error', '프로필 이미지는 최대 6MB까지 허용됩니다.');
            }

            // 저장 경로: public/uploads/profile/
            $uploadPath = FCPATH . 'uploads/profile/';
            if (! is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // 기존 이미지 삭제
            if (! empty($user['profile_image'])) {
                $oldFile = $uploadPath . $user['profile_image'];
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }

            // 새 파일명: user_{idx}_{timestamp}.{ext}
            $newName = 'user_' . $userIdx . '_' . time() . '.' . $ext;
            $file->move($uploadPath, $newName);

            $updateData['profile_image'] = $newName;
        }

        if (! empty($updateData)) {
            $model->update($userIdx, $updateData);

            // 세션의 이름/이메일도 갱신
            if (isset($updateData['name'])) {
                session()->set('user.name', $updateData['name']);
            }
            if (isset($updateData['email'])) {
                session()->set('user.email', $updateData['email']);
            }
            if (isset($updateData['profile_image'])) {
                session()->set('user.profile_image', $updateData['profile_image']);
            }
        }

        return redirect()->to('/mypage/profile')->with('success', '프로필이 성공적으로 수정되었습니다.');
    }

    /**
     * 현재 비밀번호만 검증 (POST, AJAX) — 스텝 1 전용
     * 응답: JSON { success: bool, message: string }
     */
    public function passwordVerify(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userIdx = (int) session()->get('user.idx');
        if (! $userIdx) {
            return $this->response->setJSON(['success' => false, 'message' => '로그인이 필요합니다.']);
        }

        $model     = new UserInfoModel();
        $user      = $model->find($userIdx);
        $currentPw = $this->request->getPost('current_password') ?? '';

        if (! password_verify($currentPw, $user['password'])) {
            return $this->response->setJSON(['success' => false, 'message' => '현재 비밀번호가 올바르지 않습니다.']);
        }

        return $this->response->setJSON(['success' => true, 'message' => '확인되었습니다.']);
    }

    /**
     * 비밀번호 변경 처리 (POST) — AJAX 전용
     * 응답: JSON { success: bool, message: string }
     */
    public function passwordChange(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userIdx = (int) session()->get('user.idx');
        if (! $userIdx) {
            return $this->response->setJSON(['success' => false, 'message' => '로그인이 필요합니다.']);
        }

        $model = new UserInfoModel();
        $user  = $model->find($userIdx);

        $currentPw = $this->request->getPost('current_password') ?? '';
        $newPw     = $this->request->getPost('new_password')     ?? '';
        $confirmPw = $this->request->getPost('confirm_password') ?? '';

        // 현재 비밀번호 검증
        if (! password_verify($currentPw, $user['password'])) {
            return $this->response->setJSON(['success' => false, 'message' => '현재 비밀번호가 올바르지 않습니다.']);
        }

        // 새 비밀번호 길이 검증
        if (strlen($newPw) < 8) {
            return $this->response->setJSON(['success' => false, 'message' => '새 비밀번호는 8자 이상이어야 합니다.']);
        }

        // 새 비밀번호 확인 일치 여부
        if ($newPw !== $confirmPw) {
            return $this->response->setJSON(['success' => false, 'message' => '새 비밀번호와 확인이 일치하지 않습니다.']);
        }

        // 현재 비밀번호와 동일 여부
        if (password_verify($newPw, $user['password'])) {
            return $this->response->setJSON(['success' => false, 'message' => '현재 비밀번호와 동일한 비밀번호로 변경할 수 없습니다.']);
        }

        // UserInfoModel의 beforeUpdate 콜백이 자동으로 해싱 처리
        $model->update($userIdx, ['password' => $newPw]);

        return $this->response->setJSON(['success' => true, 'message' => '비밀번호가 성공적으로 변경되었습니다.']);
    }
}
