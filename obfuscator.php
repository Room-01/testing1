<?php
/**
 * Modul Keamanan: Dynamic JS Packer & Obfuscator
 * Berfungsi untuk mengacak logika Bypass WebView agar aman dari Ctrl+U dan Bot Crawler.
 */

function generateSecureBypass($final_url,$is_android, $is_ios,$intent_url_clean) {
    // Parsing variabel boolean PHP ke bentuk string JavaScript
    $is_a =$is_android ? 'true' : 'false';
    $is_i =$is_ios ? 'true' : 'false';
    
    // LEVEL 1: Enkripsi URL utama ke Base64 
    $b64_url = base64_encode($final_url);

    // LEVEL 2: Raw JavaScript dengan pemecahan string skema (Schema Splitting)
    // Kata 'intent://' dan 'googlechromes://' dipecah menjadi array char agar tidak terdeteksi regex bot
    $raw_js = "
        const b = atob('$b64_url');
        const a = $is_a;
        const i = $is_i;
        
        const p1 = ['i','n','t','e','n','t',':','/','/'].join('');
        const intU = p1 + '$intent_url_clean' + '#Intent;scheme=https;action=android.intent.action.VIEW;category=android.intent.category.BROWSABLE;end;';
        
        let cU = b;
        if(cU.startsWith('https://')){
            cU = cU.replace('https://', ['g','o','o','g','l','e','c','h','r','o','m','e','s',':','/','/'].join(''));
        } else if(cU.startsWith('http://')){
            cU = cU.replace('http://', ['g','o','o','g','l','e','c','h','r','o','m','e',':','/','/'].join(''));
        }

        document.addEventListener('DOMContentLoaded', function(){
            const ab = document.getElementById('action-box');
            const lb = document.getElementById('loader-box');
            const btn = document.getElementById('bypass-btn');

            if(a){
                window.location.replace(intU);
                setTimeout(() => {
                    lb.style.display = 'none';
                    ab.style.display = 'block';
                    btn.onclick = () => { window.location.href = intU; };
                }, 1500);
            } else if(i){
                setTimeout(() => {
                    lb.style.display = 'none';
                    ab.style.display = 'block';
                    btn.onclick = () => {
                        window.location.href = cU;
                        setTimeout(() => { window.location.href = b; }, 800);
                    };
                }, 1500);
            } else {
                window.location.replace(b);
            }
        });
    ";

    // Menghapus spasi dan tab berlebih (Minification sederhana sebelum dienkripsi)
    $minified_js = preg_replace('/\s+/', ' ',$raw_js);

    // LEVEL 3: JavaScript Packer (Pengacakan Total ke Hexadecimal)
    $hex_array = [];
    $length = strlen($minified_js);
    for ($x = 0; $x <$length; $x++) {$hex_array[] = "0x" . bin2hex($minified_js[$x]);
    }
    $hex_string = implode(',',$hex_array);

    // Menggunakan `new Function()` sebagai ganti `eval()` karena lebih stealth 
    // dan sulit dideteksi oleh sistem anti-malware standar.
    $obfuscated_js = "(function(_0x1a){var _0x2b=String.fromCharCode.apply(null,_0x1a);new Function(_0x2b)();})([$hex_string]);";

    // Kembalikan output final beserta tag script
    return "<script>\n" . $obfuscated_js . "\n</script>";
}
?>