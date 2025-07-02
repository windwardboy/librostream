<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LibriVoxService
{
    protected string $baseUrl = 'https://archive.org/advancedsearch.php';

    /**
     * Fetches audiobooks from the Archive.org Search API.
     *
     * @param int $limit The number of records to fetch.
     * @param int $offset The offset for pagination.
     * @param array $params Additional parameters for the API query.
     * @return array An array containing 'books' and 'total_found'.
     */
    public function fetchAudiobooks(int $limit = 5, int $offset = 0, array $params = []): array
    {
        $defaultParams = [
            'q' => 'subject:(librivox) AND mediatype:audio',
            'fl' => 'identifier,title,creator,description,publicdate,subject,runtime,avg_rating,num_reviews,language,image,url,format',
            'rows' => $limit,
            'start' => $offset,
            'output' => 'json',
        ];

        // Add date range to the query if provided
        if (isset($params['date_range'])) {
            $defaultParams['q'] .= " AND publicdate:[{$params['date_range']}]";
            unset($params['date_range']); // Remove from params to avoid duplication
        }

        $queryParams = array_merge($defaultParams, $params);

        try {
            $response = Http::timeout(60)->get($this->baseUrl, $queryParams);

            if ($response->successful()) {
                $data = $response->json();
                $audiobooks = $data['response']['docs'] ?? [];
                $numFound = $data['response']['numFound'] ?? 0;

                Log::info('Archive.org API successful response.', [
                    'status' => $response->status(),
                    'results_count' => count($audiobooks),
                    'total_found' => $numFound,
                    'offset' => $offset,
                ]);

                // Always return a consistent format
                return [
                    'books' => $audiobooks,
                    'total_found' => $numFound,
                ];
            } else {
                Log::error('Archive.org API request failed.', [
                    'status' => $response->status(),
                    'response_body' => $response->body(),
                ]);
                return ['books' => [], 'total_found' => 0];
            }
        } catch (\Exception $e) {
            Log::error('Archive.org API request exception: ' . $e->getMessage());
            return ['books' => [], 'total_found' => 0];
        }
    }
}
