<?php
/**
 * author: NanQi
 * datetime: 2019/7/3 17:53
 */

namespace Ethereum;

class InfuraApi implements ProxyApi
{
    protected $apiKey;
    protected $network;

    function __construct(string $apiKey, $network = 'mainnet')
    {
        $this->apiKey = $apiKey;
        $this->network = $network;
    }

    public function send($method, $params = [])
    {
        $url = "https://{$this->network}.infura.io/v3/{$this->apiKey}";

        $arr = array_map(function ($item) {
            if (is_array($item)) {
                return json_encode($item);
            } else {
                return '"' . $item . '"';
            }
        }, $params);
        $strParams = implode(",", $arr);
        $data_string = <<<data
{"jsonrpc":"2.0","method":"{$method}","params": [$strParams],"id":1}
data;
        $res = Utils::httpRequest('POST', $url, [
            'body' => $data_string
        ]);
        if (isset($res['result'])) {
            return $res['result'];
        } else {
            return false;
        }
    }

    function gasPrice()
    {
        $retDiv = Utils::fromWei($this->send('eth_gasPrice'), 'ether');
        if (is_array($retDiv)) {
            return Utils::divideDisplay($retDiv, 16);
        } else {
            return $retDiv;
        }
    }

    function ethBalance(string $address, string $block = 'latest')
    {
        $retDiv = Utils::fromWei($this->send('eth_getBalance', [$address, $block]), 'ether');
        if (is_array($retDiv)) {
            return Utils::divideDisplay($retDiv, 16);
        } else {
            return $retDiv;
        }
    }

    function receiptStatus(string $txHash): ?bool
    {
        $res = $this->send('eth_getTransactionByHash', [$txHash]);
        if (!$res) {
            return false;
        }
        if (!$res['blockNumber']) {
            return null;
        }
        $res = $this->send('eth_getTransactionReceipt', [$txHash]);
        return isset($res['status']) && $res['status'] == '0x1';
    }

    function sendRawTransaction($raw)
    {
        return $this->send('eth_sendRawTransaction', [$raw]);
    }

    function getNonce(string $address)
    {
        return $this->send('eth_getTransactionCount', [$address]);
    }

    function getTransactionReceipt(string $txHash)
    {
        return $this->send('eth_getTransactionReceipt', [$txHash]);
    }

    function getNetwork(): string
    {
        return $this->network;
    }
}
