<?php
declare(strict_types=1);
return [
    'nsq_detection_time' => 5, // NSQ检测补偿事务时间
    'nsq_topic' => env('APP_NAME', 'hyperf') . ':tcc', // NSQ Topic
    'redis_prefix' => env('APP_NAME', 'hyperf') . ':tcc', // Redis 缓存前缀
    'exception' => \Tcc\TccTransaction\Exception\Handle::class, // 无法处理异常通知类
    'logger' => \Hyperf\Contract\StdoutLoggerInterface::class, // 日志提供者
];
