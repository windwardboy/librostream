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
            $response = Http::timeout(60)->get($this->baseUrl, $queryParams);

            if ($response->successful()) {
                $data = $response->json();
                // Log successful response data for debugging
                Log::info('LibriVox API successful response.', [
                    'status' => $response->status(),
                    'data_keys' => array_keys($data),
                    'books_count' => count($data['books'] ?? []),
                    'url' => $this->baseUrl,
                    'params' => $queryParams
                ]);
                // The audiobooks are usually under a 'books' key
                return $data['books'] ?? [];
            } else {
                // Log failed response details for debugging
                Log::error('LibriVox API request failed.', [
                    'status' => $response->status(),
                    'response_body' => $response->body(), // Log the full response body
                    'url' => $this->baseUrl,
                    'params' => $queryParams
                ]);
                return [];
            }
        } catch (\Illuminate\Http\Client\RequestException $e) {
            // Log request exception details for debugging
            Log::error('LibriVox API request exception: ' . $e->getMessage(), [
                'url' => $this->baseUrl,
                'params' => $queryParams,
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString() // Log stack trace for detailed error
            ]);
            return [];
        } catch (\Exception $e) {
            // Log any other unexpected errors for debugging
            Log::error('An unexpected error occurred while fetching from LibriVox API: ' . $e->getMessage(), [
                'url' => $this->baseUrl,
                'params' => $queryParams,
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString() // Log stack trace for detailed error
            ]);
            return [];
        }
    }
}
