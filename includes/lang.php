<?php
// Language management system
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get language from session, cookie, or default to 'en'
$lang = $_SESSION['lang'] ?? $_COOKIE['lang'] ?? 'tr';

// Allow language switching via GET parameter
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'tr'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;
    setcookie('lang', $lang, time() + (365 * 24 * 60 * 60), '/'); // 1 year
}

// Load translations
$translations = [];
// If English is selected, we still load Turkish as the base UI language 
// so that Google Translate can translate from Turkish to English.
$load_lang = ($lang == 'en') ? 'tr' : $lang;
$lang_file = __DIR__ . '/../translations/' . $load_lang . '.php';
if (file_exists($lang_file)) {
    $translations = include $lang_file;
}

// Translation function
function t($key, $default = '') {
    global $translations;
    return $translations[$key] ?? $default;
}

// Get current language
function getLang() {
    global $lang;
    return $lang;
}
?>
