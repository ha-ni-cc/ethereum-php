<?php

namespace Ethereum;

use Web3p\EthereumTx\Transaction;

/**
 * @method bool|null receiptStatus(string $txHash)
 * @method mixed gasPrice()
 * @method mixed ethBalance(string $address)
 * @method mixed getTransactionReceipt(string $txHash)
 */
class Eth
{
    protected $proxyApi;

    function __construct(ProxyApi $proxyApi)
    {
        $this->proxyApi = $proxyApi;
    }

    function __call($name, $arguments)
    {
        return call_user_func_array([$this->proxyApi, $name], $arguments);
    }

    public static function gasPriceOracle($type = 'standard')
    {
        $url = 'https://www.etherchain.org/api/gasPriceOracle';
        $res = Utils::httpRequest('GET', $url);
        if ($type && isset($res[$type])) {
            $price = $res[$type];
            $price = Utils::toWei($price, 'gwei');
            //$price = $price * 1e9;
            return $price;
        } else {
            return $res;
        }
    }

    protected function getChainId(): int
    {
        $network = $this->proxyApi->getNetwork();
        $chainId = 1;
        switch ($network) {
            case 'rinkeby':
                $chainId = 4;
                break;
            case 'ropsten':
                $chainId = 3;
                break;
            case 'kovan':
                $chainId = 42;
                break;
            default:
                break;
        }

        return $chainId;
    }

    public function transfer(string $privateKey, string $to, float $value, string $gasPrice = 'standard')
    {
        $from = PEMHelper::privateKeyToAddress($privateKey);
        $nonce = $this->proxyApi->getNonce($from);
        if (!Utils::isHex($gasPrice)) {
            $gasPrice = Utils::toHex(self::gasPriceOracle($gasPrice), true);
        }

        $eth = Utils::toWei("$value", 'ether');
        //$eth = $value * 1e16;
        $eth = Utils::toHex($eth, true);

        $transaction = new Transaction([
            'nonce' => "$nonce",
            'from' => $from,
            'to' => $to,
            'gas' => '0x76c0',
            'gasPrice' => "$gasPrice",
            'value' => "$eth",
            'chainId' => $this->getChainId(),
        ]);

        $raw = $transaction->sign($privateKey);
        return $this->proxyApi->sendRawTransaction('0x' . $raw);
    }
}