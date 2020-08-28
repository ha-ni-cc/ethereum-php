<?php

class Ethereum {

    protected $config = [
        'network' => 'mainnet',
        'api_type' => 'etherscan',
        'api_key' => 'KJU6S4DP2AFPA91T6XEKCEDJUA6V5R9MD5',
    ];

    public function __construct($config = [])
    {
        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }
    }
}