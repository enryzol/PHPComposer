<?php
/* 'log'  => [
	// 日志记录方式，内置 file socket 支持扩展
	'type'  => 'HjCommon\Log\Tp5Log',//'HjFile',
	// 日志保存目录
	'path'  => LOG_PATH,
	// 日志记录级别
	'level' => ['log','error','sql','debug'],
], */


namespace HjCommon\Log;

use think\App;

/**
 * 本地化调试输出到文件
 */
class Tp5Log
{
    protected $config = [
        'time_format' => 'Y-m-d H:i:s',
        'file_size'   => 20971520,
        'path'        => LOG_PATH,
        'apart_level' => [],
    ];

    // 实例化并传入参数
    public function __construct($config = [])
    {
        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }
    }

    /**
     * 日志写入接口
     * @access public
     * @param array $log 日志信息
     * @param bool  $depr 是否写入分割线
     * @return bool
     */
    public function save(array $log = [], $depr = false)
    {
        $now         = date($this->config['time_format']);
        $destination = $this->config['path'] . date('Y-m-d') . '.log';

        $path = dirname($destination);
        !is_dir($path) && mkdir($path, 0755, true);

        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        if (is_file($destination) && floor($this->config['file_size']) <= filesize($destination)) {
            rename($destination, dirname($destination) . DS . $_SERVER['REQUEST_TIME'] . '-' . basename($destination));
        }

        $depr = $depr ? "---------------------------------------------------------------\r\n" : '';
        $info = '';
        if (App::$debug) {
            // 获取基本信息
            if (isset($_SERVER['HTTP_HOST'])) {
                $current_uri = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            } else {
                $current_uri = "cmd:" . implode(' ', $_SERVER['argv']);
            }

            $runtime    = round(microtime(true) - THINK_START_TIME, 10);
            $runtimeMS    = ($runtime*1000)."ms";
            $reqs       = $runtime > 0 ? number_format(1 / $runtime, 2) : '∞';
            $time_str   = ' [运行时间：' . number_format($runtime, 6) . 's][吞吐率：' . $reqs . 'req/s]';
            $memory_use = number_format((memory_get_usage() - THINK_START_MEM) / 1024, 2);
            $memory_str = ' [内存消耗：' . $memory_use . 'kb]';
            $file_load  = ' [文件加载：' . count(get_included_files()) . ']';

            $info   = '=> [ log ] ' . $current_uri . $time_str . $memory_str . $file_load . "\r\n";
            $server = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '0.0.0.0';
            $server_name = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'http://';
            $server_name = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $server_name;
            $server_name = isset($_SERVER['HTTP_X_FORWARDED_SERVER']) ? $_SERVER['HTTP_X_FORWARDED_SERVER'] : $server_name;
            $remote = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
            $remote = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $remote;
            $remote = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $remote;
            $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'CLI';
            $uri    = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        }
        foreach ($log as $type => $val) {
            $level = '';
            foreach ($val as $msg) {
                if (!is_string($msg)) {
                    $msg = var_export($msg, true);
                }
                $level .= '=> [ ' . $type . ' ] ' . $msg . "\r\n";
            }
            if (in_array($type, $this->config['apart_level'])) {
                // 独立记录的日志级别
                $filename = $path . DS . date('d') . '_' . $type . '.log';
                error_log("=> [{$now}] {$level} \r\n", 3, $filename);
            } else {
                $info .= $level;
            }
        }
        if (App::$debug) {
//             $info = "[{$server}][{$remote}] {$method} {$uri}\r\n" . $info;[Domain:{$server_name}]
            $info = "[IP:{$remote}][URL:{$uri}][Runtime:{$runtimeMS}][Domain:{$server_name}]: \r\n" . $info;
        }
        return error_log("[{$now}]{$info} \r\n", 3, $destination);
    }

}
