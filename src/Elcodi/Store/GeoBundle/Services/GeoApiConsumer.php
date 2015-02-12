<?php

/*
 * This file is part of the Elcodi package.
 *
 * Copyright (c) 2014 Elcodi.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 * @author Aldo Chiecchia <zimage@tiscali.it>
 * @author Elcodi Team <tech@elcodi.com>
 */

namespace Elcodi\Store\GeoBundle\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\Response;

/**
 * Class GeoApiConsumer
 */
class GeoApiConsumer
{
    /**
     * The http OK response code
     *
     * @var integer
     */
    const HTTP_OK_RESPONSE_CODE = 200;

    /**
     * @var Client
     *
     * A guzzle client
     */
    private $guzzleClient;

    /**
     * @var string
     *
     * The simple geo api url
     */
    private $simpleGeoApiUrl;

    /**
     * Builds a new geo api address finder
     *
     * @param Client $guzzleClient    A guzzle client instance to make api calls
     * @param string $simpleGeoApiUrl The url for the simple geo api
     */
    public function __construct(
        Client $guzzleClient,
        $simpleGeoApiUrl
    ) {
        $this->guzzleClient    = $guzzleClient;
        $this->simpleGeoApiUrl = $simpleGeoApiUrl;
    }

    /**
     * Get the info for an area.
     *
     * @param string $areaId The area id
     *
     * @return array
     */
    public function getAreaInfo($areaId)
    {
        $url = sprintf(
            '%s/area/%s',
            $this->simpleGeoApiUrl,
            $areaId
        );

        try {
            $response = $this->guzzleClient->get($url);
        } catch (RequestException $e) {
            return [];
        }

        return $this->formatResponse($response);
    }

    /**
     * Get the childrens for the received parent id.
     *
     * @param string|null $parentAreaId The parent id.
     *
     * @return array
     */
    public function getChildrenAreas($parentAreaId = null)
    {
        try {
            if (is_null($parentAreaId)) {
                $url      = sprintf(
                    '%s/countries',
                    $this->simpleGeoApiUrl
                );
                $response = $this->guzzleClient->get($url);
            } else {
                $url      = sprintf(
                    '%s/area/%s/childrens',
                    $this->simpleGeoApiUrl,
                    $parentAreaId
                );
                $response = $this->guzzleClient->get($url);
            }
        } catch (RequestException $e) {
            return [];
        }

        return $this->formatResponse($response);
    }

    /**
     * Gets the location for a city (From country to city).
     *
     * @param string $cityId The city identifier
     *
     * @return array the full location
     */
    public function getCityLocation($cityId)
    {
        $url = sprintf(
            '%s/city/%s/location',
            $this->simpleGeoApiUrl,
            $cityId
        );

        try {
            $response = $this->guzzleClient->get($url);
        } catch (RequestException $e) {
            return [];
        }
        return $this->formatResponse($response);
    }

    /**
     * Formats the response from the api.
     *
     * @param mixed $response The API response to format.
     *
     * @return array
     */
    protected function formatResponse($response)
    {
        if (
            !is_null($response) &&
            self:: HTTP_OK_RESPONSE_CODE === $response->getStatusCode()
        ) {
            $content = $response->getBody()->getContents();
            return json_decode($content, true);
        }

        return [];
    }
}
