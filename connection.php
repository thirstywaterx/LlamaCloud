<?php
// 设置API令牌和账户ID
$api_token = 'YOUR_API_TOKEN';
$account_id = 'YOUR_ACCOUNT_ID';

// 获取POST数据
$request = file_get_contents('php://input');
$data = json_decode($request, true);

// 如果没有提供对话数据，返回错误信息
if (!isset($data['conversation']) || empty($data['conversation'])) {
    echo json_encode(['error' => 'No conversation data provided']);
    exit();
}

// 设置请求URL和数据
$url = "https://api.cloudflare.com/client/v4/accounts/$account_id/ai/run/@cf/meta/llama-3-8b-instruct";
$conversation = $data['conversation'];
$messages = array_map(function($entry) {
    return ['role' => $entry['role'], 'content' => $entry['content']];
}, $conversation);

$requestData = json_encode(['messages' => $messages]);

// 初始化cURL
$ch = curl_init($url);

// 设置cURL选项
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    "Authorization: Bearer $api_token"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $requestData);

// 执行cURL请求
$response = curl_exec($ch);

// 检查cURL错误
if ($response === false) {
    echo json_encode(['error' => curl_error($ch)]);
} else {
    // 输出响应
    $responseData = json_decode($response, true);
    if (isset($responseData['result'])) {
        echo json_encode(['response' => $responseData['result']['response']]);
    } else {
        echo json_encode(['error' => 'Invalid response from AI']);
    }
}

// 关闭cURL会话
curl_close($ch);
?>
