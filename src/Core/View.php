<?php
/**
 * View Class
 * 
 * Template rendering and helper functions
 */

namespace App\Core;

class View
{
    private static string $layoutPath = '';
    private static array $sections = [];
    private static ?string $currentSection = null;
    private static array $sharedData = [];

    /**
     * Set layout for views
     */
    public static function setLayout(string $layout): void
    {
        self::$layoutPath = $layout;
    }

    /**
     * Share data with all views
     */
    public static function share(string $key, mixed $value): void
    {
        self::$sharedData[$key] = $value;
    }

    /**
     * Render a view
     */
    public static function render(string $view, array $data = [], ?string $layout = null): void
    {
        $viewPath = self::getViewPath($view);
        
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        // Set layout if provided
        if ($layout !== null) {
            self::$layoutPath = $layout;
        }

        // Merge shared data with view data
        $data = array_merge(self::$sharedData, $data);
        
        // Add common helpers to data
        $data['auth'] = Auth::user();
        $data['config'] = require __DIR__ . '/../../config/app.php';
        
        // Extract data to variables
        extract($data);

        // Start output buffering
        ob_start();
        
        // Include the view
        require $viewPath;
        
        $content = ob_get_clean();

        // If layout is set, wrap content in layout
        if (self::$layoutPath) {
            self::$sections['content'] = $content;
            
            $layoutPath = self::getViewPath('layouts/' . self::$layoutPath);
            
            if (file_exists($layoutPath)) {
                ob_start();
                require $layoutPath;
                $content = ob_get_clean();
            }
            
            self::$layoutPath = '';
            self::$sections = [];
        }

        echo $content;
    }

    /**
     * Start a section
     */
    public static function section(string $name): void
    {
        self::$currentSection = $name;
        ob_start();
    }

    /**
     * End current section
     */
    public static function endSection(): void
    {
        if (self::$currentSection) {
            self::$sections[self::$currentSection] = ob_get_clean();
            self::$currentSection = null;
        }
    }

    /**
     * Yield a section
     */
    public static function yield(string $name, string $default = ''): string
    {
        return self::$sections[$name] ?? $default;
    }

    /**
     * Include a partial view
     */
    public static function partial(string $view, array $data = []): void
    {
        $viewPath = self::getViewPath($view);
        
        if (file_exists($viewPath)) {
            extract(array_merge(self::$sharedData, $data));
            require $viewPath;
        }
    }

    /**
     * Get view file path
     */
    private static function getViewPath(string $view): string
    {
        $view = str_replace('.', '/', $view);
        return __DIR__ . '/../../resources/views/' . $view . '.php';
    }

    /**
     * Get base URL path (for subdirectory installations)
     */
    public static function baseUrl(): string
    {
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        return ($scriptName !== '/' && $scriptName !== '\\') ? $scriptName : '';
    }

    /**
     * Generate URL with base path
     */
    public static function url(string $path = ''): string
    {
        $base = self::baseUrl();
        $path = '/' . ltrim($path, '/');
        return $base . $path;
    }

    /**
     * Escape HTML
     */
    public static function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    /**
     * Format currency (Thai Baht)
     */
    public static function currency(float $amount): string
    {
        return number_format($amount, 2) . ' ฿';
    }

    /**
     * Format currency in short K/M format
     */
    public static function currencyShort(float $amount, int $decimals = 2): string
    {
        $absAmount = abs($amount);
        $sign = $amount < 0 ? '-' : '';
        
        if ($absAmount >= 1000000) {
            return $sign . number_format($absAmount / 1000000, $decimals) . 'M';
        } elseif ($absAmount >= 1000) {
            return $sign . number_format($absAmount / 1000, $decimals) . 'K';
        }
        return $sign . number_format($absAmount, $decimals);
    }

    /**
     * Format currency in short M format with 4 decimal places
     * Example: 1234567.89 -> "1.2346M"
     */
    public static function currencyShortM4(float $amount): string
    {
        if ($amount >= 1000000) {
            return number_format($amount / 1000000, 4) . 'M';
        } elseif ($amount >= 1000) {
            return number_format($amount / 1000, 2) . 'K';
        }
        return number_format($amount, 2);
    }

    /**
     * Format currency ALWAYS as M with 4 decimal places (no smart conversion)
     * Example: 1234567.89 -> "1.2346M", 500000 -> "0.5000M", 0 -> "0.0000M"
     */
    public static function currencyM4(float $amount): string
    {
        $sign = $amount < 0 ? '-' : '';
        $absAmount = abs($amount);
        return $sign . number_format($absAmount / 1000000, 4) . 'M';
    }

    /**
     * Format number
     */
    public static function number(float $number, int $decimals = 0): string
    {
        return number_format($number, $decimals);
    }

    /**
     * Format date (Thai)
     */
    public static function date(?string $date, string $format = 'd/m/Y'): string
    {
        if (!$date) return '-';
        
        $timestamp = strtotime($date);
        
        // Convert to Buddhist year if format contains Y
        if (strpos($format, 'Y') !== false) {
            $year = (int) date('Y', $timestamp) + 543;
            $formatted = date(str_replace('Y', '', $format), $timestamp);
            return str_replace(date('Y', $timestamp), $year, date($format, $timestamp));
        }
        
        return date($format, $timestamp);
    }

    /**
     * Format datetime (Thai)
     */
    public static function datetime(?string $datetime): string
    {
        if (!$datetime) return '-';
        return self::date($datetime, 'd/m/Y H:i');
    }

    /**
     * Get Vite asset URL
     */
    public static function vite(string $entry): string
    {
        $isDev = ($_ENV['APP_ENV'] ?? 'production') === 'development';
        $devServer = $_ENV['VITE_DEV_SERVER'] ?? 'http://localhost:5173';
        
        if ($isDev) {
            // Development mode - use Vite dev server
            return $devServer . '/' . $entry;
        }
        
        // Production mode - use manifest
        $manifestPath = __DIR__ . '/../../public/assets/.vite/manifest.json';
        
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            
            if (isset($manifest[$entry])) {
                return '/assets/' . $manifest[$entry]['file'];
            }
        }
        
        return '/assets/' . $entry;
    }

    /**
     * Generate CSRF token
     */
    public static function csrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Generate CSRF input field
     */
    public static function csrf(): string
    {
        return '<input type="hidden" name="_token" value="' . self::csrfToken() . '">';
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrf(): bool
    {
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
}
