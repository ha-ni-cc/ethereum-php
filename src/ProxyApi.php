<?php

namespace Ethereum;

interface ProxyApi
{

    function send($method, $params = []);

    function getNetwork(): string;

    function gasPrice();

    function ethBalance(string $address);

    function receiptStatus(string $txHash): ?bool;

    function getTransactionReceipt(string $txHash);

    function sendRawTransaction($raw);

    function getNonce(string $address);
}