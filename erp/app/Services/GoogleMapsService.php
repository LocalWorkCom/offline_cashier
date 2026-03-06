<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Http;

class GoogleMapsService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = config('services.google.maps_api_key');
    }

    /**
     * Get latitude and longitude for a given address.
     *
     * @param string $address
     * @return array|null
     * @throws GuzzleException
     */
    public function getLatLongFromAddress(string $address): ?array
    {

            $apiKey = env('GOOGLE_MAPS_API_KEY'); // Store your API key in .env

            $url = "https://maps.googleapis.com/maps/api/geocode/json";

            $response = Http::get($url, [
                'address' => $address,
                'key' => $apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] === 'OK') {
                    $location = $data['results'][0]['geometry']['location'];
                    $addressComponents = $data['results'][0]['address_components'];

                    $city = $state = $postalCode = null;

                    foreach ($addressComponents as $component) {
                        if (in_array('locality', $component['types'])) {
                            $city = $component['long_name'];
                        }

                        if (in_array('administrative_area_level_1', $component['types'])) {
                            $state = $component['long_name'];
                        }

                        if (in_array('postal_code', $component['types'])) {
                            $postalCode = $component['long_name'];
                        }
                    }

                    return [
                        'lat' => $location['lat'],
                        'lng' => $location['lng'],
                        'city' => $city,
                        'state' => $state,
                        'postal_code' => $postalCode,
                    ];
                }
            }

            return ['lat' => null, 'lng' => null, 'city' => null, 'state' => null, 'postal_code' => null];


    }
}
