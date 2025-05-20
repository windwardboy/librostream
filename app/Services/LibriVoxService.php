<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LibriVoxService
{
    protected string $baseUrl = 'https://librivox.org/api/feed/audiobooks/';

    /**
     * Fetches audiobooks from the LibriVox API.
     *
     * @param int $limit The number of records to fetch.
     * @param int $offset The offset for pagination.
     * @param array $params Additional parameters for the API query (e.g., author, title, since).
     * @return array An array of audiobook data, or an empty array on failure.
     */
    public function fetchAudiobooks(int $limit = 5, int $offset = 0, array $params = []): array
    {
        $defaultParams = [
            'format' => 'json',
            'extended' => '1', // To get more details including sections
            'limit' => $limit,
            'offset' => $offset,
        ];

        // Merge default params with any custom params provided
        // Custom params will overwrite defaults if keys are the same
        $queryParams = array_merge($defaultParams, $params);

        try {
            $response = Http::timeout(30)->get($this->baseUrl, $queryParams);

            if ($response->successful()) {
                $data = $response->json();
                // The audiobooks are usually under a 'books' key
                return $data['books'] ?? [];
            } else {
                Log::error('LibriVox API request failed.', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'url' => $this->baseUrl,
                    'params' => $queryParams
                ]);
                return [];
            }
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('LibriVox API request exception: ' . $e->getMessage(), [
                'url' => $this->baseUrl,
                'params' => $queryParams,
                'exception' => $e
            ]);
            return [];
        } catch (\Exception $e) {
            Log::error('An unexpected error occurred while fetching from LibriVox API: ' . $e->getMessage(), [
                'url' => $this->baseUrl,
                'params' => $queryParams,
                'exception' => $e
            ]);
            return [];
        }
    }
}
