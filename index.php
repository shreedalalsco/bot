<?php
// ✅ Telegram Bot Token
$botToken = "7664376832:AAHs1htfIN2KhcsJopg9GbQqlYtlQci-GRE";
$telegramAPI = "https://api.telegram.org/bot".$botToken."/";

// ✅ User Messages Read Karna
$update = json_decode(file_get_contents("php://input"), TRUE);
$chat_id = $update["message"]["chat"]["id"];
$text = $update["message"]["text"];

// ✅ Subject → Chapter → Lecture Structure
$subjects = [
    "Physics" => [
        "1" => [ // 🟢 Chapter 1: Mechanics
            "name" => "Mechanics",
            "lectures" => [
                "1" => "video_1.mp4",
                "2" => "video_2.mp4"
            ]
        ],
        "2" => [ // 🟢 Chapter 2: Electromagnetism
            "name" => "Electromagnetism",
            "lectures" => [
                "1" => "video_3.mp4",
                "2" => "video_4.mp4"
            ]
        ]
    ]
];

// ✅ User Commands Handle Karna
if ($text == "/start") {
    sendInlineKeyboard($chat_id, "📚 Select Subject:", [["Physics"]]);
} elseif ($text == "Physics") {
    $buttons = [];
    foreach ($subjects["Physics"] as $chapter_id => $chapter) {
        $buttons[] = [$chapter["name"]." (Chapter $chapter_id)"];
    }
    sendInlineKeyboard($chat_id, "📖 Select Chapter:", $buttons);
} elseif (preg_match('/Chapter (\d+)/', $text, $matches)) {
    $chapter_id = $matches[1];
    if (isset($subjects["Physics"][$chapter_id])) {
        $chapter = $subjects["Physics"][$chapter_id];
        $buttons = [];
        foreach ($chapter["lectures"] as $lecture_no => $file_name) {
            $buttons[] = ["🎥 Lecture $lecture_no"];
        }
        sendInlineKeyboard($chat_id, "🎬 Select Lecture:", $buttons);
    }
} elseif (preg_match('/Lecture (\d+)/', $text, $matches)) {
    $lecture_no = $matches[1];
    foreach ($subjects["Physics"] as $chapter) {
        if (isset($chapter["lectures"][$lecture_no])) {
            sendVideo($chat_id, $chapter["lectures"][$lecture_no]);
            break;
        }
    }
}

// ✅ Function: Message Send Karna
function sendMessage($chat_id, $message) {
    global $telegramAPI;
    file_get_contents($telegramAPI."sendMessage?chat_id=".$chat_id."&text=".urlencode($message));
}

// ✅ Function: Inline Keyboard Send Karna
function sendInlineKeyboard($chat_id, $message, $buttons) {
    global $telegramAPI;
    $keyboard = ["keyboard" => $buttons, "resize_keyboard" => true, "one_time_keyboard" => true];
    $postData = [
        "chat_id" => $chat_id,
        "text" => $message,
        "reply_markup" => json_encode($keyboard)
    ];
    file_get_contents($telegramAPI."sendMessage?".http_build_query($postData));
}

// ✅ Function: Video Send Karna (Telegram Pe Hi Play Hoga)
function sendVideo($chat_id, $file_path) {
    global $telegramAPI;
    $postData = [
        "chat_id" => $chat_id,
        "video" => new CURLFile($file_path),
        "caption" => "📺 Your Lecture"
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $telegramAPI."sendVideo");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}
?>

