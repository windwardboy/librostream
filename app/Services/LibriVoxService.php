<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LibriVoxService
{
    // Change base URL to Archive.org's advanced search API
    protected string $baseUrl = 'https://archive.org/advancedsearch.php';

    /**
     * Fetches audiobooks from the Archive.org Search API.
     *
     * @param int $limit The number of records to fetch.
     * @param int $offset The offset for pagination.
     * @param array $params Additional parameters for the API query.
     * @return array An array of audiobook data, or an empty array on failure.
     */
    public function fetchAudiobooks(int $limit = 5, int $offset = 0, array $params = []): array
    {
        $defaultParams = [
            // Query for LibriVox audiobooks
            'q' => 'collection:librivox AND mediatype:audio',
            'fl' => 'identifier,title,creator,description,publicdate,subject,runtime,avg_rating,num_reviews,language,image,url,format', // Fields to return as a comma-separated string
            'rows' => $limit, // Archive.org uses 'rows' instead of 'limit'
            'start' => $offset, // Archive.org uses 'start' instead of 'offset'
            'output' => 'json', // Request JSON output
        ];

        // Merge default params with any custom params provided
        $queryParams = array_merge($defaultParams, $params);

        try {
            $response = Http::timeout(60)->get($this->baseUrl, $queryParams);

            if ($response->successful()) {
                $data = $response->json();
                // Archive.org search results are typically under 'response' -> 'docs'
                $audiobooks = $data['response']['docs'] ?? [];

                Log::info('Archive.org API successful response.', [
                    'status' => $response->status(),
                    'results_count' => count($audiobooks),
                    'url' => $this->baseUrl,
                    'params' => $queryParams
                ]);

                return $audiobooks;
            } else {
                Log::error('Archive.org API request failed.', [
                    'status' => $response->status(),
                    'response_body' => $response->body(),
                    'url' => $this->baseUrl,
                    'params' => $queryParams
                ]);
                return [];
            }
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Archive.org API request exception: ' . $e->getMessage(), [
                'url' => $this->baseUrl,
                'params' => $queryParams,
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString()
            ]);
            return [];
        } catch (\Exception $e) {
            Log::error('An unexpected error occurred while fetching from Archive.org API: ' . $e->getMessage(), [
                'url' => $this->baseUrl,
                'params' => $queryParams,
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }
}
