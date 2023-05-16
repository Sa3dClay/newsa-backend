<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Exception\GuzzleException;

class NewsApiController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return response([
            'preferred_sources' => $user->preferred_sources ?? [],
            'preferred_categories' => $user->preferred_categories ?? [],
        ]);
    }

    public function options()
    {
        try {
            $response = Http::get(env('NEWS_API_BASE_URL') . '/sources', [
                'apiKey' => env('NEWS_API_KEY'),
                'from' => Carbon::today(),
            ]);
            $sources = $response->json()['sources'];

            $categories = array_values(array_unique(array_column($sources, 'category')));
            $languages = array_values(array_unique(array_column($sources, 'language')));
            $countries = array_values(array_unique(array_column($sources, 'country')));
            $domains = array_values(array_unique(array_column($sources, 'url')));

            return response([
                'categories' => $categories,
                'languages' => $languages,
                'countries' => $countries,
                'sources' => $domains,
            ]);
        } catch (GuzzleException $e) {
            return response([
                'error' => 'Failed to fetch news sources!',
                'message' => $e,
            ]);
        }
    }

    public function save(Request $request)
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
}
