<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LibriVoxService
{
    protected string $audiobooksBaseUrl = 'https://librivox.org/api/feed/audiobooks';
    protected string $audiotracksBaseUrl = 'https://librivox.org/api/feed/audiotracks';

    /**
     * Fetches audiobooks from the LibriVox API.
     *
     * @param int $limit The number of records to fetch.
     * @param int $offset The offset for pagination.
     * @param array $params Additional parameters for the API query.
     * @return array An array containing 'books' and 'total_found'.
     */
    public function fetchAudiobooks(int $limit = 5, int $offset = 0, array $params = []): array
    {
        $queryParams = [
            'format' => 'json',
            'extended' => 1, // Get full set of data
            'coverart' => 1, // Get cover art links
            'limit' => $limit,
            'offset' => $offset,
        ];

        // Merge additional parameters (e.g., title, author, genre, since)
        $queryParams = array_merge($queryParams, $params);

        try {
            $response = Http::timeout(60)->get($this->audiobooksBaseUrl, $queryParams);

            if ($response->successful()) {
                $data = $response->json();
                $audiobooks = $data['books'] ?? [];
                $numFound = $data['num_results'] ?? 0; // LibriVox API uses 'num_results'

                Log::info('LibriVox API successful response for audiobooks.', [
                    'status' => $response->status(),
                    'results_count' => count($audiobooks),
                    'total_found' => $numFound,
                    'offset' => $offset,
                ]);

                return [
                    'books' => $audiobooks,
                    'total_found' => $numFound,
                ];
            } else {
                Log::error('LibriVox API request failed for audiobooks.', [
                    'status' => $response->status(),
                    'response_body' => $response->body(),
                    'query_params' => $queryParams,
                ]);
                return ['books' => [], 'total_found' => 0];
            }
        } catch (\Exception $e) {
            Log::error('LibriVox API request exception for audiobooks: ' . $e->getMessage(), [
                'exception' => $e,
                'query_params' => $queryParams,
            ]);
            return ['books' => [], 'total_found' => 0];
        }
    }

    /**
     * Fetches detailed track (section) metadata for a specific audiobook from LibriVox API.
     *
     * @param int $projectId The LibriVox project ID.
     * @return array An array of track metadata, or empty array on failure.
     */
    public function fetchAudiobookTracks(int $projectId): array
    {
        $queryParams = [
            'format' => 'json',
            'project_id' => $projectId,
        ];

        try {
            $response = Http::timeout(60)->get($this->audiotracksBaseUrl, $queryParams);

                if ($response->successful()) {
                    $data = $response->json();
                    $tracks = $data['sections'] ?? []; // Corrected key from 'tracks' to 'sections'

                Log::info('LibriVox API successful response for audiotracks.', [
                    'project_id' => $projectId,
                    'status' => $response->status(),
                    'tracks_count' => count($tracks),
                ]);

                return $tracks;
            } else {
                Log::error('LibriVox API request failed for audiotracks.', [
                    'project_id' => $projectId,
                    'status' => $response->status(),
                    'response_body' => $response->body(),
                    'query_params' => $queryParams,
                ]);
                return [];
            }
        } catch (\Exception $e) {
            Log::error('LibriVox API request exception for audiotracks: ' . $e->getMessage(), [
                'exception' => $e,
                'project_id' => $projectId,
                'query_params' => $queryParams,
            ]);
            return [];
        }
    }
}
