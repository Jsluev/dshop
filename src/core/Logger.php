<?php
/**
 * Logger
 *
 * @package DShop\Core
 */

namespace DShop\Core;

/**
 * Class Logger
 *
 * Handles logging operations
 */
class Logger
{
    /**
     * Log levels
     */
    const LEVEL_DEBUG = 'debug';
    const LEVEL_INFO = 'info';
    const LEVEL_NOTICE = 'notice';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';
    const LEVEL_CRITICAL = 'critical';

    /**
     * Log levels priority
     *
     * @var array
     */
    private $levels = [
        self::LEVEL_DEBUG => 0,
        self::LEVEL_INFO => 1,
        self::LEVEL_NOTICE => 2,
        self::LEVEL_WARNING => 3,
        self::LEVEL_ERROR => 4,
        self::LEVEL_CRITICAL => 5,
    ];

    /**
     * Minimum log level
     *
     * @var string
     */
    private $minLevel = self::LEVEL_DEBUG;

    /**
     * Log to database
     *
     * @var bool
     */
    private $logToDatabase = true;

    /**
     * Log to file
     *
     * @var bool
     */
    private $logToFile = false;

    /**
     * Log file path
     *
     * @var string
     */
    private $logFile = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->minLevel = get_option('dshop_log_level', self::LEVEL_DEBUG);
        $this->logToDatabase = (bool) get_option('dshop_log_to_database', true);
        $this->logToFile = (bool) get_option('dshop_log_to_file', false);
        $this->logFile = DSHOP_PLUGIN_DIR . 'logs/dshop.log';
        
        // Set file logging directory
        $this->ensureLogDirectory();
    }
    
    private function ensureLogDirectory(): void
    {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Log debug message
     *
     * @param string $message Log message
     * @param array $context Context data
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log(self::LEVEL_DEBUG, $message, $context);
    }

    /**
     * Log info message
     *
     * @param string $message Log message
     * @param array $context Context data
     * @return void
     */
    public function info(string $message, array $context = []): void
    {
        $this->log(self::LEVEL_INFO, $message, $context);
    }

    /**
     * Log notice message
     *
     * @param string $message Log message
     * @param array $context Context data
     * @return void
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log(self::LEVEL_NOTICE, $message, $context);
    }

    /**
     * Log warning message
     *
     * @param string $message Log message
     * @param array $context Context data
     * @return void
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log(self::LEVEL_WARNING, $message, $context);
    }

    /**
     * Log error message
     *
     * @param string $message Log message
     * @param array $context Context data
     * @return void
     */
    public function error(string $message, array $context = []): void
    {
        $this->log(self::LEVEL_ERROR, $message, $context);
    }

    /**
     * Log critical message
     *
     * @param string $message Log message
     * @param array $context Context data
     * @return void
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log(self::LEVEL_CRITICAL, $message, $context);
    }

    /**
     * Log message
     *
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Context data
     * @return void
     */
    public function log(string $level, string $message, array $context = []): void
    {
        if (!$this->shouldLog($level)) {
            return;
        }

        $message = $this->interpolate($message, $context);

        if ($this->logToDatabase) {
            $this->logToDb($level, $message, $context);
        }

        if ($this->logToFile) {
            $this->logToFile($level, $message);
        }
        
        // Ensure log directory exists
        $this->ensureLogDirectory();

        // WordPress error log
        if (in_array($level, [self::LEVEL_ERROR, self::LEVEL_CRITICAL], true)) {
            error_log("DShop [{$level}]: {$message}");
        }
    }

    /**
     * Check if should log at this level
     *
     * @param string $level Log level
     * @return bool
     */
    private function shouldLog(string $level): bool
    {
        return $this->levels[$level] >= $this->levels[$this->minLevel];
    }

    /**
     * Interpolate context values into message
     *
     * @param string $message Log message
     * @param array $context Context data
     * @return string
     */
    private function interpolate(string $message, array $context): string
    {
        $replace = [];
        foreach ($context as $key => $val) {
            if (is_string($val) || (is_object($val) && method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        return strtr($message, $replace);
    }

    /**
     * Log to database
     *
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Context data
     * @return void
     */
    private function logToDb(string $level, string $message, array $context): void
    {
        global $wpdb;

        $table = $wpdb->prefix . 'dshop_logs';

        $wpdb->insert(
            $table,
            [
                'level' => $level,
                'message' => $message,
                'context' => maybe_serialize($context),
                'user_id' => get_current_user_id(),
                'ip_address' => $this->getClientIp(),
            ],
            ['%s', '%s', '%s', '%d', '%s']
        );
    }

    /**
     * Log to file
     *
     * @param string $level Log level
     * @param string $message Log message
     * @return void
     */
    private function logToFile(string $level, string $message): void
    {
        $log_dir = dirname($this->logFile);

        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $log_line = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;

        file_put_contents($this->logFile, $log_line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get client IP address
     *
     * @return string
     */
    private function getClientIp(): string
    {
        $ip_keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = explode(',', $_SERVER[$key]);
                $ip = trim($ip[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Clear old logs
     *
     * @param int $days Number of days to keep
     * @return int Number of deleted entries
     */
    public function clearOldLogs(int $days = 30): int
    {
        global $wpdb;

        $table = $wpdb->prefix . 'dshop_logs';
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $result = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$table} WHERE created_at < %s",
                $date
            )
        );

        return $result;
    }

    /**
     * Set minimum log level
     *
     * @param string $level Minimum log level
     * @return void
     */
    public function setMinLevel(string $level): void
    {
        $this->minLevel = $level;
    }

    /**
     * Enable/disable database logging
     *
     * @param bool $enabled Enable database logging
     * @return void
     */
    public function setDatabaseLogging(bool $enabled): void
    {
        $this->logToDatabase = $enabled;
    }

    /**
     * Enable/disable file logging
     *
     * @param bool $enabled Enable file logging
     * @return void
     */
    public function setFileLogging(bool $enabled): void
    {
        $this->logToFile = $enabled;
    }
}
