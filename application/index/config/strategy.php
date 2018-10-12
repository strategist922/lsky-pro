<?php
/**
 * User: Wisp X
 * Date: 2018/9/29
 * Time: 下午1:40
 * Link: http://gitee.com/wispx
 */

// [策略组]

return [
    'local'     => [
        'name'  => '本地',
        'class' => \strategy\driver\Local::class
    ],
    'oss'       => [
        'name'  => '阿里云OSS',
        'class' => \strategy\driver\Oss::class
    ],
    'cos'       => [
        'name'  => '腾讯云COS',
        'class' => \strategy\driver\Cos::class
    ],
    'qiniu'     => [
        'name'  => '七牛云',
        'class' => \strategy\driver\Qiniu::class
    ],
    'upyun'     => [
        'name'  => '又拍云',
        'class' => \strategy\driver\Upyun::class
    ],
];