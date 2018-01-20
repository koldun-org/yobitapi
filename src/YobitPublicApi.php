<?php

namespace OlegStyle\YobitApi;

use OlegStyle\YobitApi\Models\CurrencyPair;
use GuzzleHttp\Client;

/**
 * Class YobitPublicApi
 * @package OlegStyle\YobitApi
 *
 * @author Oleh Borysenko <olegstyle1@gmail.com>
 */
class YobitPublicApi
{
    const BASE_URI = 'https://yobit.net/api/3/';
    /**
     * @var Client
     */
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => static::BASE_URI,
            'timeout' => 30.0,
            'headers' => [
                "Content-type: application/json"
            ]
        ]);
    }

    /**
     * Get info about currencies
     *
     * @return array|null
     */
    public function getInfo()
    {
        $response = $this->client->get('info');
        $result = json_decode((string) $response->getBody(), true);

        return $result;
    }

    /**
     * @param CurrencyPair[] $pairs
     * @return string
     */
    protected function prepareQueryForPairs($pairs)
    {
        $query = [];
        foreach ($pairs as $pair) {
            $query[] = "{$pair->from}_{$pair->to}";
        }
        $query = implode('-', $query);

        return $query;
    }

    /**
     * @param CurrencyPair[] $pairs -> example ['ltc' => 'btc']
     * @return array|null
     */
    public function getDepths($pairs)
    {
        $query = $this->prepareQueryForPairs($pairs);
        $response = $this->client->get('depth/' . $query);
        $result = json_decode((string) $response->getBody(), true);

        return $result;
    }

    /**
     * @return array|null
     */
    public function getDepth(string $from, string $to)
    {
        return $this->getDepths([new CurrencyPair($from, $to)]);
    }

    /**
     * @param CurrencyPair[] $pairs -> example ['ltc' => 'btc']
     * @return array|null
     */
    public function getTrades(array $pairs)
    {
        $query = $this->prepareQueryForPairs($pairs);
        $response = $this->client->get('trades/' . $query);
        $result = json_decode((string) $response->getBody(), true);

        return $result;
    }

    /**
     * @return array|null
     */
    public function getTrade(string $from, string $to)
    {
        return $this->getTrades([new CurrencyPair($from, $to)]);
    }

    /**
     * @param CurrencyPair[] $pairs -> example ['ltc' => 'btc']
     * @return array|null
     */
    public function getTickers(array $pairs)
    {
        $query = $this->prepareQueryForPairs($pairs);
        $response = $this->client->get('ticker/' . $query);
        $result = json_decode((string) $response->getBody(), true);

        return $result;
    }

    /**
     * @return array|null
     */
    public function getTicker(string $from, string $to)
    {
        return $this->getTickers([new CurrencyPair($from, $to)]);
    }
}
