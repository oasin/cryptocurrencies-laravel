<?php

namespace Oasin\Cryptocurrencies\Gateways\Coinmarketcap;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Oasin\Cryptocurrencies\Gateways\Gateway;

//TODO: create specific method for every coinmarketcap endpoint to permit custom cache time per request
class CoinmarketcapGateway extends Gateway
{
    /** @var string endpoint for coinmarketcap platform */
    protected $endpoint = '';

    protected $apiKey;

    /** @var $name string Name of gateway */
    protected $name = 'coinmarketcap';

    public function __construct(Client $http)
    {
        parent::__construct($http);

        $this->endpoint = config('cryptocurrencies.coinmarketcap.sandbox_mode') ?
            config('cryptocurrencies.coinmarketcap.sandbox_url') :
            config('cryptocurrencies.coinmarketcap.production_url');
    }

    /**
     * {@inheritdoc}
     * TODO: Cache response body as suggested by coinmarketcap
     */
    public function send($endpoint, $method = 'GET', $options = [])
    {

        $apiKey = config('cryptocurrencies.cryptocurrencies.api_key') ?? $this->getApiKey();

        if (!\Arr::get($options, 'headers.X-CMC_PRO_API_KEY')) {
            $options = array_merge($options, ['headers' => ['X-CMC_PRO_API_KEY' => $apiKey]]);
        }

        try {
            $response = $this->http->request($method, $this->endpoint . $endpoint, $options);
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
        }

        return $response->getBody()->getContents();
    }

    /**
     * Return ids of symbols on coinmarketcap
     *
     * @param string $listingStatus
     * @param int $start
     * @param int $limit
     * @param string $symbol
     * @return mixed|string
     */
    public function getCoinmarketcapId($listingStatus = 'active', $start = 1, $limit = 100, $symbol = "")
    {
        return $this->send('/v1/cryptocurrency/map', 'GET', [
            'listing_status' => $listingStatus,
            'start' => $start,
            'limit' => $limit,
            'symbol' => $symbol,
        ]);
    }

    /**
     * Get the value of apiKey
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set the value of apiKey
     *
     * @return  self
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }
}
