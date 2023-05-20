<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\FollowAuthorRequest;

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
            // TODO: use it in home page with news feed!
            $preferred_sources = implode(',', $user->preferred_sources);

            $response = Http::get(env('NEWS_API_BASE_URL') . '/everything', [
                'apiKey' => env('NEWS_API_KEY'),
                'q' => $request->q,
                'to' => $request->to_date,
                'from' => $request->from_date,
                'sources' => $request->source,
            ]);
            $articles = $response->json()['articles'];

            return response(['articles' => $articles]);
        } catch (\Exception $e) {
            return response([
                'error' => 'Failed to fetch news articles!',
                'articles' => [],
                'message' => $e,
            ]);
        }
    }

    public function getFollowedAuthor()
    {
        $user = auth()->user();

        return response([
            'preferred_authors' => $user->preferred_authors ?? []
        ]);
    }

    public function followAuthor(FollowAuthorRequest $request)
    {
        try {
            $user = auth()->user();
            $authorName = $request->author_name;
            $preferredAuthors = $user->preferred_authors ?? [];

            if (!in_array($authorName, $preferredAuthors)) {
                $preferredAuthors[] = $authorName;
            }

            $user->preferred_authors = $preferredAuthors;
            $user->save();

            return response(['message' => 'You are now following ' . $authorName]);
        } catch (\Exception $e) {
            return response([
                'error' => 'Failed to follow author!',
                'message' => $e,
            ]);
        }
    }

    public function unfollowAuthor(FollowAuthorRequest $request)
    {
        try {
            $user = auth()->user();
            $authorName = $request->author_name;
            $preferredAuthors = $user->preferred_authors ?? [];

            $preferredAuthors = array_filter($preferredAuthors, function ($author) use ($authorName) {
                return $author !== $authorName;
            }, ARRAY_FILTER_USE_BOTH);

            $user->preferred_authors = $preferredAuthors;
            $user->save();

            return response(['message' => 'You are now not following ' . $request->author_name]);
        } catch (\Exception $e) {
            return response([
                'error' => 'Failed to unfollow author!',
                'message' => $e,
            ]);
        }
    }
}
