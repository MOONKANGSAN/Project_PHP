/* ===================== 국밥 맛집 좋아요 투표 — 클릭 처리 (1인 1일 1회) ===================== */
(function initGukbapVote() {
    var isLoggedIn = <?= session()->get('user.idx') ? 'true' : 'false' ?>;
    var eventIdx   = <?= (int) $event['idx'] ?>;
    var banner     = document.getElementById('participationBanner');

    var buttons = document.querySelectorAll('.m-vote-btn');
    if (!buttons.length) return;

    /* 오늘 참여 완료 상태로 모든 버튼 전환 (내가 투표한 카드만 '완료' 표시로 구분) */
    function markAllVotedToday(votedRestaurantIdx) {
        document.querySelectorAll('.m-vote-btn').forEach(function (b) {
            b.disabled = true;
            if (String(votedRestaurantIdx) === b.dataset.restaurantIdx) {
                b.textContent = '✓ 오늘 투표완료';
                b.classList.add('is-voted');
            } else if (!b.classList.contains('is-voted')) {
                b.textContent = '오늘 투표 완료';
            }
        });
    }

    function updateVoteCount(restaurantIdx, count) {
        document.querySelectorAll('.m-spot-card[data-restaurant-idx="' + restaurantIdx + '"] .m-spot-vote-count')
            .forEach(function (el) { el.textContent = '🔥 ' + count + '표'; });
    }

    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!isLoggedIn) {
                var loginModal = document.getElementById('loginModal');
                if (loginModal) {
                    loginModal.classList.add('is-open');
                    document.body.style.overflow = 'hidden';
                } else {
                    alert('로그인이 필요합니다.');
                }
                return;
            }
            if (btn.disabled) return;

            var restaurantIdx = btn.dataset.restaurantIdx;
            btn.disabled = true;

            fetch('/events/' + eventIdx + '/gukbap-like', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ restaurant_idx: restaurantIdx }),
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success) {
                        markAllVotedToday(restaurantIdx);
                        updateVoteCount(restaurantIdx, data.vote_count);
                        if (banner) {
                            banner.textContent = data.message;
                            banner.classList.toggle('is-eligible', data.participation_days >= 3);
                        }
                    } else {
                        if (data.already_voted) {
                            markAllVotedToday(-1);
                        } else {
                            btn.disabled = false;
                        }
                        alert(data.message);
                    }
                })
                .catch(function () {
                    btn.disabled = false;
                    alert('서버 연결에 실패했습니다. 잠시 후 다시 시도해주세요.');
                });
        });
    });
})();
