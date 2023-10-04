<?php

namespace Tcc\TccTransaction;


class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for transaction.',
                    'source' => __DIR__ . '/../publish/config/tcc.php',
                    'destination' => BASE_PATH . '/config/autoload/tcc.php',
                ],
                [
                    'id' => 'migration',
                    'description' => 'The migration for transaction.',
                    'source' => __DIR__ . '/../publish/migrations/2021_05_22_004906_create_tcc_fail_table.php',
                    'destination' => BASE_PATH . '/migrations/2021_05_22_004906_create_tcc_fail_table.php',
                ],
            ],
        ];
    }
}