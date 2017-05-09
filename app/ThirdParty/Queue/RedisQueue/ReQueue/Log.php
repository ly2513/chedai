<?php
/**
 * Created by IntelliJ IDEA.
 * User: yongli
 * Date: 16/10/13
 * Time: 上午10:43
 * Email: liyong@addnewer.com
 */
namespace Queue\RedisQueue\ReQueue;

class Log
{
    //public  $logPath = null;
    public  $logPath = NULL;

    //public $logFile = null;
    const LOGFILE = NULL;

    /**
     * 配置日志初始化目录与文件
     * Log constructor.
     */
    public function __construct()
    {
        require APPLICATION_ROOT . 'application/config/queue.php';
        $this->logPath = $config['queue']['logPath'];
        is_dir($this->logPath) or mkdir($this->logPath, 0777, TRUE);
        $this->logFile = $this->logPath . '/queue_'  .date('Y-m-d', time()) . '.log';
        is_file($this->logFile) or touch($this->logFile);
    }

    /**
     * 写日志
     *
     * @param $message
     * @param int $logLevel
     */
    public  function writeLog($message, $logLevel = 2)
    {
        if ($logLevel == 1) {
            file_put_contents($this->logFile, $message, FILE_APPEND);
            //fwrite(STDOUT, "*** " . $message . "\n");
        }

        if ($logLevel == 2) {
            $message = '[' . date('Y-m-d H:i:s', time()) . ']' . $message . PHP_EOL;
            file_put_contents($this->logFile, $message, FILE_APPEND);
            //fwrite(STDOUT, "** [" . strftime('%T %Y-%m-%d') . "] " . $message . "\n");
        }

    }


}
