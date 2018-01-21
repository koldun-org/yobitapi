<?php

namespace OlegStyle\YobitApi;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use OlegStyle\YobitApi\Exceptions\ApiDDosException;
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

    /**
     * @var string
     */
    protected $userAgent;

    public function __construct()
    {
        $this->userAgent = 'Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.4) Gecko/20030624 Netscape/7.1 (ax)';

        $this->client = new Client([
            'base_uri' => static::BASE_URI,
            'timeout' => 30.0,
            'headers' => [
                'User-Agent' => $this->userAgent,
                "Content-type: application/json"
            ]
        ]);
    }

    /**
     * @throws ApiDisabledException|ApiDDosException
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
     * @throws ApiDisabledException|ApiDDosException
     */
    public function handleResponse(?ResponseInterface $response): ?array
    {
        if ($response === null) {
            throw new ApiDisabledException();
        }

        $responseBody = (string) $response->getBody();

        if ($response->getStatusCode() === 503) { // cloudflare ddos protection
            throw new ApiDDosException($responseBody);
        }

        if (preg_match('/ddos/i', $responseBody)) {
            throw new ApiDDosException($responseBody);
        }

        return json_decode($responseBody, true);
    }

    /**
     * Get info about currencies
     *
     * @throws ApiDisabledException|ApiDDosException
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
     *
     * @throws ApiDisabledException|ApiDDosException
     */
    public function getDepths($pairs)
    {
        $query = $this->prepareQueryForPairs($pairs);

        return $this->sendResponse('depth/' . $query);
    }

    /**
     * @return array|null
     *
     * @throws ApiDisabledException|ApiDDosException
     */
    public function getDepth(string $from, string $to)
    {
        return $this->getDepths([new CurrencyPair($from, $to)]);
    }

    /**
     * @param CurrencyPair[] $pairs -> example ['ltc' => 'btc']
     * @return array|null
     *
     * @throws ApiDisabledException|ApiDDosException
     */
    public function getTrades(array $pairs)
    {
        $query = $this->prepareQueryForPairs($pairs);

        return $this->sendResponse('trades/' . $query);
    }

    /**
     * @return array|null
     *
     * @throws ApiDisabledException|ApiDDosException
     */
    public function getTrade(string $from, string $to)
    {
        return $this->getTrades([new CurrencyPair($from, $to)]);
    }

    /**
     * @param CurrencyPair[] $pairs -> example ['ltc' => 'btc']
     * @return array|null
     *
     * @throws ApiDisabledException|ApiDDosException
     */
    public function getTickers(array $pairs)
    {
        $query = $this->prepareQueryForPairs($pairs);

        return $this->sendResponse('ticker/' . $query);
    }

    /**
     * @return array|null
     *
     * @throws ApiDisabledException|ApiDDosException
     */
    public function getTicker(string $from, string $to)
    {
        return $this->getTickers([new CurrencyPair($from, $to)]);
    }
}
