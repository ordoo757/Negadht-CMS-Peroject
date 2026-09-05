<?php

class Language
{
    protected $availableLanguages = [
        'fa' => 'فارسی',
        'en' => 'English',
        'ar' => 'العربية',
        'tr' => 'Türkçe',
        'ku' => 'Kurdî',
        'az' => 'Azərbaycanca',
        'ru' => 'Русский',
        'zh' => '中文',
        'es' => 'Español',
        'fr' => 'Français',
        'de' => 'Deutsch',
        'pt' => 'Português',
        'hi' => 'हिन्दी',
        'ur' => 'اردو',
        'ps' => 'پښتو',
        'ms' => 'Bahasa Melayu',
        'ko' => '한국어',
        'ja' => '日本語',
        'it' => 'Italiano',
        'hy' => 'Հայերեն',
        'la' => 'Latina',
    ];

    protected $rtlLanguages = ['fa', 'ar', 'ku', 'ur', 'ps'];

    protected $currentLanguage;

    public function __construct()
    {
        // بررسی زبان از session، cookie یا GET
        if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $this->availableLanguages)) {
            $this->currentLanguage = $_GET['lang'];
            $_SESSION['install_lang'] = $this->currentLanguage;
            setcookie('install_lang', $this->currentLanguage, time() + 3600 * 24 * 30, '/');
        } elseif (isset($_SESSION['install_lang']) && array_key_exists($_SESSION['install_lang'], $this->availableLanguages)) {
            $this->currentLanguage = $_SESSION['install_lang'];
        } elseif (isset($_COOKIE['install_lang']) && array_key_exists($_COOKIE['install_lang'], $this->availableLanguages)) {
            $this->currentLanguage = $_COOKIE['install_lang'];
        } else {
            // پیش‌فرض: فارسی
            $this->currentLanguage = 'fa';
        }

        // بارگذاری فایل زبان
        $this->loadLanguage();
    }

    protected $translations = [];

    protected function loadLanguage()
    {
        $langFile = __DIR__ . '/../languages/' . $this->currentLanguage . '.php';
        if (file_exists($langFile)) {
            $this->translations = require $langFile;
        } else {
            // Fallback به انگلیسی
            $langFile = __DIR__ . '/../languages/en.php';
            $this->translations = require $langFile;
        }
    }

    public function get($key, $default = '')
    {
        return $this->translations[$key] ?? $default;
    }

    public function getCurrentLanguage()
    {
        return $this->currentLanguage;
    }

    public function isRTL()
    {
        return in_array($this->currentLanguage, $this->rtlLanguages);
    }

    public function getAvailableLanguages()
    {
        return $this->availableLanguages;
    }

    public function renderLanguageSwitcher()
    {
        $html = '<div class="dropdown d-inline-block">';
        $html .= '<button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">';
        $html .= '<i class="fas fa-globe"></i> ' . ($this->availableLanguages[$this->currentLanguage] ?? 'English');
        $html .= '</button>';
        $html .= '<ul class="dropdown-menu dropdown-menu-end">';

        foreach ($this->availableLanguages as $code => $name) {
            $active = $code === $this->currentLanguage ? 'active' : '';
            $html .= '<li>';
            $html .= '<a class="dropdown-item ' . $active . '" href="?lang=' . $code . '&step=' . ($_GET['step'] ?? 1) . '">';
            $html .= $name;
            if ($code === $this->currentLanguage) {
                $html .= ' <i class="fas fa-check"></i>';
            }
            $html .= '</a>';
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '</div>';

        return $html;
    }
}
