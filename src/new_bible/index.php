<?php
// PHP 환경 및 서버 정보 출력 테스트 페이지
$serverInfo = [
    'PHP 버전'    => PHP_VERSION,
    'OS'          => PHP_OS,
    '서버 소프트웨어' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
    '서버 포트'   => $_SERVER['SERVER_PORT'] ?? 'N/A',
    '문서 루트'   => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
    '현재 시각'   => date('Y-m-d H:i:s'),
];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP 테스트 페이지</title>
    <style>
        body {
            font-family: 'Malgun Gothic', sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
            padding: 32px 40px;
            min-width: 400px;
        }
        h1 {
            font-size: 1.4rem;
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #4f8ef7;
            padding-bottom: 10px;
        }
        table { width: 100%; border-collapse: collapse; }
        td {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
        }
        td:first-child { color: #888; width: 40%; }
        td:last-child { color: #222; font-weight: bold; }
        .badge {
            display: inline-block;
            background: #e6f0ff;
            color: #4f8ef7;
            border-radius: 4px;
            padding: 2px 8px;
            font-size: 0.8rem;
            margin-top: 16px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>PHP 환경 테스트</h1>
        <table>
            <?php foreach ($serverInfo as $label => $value): ?>
            <tr>
                <td><?= htmlspecialchars($label) ?></td>
                <td><?= htmlspecialchars($value) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <div class="badge">정상 동작 중</div>
    </div>
</body>
</html>
