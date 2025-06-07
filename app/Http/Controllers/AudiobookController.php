<?php

namespace App\Http\Controllers;

use App\Models\Audiobook;
use App\Models\Category; // Import the Category model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Import DB facade
use Illuminate\Support\Str; // Import Str facade for slugification

class AudiobookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Audiobook::query()->with('category')->whereNotNull('slug');

        // Search filter
        if ($request->filled('search')) {
            $searchTerm = '%' . strtolower($request->input('search')) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(title) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(author) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(narrator) LIKE ?', [$searchTerm]);
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Language filter
        if ($request->filled('language')) {
            $query->where('language', $request->input('language'));
        }

        $audiobooks = $query->orderBy('title')->paginate(12); // This is the potentially filtered list, now paginated

        // Fetch latest audiobooks (e.g., 4 newest)
        $latestAudiobooks = Audiobook::with('category')
                                     ->whereNotNull('slug') // Ensure latest audiobooks also have slugs
                                     ->latest() // Orders by created_at descending
                                     ->take(4)
                                     ->get();

        $categories = Category::orderBy('name')->get();

        // Fetch unique languages for the filter dropdown
        $languages = Audiobook::select('language')
                              ->distinct()
                              ->whereNotNull('language')
                              ->orderBy('language')
                              ->pluck('language');

        // Fetch counts for the features widget
        $totalAudiobooks = Audiobook::count();
        $uniqueLanguages = Audiobook::distinct('language')->whereNotNull('language')->count();
        // Assuming narrators are stored in the 'narrator' column of the audiobooks table
        $uniqueReaders = Audiobook::distinct('narrator')->whereNotNull('narrator')->count();


        return view('audiobooks.index', compact('audiobooks', 'categories', 'latestAudiobooks', 'languages', 'totalAudiobooks', 'uniqueLanguages', 'uniqueReaders'));
    }

    /**
     * Fetch audiobook details by slugs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBySlugs(Request $request)
    {
        $request->validate([
            'slugs' => 'required|array',
            'slugs.*' => 'string|exists:audiobooks,slug',
        ]);

        $slugs = $request->input('slugs');

        $audiobooks = Audiobook::with(['category', 'sections'])
                               ->whereIn('slug', $slugs)
                               ->whereNotNull('slug') // Ensure we only return valid slugs
                               ->get()
                               ->keyBy('slug'); // Key the collection by slug for easy lookup

        // Return audiobooks in the order of the requested slugs
        $orderedAudiobooks = collect($slugs)->map(function ($slug) use ($audiobooks) {
            return $audiobooks->get($slug);
        })->filter(); // Remove any null entries if a slug wasn't found

        return response()->json($orderedAudiobooks->values()); // Return as a simple array
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // For MVP, we might not need a create form if data comes from external sources
        return "Audiobook Create Form (Not Implemented for MVP)";
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // For MVP, data is seeded or fetched externally
        return "Audiobook Store Logic (Not Implemented for MVP)";
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Audiobook  $audiobook
     * @return \Illuminate\Http\Response
     */
    public function show(Audiobook $audiobook) // Route model binding
    {
        // Eager load sections
        $audiobook->load('sections');

        // The reader name is now stored directly on the audiobook_sections table
        // and is available via the 'sections' relationship.

        return view('audiobooks.show', compact('audiobook'));
    }

    /**
     * Display a listing of audiobooks by a specific tag.
     *
     * @param  string  $tag
     * @return \Illuminate\Http\Response
     */
    public function byTag($tag)
    {
        // Use the incoming tag slug directly for comparison in the database query
        $tagSlug = strtolower($tag);

        // Query audiobooks where the category slug, slugified author, or slugified narrator matches the tag slug
        $audiobooks = Audiobook::query()
            ->with('category')
            ->whereNotNull('slug')
            ->where(function ($query) use ($tagSlug) {
                // Match category by slug
                $query->whereHas('category', function ($q) use ($tagSlug) {
                    $q->where('slug', $tagSlug);
                });
                // OR Match author (slugified)
                $query->orWhere(DB::raw("LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(author, '''', ''), ',', '-'), '&', ''), ' ', '-'), '*', ''), '--', '-'), '--', '-'))"), 'LIKE', $tagSlug);
                // OR Match narrator (slugified)
                $query->orWhere(DB::raw("LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(narrator, '''', ''), ',', '-'), '&', ''), ' ', '-'), '*', ''), '--', '-'), '--', '-'))"), 'LIKE', $tagSlug);
            })
            ->orderBy('title')
            ->paginate(12);

        // Determine a display name for the tag
        // Try to find a category with a matching slug first
        $category = Category::where('slug', $tagSlug)->first();
        if ($category) {
            $tagName = $category->name;
        } else {
             // If no category matches the slug, try to find a matching author or narrator
             $author = Audiobook::select('author')->distinct()->where(DB::raw("LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(author, '''', ''), ',', '-'), '&', ''), ' ', '-'), '*', ''), '--', '-'), '--', '-'))"), 'LIKE', $tagSlug)->first();
             if ($author) {
                 $tagName = $author->author;
             } else {
                 $narrator = Audiobook::select('narrator')->distinct()->where(DB::raw("LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(narrator, '''', ''), ',', '-'), '&', ''), ' ', '-'), '*', ''), '--', '-'), '--', '-'))"), 'LIKE', $tagSlug)->first();
                 if ($narrator) {
                     $tagName = $narrator->narrator;
                 } else {
                     // If no match found, use the cleaned slug for display
                     $tagName = ucwords(str_replace('-', ' ', $tag));
                 }
             }
        }

        // Pass audiobooks and tag name to the view
        return view('audiobooks.by-tag', compact('audiobooks', 'tagName'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
