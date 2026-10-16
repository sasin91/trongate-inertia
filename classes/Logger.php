<?php
class Logger {
    private $logFile;

    /**
     * @param string $logFile path relative to the app root; its directory
     *        (logs/ by default) is a persistent path on trongate.cloud
     */
    public function __construct($logFile = 'logs/app.log') {
        $this->logFile = dirname(__DIR__) . '/' . $logFile;
        $logDir = dirname($this->logFile);

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        // Never take the request down over a log file: in production the
        // app's files are read-only to the (non-root) container user.
        if (!is_writable($this->logFile) && !(!file_exists($this->logFile) && is_writable($logDir))) {
            $this->logFile = null;
        }
    }

    private function log($level, $message, $extra = null) {
        $timestamp = date('Y-m-d H:i:s.u');
        $logEntry = "[{$timestamp}] [{$level}] {$message}\n";
        if ($extra !== null) {
            $logEntry .= var_export($extra, true) . "\n";
        }
        if ($this->logFile !== null) {
            file_put_contents($this->logFile, $logEntry, FILE_APPEND);
        }
        // Also to stderr, so the lines show up in the container's logs
        file_put_contents('php://stderr', $logEntry);
    }

    public function debug($message, $extra = null) {
        $this->log('debug', $message, $extra);
    }
    public function info($message, $extra = null) {
        $this->log('info', $message, $extra);
    }
    public function notice($message, $extra = null) {
        $this->log('notice', $message, $extra);
    }
    public function warning($message, $extra = null) {
        $this->log('warning', $message, $extra);
    }
    public function error($message, $extra = null) {
        $this->log('error', $message, $extra);
    }
    public function critical($message, $extra = null) {
        $this->log('critical', $message, $extra);
    }
    public function alert($message, $extra = null) {
        $this->log('alert', $message, $extra);
    }
    public function emergency($message, $extra = null) {
        $this->log('emergency', $message, $extra);
    }
}
