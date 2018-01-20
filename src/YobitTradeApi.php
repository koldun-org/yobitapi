<?php

namespace OlegStyle\YobitApi;

use GuzzleHttp\Client;
use OlegStyle\YobitApi\Models\CurrencyPair;

/**
 * Class YobitTradeApi
 * @package OlegStyle\YobitApi
 *
 * @author Oleh Borysenko <olegstyle1@gmail.com>
 */
class YobitTradeApi
{
    const BASE_URI = 'https://yobit.net/tapi/';

    const FILE_WITH_NONCE = __DIR__ . '/yobit_nonce.txt';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $publicApiKey;

    /**
     * @var string
     */
    protected $privateApiKey;

    public function __construct(string $publicKey, string $privateKey)
    {
        $this->publicApiKey = $publicKey;
        $this->privateApiKey = $privateKey;

        $this->client = new Client([
            'base_uri' => static::BASE_URI,
            'timeout' => 30.0,
        ]);
    }

    protected function getNextNonce(): string
    {
        if (file_exists(self::FILE_WITH_NONCE)) {
            $nonce = file_get_contents(self::FILE_WITH_NONCE);
        } else {
            $nonce = 0;
        }
        $nonce += 1;
        file_put_contents(self::FILE_WITH_NONCE, $nonce);

        return $nonce;
    }

    protected function generateSign(array $post): string
    {
        $sign = http_build_query(array_filter($post), '', '&');
        $sign = hash_hmac('sha512', $sign, $this->privateApiKey);

        return $sign;
    }

    public function getResponse(string $method, array $post = []): array
    {
        $post['method'] = $method;
        $post['nonce'] = $this->getNextNonce();

        $response = $this->client->post('', [
            'form_params' => $post,
            'headers' => [
                "Sign" => $this->generateSign($post),
                "Key" => $this->publicApiKey,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    public function getInfo(): array
    {
        return $this->getResponse('getInfo');
    }

    public function getActiveOrders(CurrencyPair $pair): array
    {
        return $this->getResponse('ActiveOrders', [
            'pair' => (string) $pair
        ]);
    }

    public function trade(CurrencyPair $pair, string $type, float $rate, float $amount)
    {
        return $this->getResponse('Trade', [
            'pair' => (string) $pair,
            'type' => $type,
            'rate' => $rate,
            'amount' => $amount,
        ]);
    }

    public function cancelOrder(int $orderId)
    {
        return $this->getResponse('CancelOrder', [
            'order_id' => $orderId,
        ]);
    }

    public function getOrderInfo(int $orderId)
    {
        return $this->getResponse('OrderInfo', [
            'order_id' => $orderId,
        ]);
    }
}
