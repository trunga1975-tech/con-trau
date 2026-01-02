<?php
$input = file_get_contents("php://input");
file_put_contents("log.txt", date("H:i:s")." | ".$input.PHP_EOL, FILE_APPEND); =====
$token = "8307166755:AAHRgY7U0vl7tqomupnkdsMYXCb2pN1VYlo";
$api = "https://core.telegram.org/bot$token/";

// ================== GET UPDATE ==================
$update = json_decode(file_get_contents("php://input"), true);
if (!isset($update["message"])) exit;

$msg = $update["message"];
$chat_id = $msg["chat"]["id"];
$text = trim($msg["text"] ?? "");
$user = $msg["from"];
$user_ip = $_SERVER['REMOTE_ADDR'];

// ================== MENU ==================
$menu = [
    "keyboard" => [
        ["🎵 TikTok", "🎧 MP3"],
        ["🔊 TTS"],
        ["🏦 Bank QR", "🚨 Scam"],
        ["📞 Phone", "🌤 Weather"],
        ["📌 IP", "⏰ Time"],
        ["👤 Info"]
    ],
    "resize_keyboard" => true
];

// ================== START ==================
if ($text == "/start") {
    sendMessage($chat_id,
"🤖 BOT TOOL PHP
━━━━━━━━━━━━━━
🎵 TikTok / 🎧 MP3
🔊 Đọc văn bản (TTS)
🏦 Bank QR / 🚨 Scam
📞 Phone / 🌤 Weather
📌 IP / ⏰ Time / 👤 Info
", $menu);
}

// ================== BASIC ==================
elseif ($text == "📌 IP") {
    sendMessage($chat_id, "🌐 IP của bạn: $user_ip");
}
elseif ($text == "⏰ Time") {
    sendMessage($chat_id, "⏰ " . date("d/m/Y H:i:s"));
}
elseif ($text == "👤 Info") {
    sendMessage($chat_id,
"👤 THÔNG TIN
━━━━━━━━━━━━
ID: {$user['id']}
Tên: {$user['first_name']}
Username: @" . ($user['username'] ?? 'Không có'));
}

// ================== WEATHER ==================
elseif ($text == "🌤 Weather") {
    sendMessage($chat_id, "🌍 Gõ:\n/weather Hanoi");
}
elseif (strpos($text, "/weather") === 0) {
    $city = trim(str_replace("/weather", "", $text));
    $weather = @file_get_contents("https://wttr.in/" . urlencode($city) . "?format=3");
    sendMessage($chat_id, "🌤 $weather");
}

// ================== PHONE ==================
elseif ($text == "📞 Phone") {
    sendMessage($chat_id, "📞 Gõ:\n/phone 0867581066");
}
elseif (strpos($text, "/phone") === 0) {
    $info = checkPhoneVN(trim(str_replace("/phone", "", $text)));
    if (!$info["valid"]) sendMessage($chat_id, "❌ Số không hợp lệ");
    else sendMessage($chat_id,
"📞 CHECK PHONE
━━━━━━━━━━━━
Số: {$info['phone']}
Nhà mạng: {$info['carrier']}");
}

// ================== TIKTOK VIDEO ==================
elseif ($text == "🎵 TikTok") {
    sendMessage($chat_id, "📥 Gõ:\n/tt link_tiktok");
}
elseif (strpos($text, "/tt") === 0) {
    $json = json_decode(@file_get_contents("https://tikwm.com/api/?url=" . urlencode(trim(str_replace("/tt", "", $text)))), true);
    if (isset($json["data"]["play"])) sendVideo($chat_id, $json["data"]["play"]);
    else sendMessage($chat_id, "❌ Không tải được video");
}

// ================== TIKTOK MP3 ==================
elseif ($text == "🎧 MP3") {
    sendMessage($chat_id, "🎧 Gõ:\n/mp3 link_tiktok");
}
elseif (strpos($text, "/mp3") === 0) {
    $json = json_decode(@file_get_contents("https://tikwm.com/api/?url=" . urlencode(trim(str_replace("/mp3", "", $text)))), true);
    if (isset($json["data"]["music"])) sendAudio($chat_id, $json["data"]["music"]);
    else sendMessage($chat_id, "❌ Không lấy được MP3");
}

// ================== BANK QR ==================
elseif ($text == "🏦 Bank QR") {
    sendMessage($chat_id,
"/ VP Bank| 010212399987 | NGUYEN MANH CUONG| 50000 | VPBank 50");
}
elseif (strpos($text, "/bank") === 0) {
    $a = array_map('trim', explode("|", str_replace("/bank", "", $text)));
    if (count($a) < 5) sendMessage($chat_id, "❌ Sai cú pháp");
    else sendPhoto($chat_id,
"https://api.vietqr.io/image/{$a[0]}-{$a[1]}-compact2.png?amount={$a[3]}&addInfo="
. urlencode($a[4]) . "&accountName=" . urlencode($a[2]));
}

// ================== SCAM ==================
elseif ($text == "🚨 Scam") {
    sendMessage($chat_id, "/scam sdt | stk | link");
}
elseif (strpos($text, "/scam") === 0) {
    $r = checkScam(trim(str_replace("/scam", "", $text)));
    sendMessage($chat_id, $r["scam"]
        ? "🚨 NGUY HIỂM\n{$r['value']}\n{$r['reason']}"
        : "✅ Chưa phát hiện lừa đảo");
}

// ================== TTS MENU (FIX LỖI) ==================
elseif ($text == "🔊 TTS") {
    sendMessage($chat_id,
"🔊 ĐỌC VĂN BẢN → GIỌNG NÓI

🇻🇳 /nam Nội dung
🇻🇳 /nu Nội dung
🇺🇸 /en Nội dung
🇯🇵 /jb Nội dung
🇰🇷 /kr Nội dung
🇨🇳 /cn Nội dung
🇫🇷 /fr Nội dung");
}

// ================== TTS COMMAND ==================
elseif (
    strpos($text, "/nam") === 0 ||
    strpos($text, "/nu") === 0 ||
    strpos($text, "/en") === 0 ||
    strpos($text, "/jb") === 0 ||
    strpos($text, "/kr") === 0 ||
    strpos($text, "/cn") === 0 ||
    strpos($text, "/fr") === 0
) {
    $p = explode(" ", $text, 2);
    $cmd = $p[0];
    $content = $p[1] ?? "";

    if ($content == "") {
        sendMessage($chat_id, "❌ Nhập nội dung sau lệnh");
        exit;
    }

    $lang = "vi";
    if ($cmd == "/en") $lang = "en";
    if ($cmd == "/jb") $lang = "ja";
    if ($cmd == "/kr") $lang = "ko";
    if ($cmd == "/cn") $lang = "zh-CN";
    if ($cmd == "/fr") $lang = "fr";

    $tts = "https://translate.google.com/translate_tts?ie=UTF-8&client=tw-ob"
        . "&tl=$lang&q=" . urlencode($content);

    sendAudio($chat_id, $tts);
}

// ================== DEFAULT ==================
else {
    sendMessage($chat_id, "❓ Không hiểu lệnh");
}

// ================== FUNCTIONS ==================
function sendMessage($id,$t,$k=null){global $api;$d=["chat_id"=>$id,"text"=>$t];if($k)$d["reply_markup"]=json_encode($k);file_get_contents($api."sendMessage?".http_build_query($d));}
function sendPhoto($id,$p){global $api;file_get_contents($api."sendPhoto?".http_build_query(["chat_id"=>$id,"photo"=>$p]));}
function sendVideo($id,$v){global $api;file_get_contents($api."sendVideo?".http_build_query(["chat_id"=>$id,"video"=>$v]));}
function sendAudio($id,$a){global $api;file_get_contents($api."sendAudio?".http_build_query(["chat_id"=>$id,"audio"=>$a]));}

function checkPhoneVN($p){
$p=preg_replace('/[^0-9]/','',$p);if(substr($p,0,2)=="84")$p="0".substr($p,2);
if(!preg_match('/^0[0-9]{9}$/',$p))return["valid"=>false];
$m=["086"=>"Viettel","096"=>"Viettel","097"=>"Viettel","098"=>"Viettel","032"=>"Viettel","033"=>"Viettel","034"=>"Viettel","035"=>"Viettel","036"=>"Viettel","037"=>"Viettel","038"=>"Viettel","039"=>"Viettel",
"088"=>"VinaPhone","091"=>"VinaPhone","094"=>"VinaPhone","081"=>"VinaPhone","082"=>"VinaPhone","083"=>"VinaPhone","084"=>"VinaPhone","085"=>"VinaPhone",
"089"=>"MobiFone","090"=>"MobiFone","093"=>"MobiFone","070"=>"MobiFone","076"=>"MobiFone","077"=>"MobiFone","078"=>"MobiFone","079"=>"MobiFone"];
return["valid"=>true,"phone"=>$p,"carrier"=>$m[substr($p,0,3)]??"Không rõ"];
}

function checkScam($i){
$bp=["0987654321"=>"Giả danh công an"];
$bb=["0123456789"=>"Lừa đảo bán hàng"];
$bl=["abcxyz.com"=>"Website giả mạo"];
$i=strtolower(preg_replace('/https?:\/\//','',$i));
if(isset($bp[$i]))return["scam"=>true,"value"=>$i,"reason"=>$bp[$i]];
if(isset($bb[$i]))return["scam"=>true,"value"=>$i,"reason"=>$bb[$i]];
foreach($bl as $l=>$r)if(strpos($i,$l)!==false)return["scam"=>true,"value"=>$i,"reason"=>$r];
return["scam"=>false,"value"=>$i];
}
