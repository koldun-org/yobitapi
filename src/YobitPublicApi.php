<?php

namespace OlegStyle\YobitApi;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use OlegStyle\YobitApi\Exceptions\ApiDisabledException;
use OlegStyle\YobitApi\Models\CurrencyPair;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

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
     * @throws ApiDisabledException
     */
    public function sendResponse(string $url): ?array
    {
        try {
            $response = $this->client->get($url);
        } catch (ClientException $ex) {
            $response = $ex->getResponse();
        } catch (RequestException $ex) {
            $response = $ex->getResponse();
        }

        return $this->handleResponse($response);
    }

    /**
     * @throws ApiDisabledException
     */
    public function handleResponse(?ResponseInterface $response): ?array
    {
        if ($response === null) {
            throw new ApiDisabledException();
        }

        $responseBody = (string) $response->getBody();

        if ($response->getStatusCode() === 503) {
            throw new ApiDisabledException($responseBody);
        }

        if (preg_match('/ddos/i', $responseBody)) {
            throw new ApiDisabledException($responseBody);
        }

        return json_decode($responseBody, true);
    }

    /**
     * Get info about currencies
     *
     * @throws ApiDisabledException
     */
    public function getInfo(): ?array
    {
        return $this->sendResponse('info');
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
     *
     * @throws ApiDisabledException
     */
    public function getTrades(array $pairs)
    {
        $query = $this->prepareQueryForPairs($pairs);

        return $this->sendResponse('trades/' . $query);
    }

    /**
     * @return array|null
     *
     * @throws ApiDisabledException
     */
    public function getTrade(string $from, string $to)
    {
        return $this->getTrades([new CurrencyPair($from, $to)]);
    }

    /**
     * @param CurrencyPair[] $pairs -> example ['ltc' => 'btc']
     * @return array|null
     * @throws ApiDisabledException
     */
    public function getTickers(array $pairs)
    {
        $query = $this->prepareQueryForPairs($pairs);

        return $this->sendResponse('ticker/' . $query);
    }

    /**
     * @return array|null
     * @throws ApiDisabledException
     */
    public function getTicker(string $from, string $to)
    {
        return $this->getTickers([new CurrencyPair($from, $to)]);
    }
}
