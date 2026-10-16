<?php
class Logger {
    /** @var array<string, resource|false> open handles by target, shared by every Logger */
    private static $streams = [];

    /** @var list<string> */
    private $targets;

    /**
     * @param string $logFile path relative to the app root; its directory
     *        (logs/ by default) is a persistent path on trongate.cloud
     */
    public function __construct($logFile = 'logs/app.log') {
        // The process's stdout and stderr (fd 1 and 2, not the response
        // body), so the lines also show up in the container's logs
        $this->targets = [APPPATH . $logFile, 'php://stdout', 'php://stderr'];
    }

    private function log($level, $message, $extra = null) {
        $timestamp = (new DateTimeImmutable())->format('Y-m-d H:i:s.u');
        $logEntry = "[{$timestamp}] [{$level}] {$message}\n";
        if ($extra !== null) {
            $logEntry .= var_export($extra, true) . "\n";
        }
        foreach ($this->targets as $target) {
            $stream = self::stream($target);
            if ($stream !== false) {
                fwrite($stream, $logEntry);
            }
        }
    }

    /**
     * Opens a target on its first write and keeps the handle (append mode,
     * so short lines from concurrent requests don't interleave). A target
     * that can't be opened is skipped rather than failing the request: in
     * production the app's files are read-only to the non-root container user.
     *
     * @return resource|false
     */
    private static function stream($target) {
        if (!array_key_exists($target, self::$streams)) {
            $dir = dirname($target);
            if (!str_starts_with($target, 'php://') && !is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            self::$streams[$target] = @fopen($target, 'a');
        }
        return self::$streams[$target];
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
