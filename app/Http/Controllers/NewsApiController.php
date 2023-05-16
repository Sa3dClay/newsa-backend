<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NewsApiController extends Controller
{
    public function getPreferences()
    {
        $user = auth()->user();

        return response([
            'preferred_sources' => $user->preferred_sources ?? [],
            'preferred_categories' => $user->preferred_categories ?? [],
        ]);
    }

    public function getPreferencesOptions()
    {
        try {
            $response = Http::get(env('NEWS_API_BASE_URL') . '/top-headlines/sources', [
                'apiKey' => env('NEWS_API_KEY'),
                'from' => Carbon::today(),
            ]);
            $sources = $response->json()['sources'];

            $categories = array_values(array_unique(array_column($sources, 'category')));
            $languages = array_values(array_unique(array_column($sources, 'language')));
            $countries = array_values(array_unique(array_column($sources, 'country')));
            $keys = array_values(array_unique(array_column($sources, 'id')));

            return response([
                'categories' => $categories,
                'languages' => $languages,
                'countries' => $countries,
                'sources' => $keys,
            ]);
        } catch (\Exception $e) {
            return response([
                'error' => 'Failed to fetch news sources!',
                'message' => $e,
            ]);
        }
    }

    public function savePreferences(Request $request)
    {
        try {
            $preferredSources = $request->input('preferred_sources', []);
            $preferredCategories = $request->input('preferred_categories', []);

            $user = auth()->user();
            $user->preferred_sources = $preferredSources;
            $user->preferred_categories = $preferredCategories;
            $user->save();

            return response(['message' => 'Preferences saved successfully']);
        } catch (\Exception $e) {
            return response([
                'error' => 'Failed to save preferences!',
                'message' => $e,
            ]);
        }
    }

    public function getNews(Request $request)
    {
        try {
            $user = auth()->user();
            $preferred_sources = implode(',', $user->preferred_sources);

            $response = Http::get(env('NEWS_API_BASE_URL') . '/everything', [
                'q' => $request->q ?? 'e',
                'apiKey' => env('NEWS_API_KEY'),
                'sources' => $preferred_sources,
                'from' => Carbon::yesterday()->format('M d Y'),
            ]);

            $data = $response->json();
            $articles = $data['articles'];

            return response(['articles' => $articles]);
        } catch (\Exception $e) {
            return response([
                'error' => 'Failed to fetch news articles!',
                'message' => $e,
            ]);
        }
    }
}
