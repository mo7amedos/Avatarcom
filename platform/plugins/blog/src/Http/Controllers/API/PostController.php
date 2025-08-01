<?php

namespace Botble\Blog\Http\Controllers\API;

use Botble\Api\Http\Controllers\BaseApiController;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Blog\Http\Resources\ListPostResource;
use Botble\Blog\Http\Resources\PostResource;
use Botble\Blog\Models\Post;
use Botble\Blog\Repositories\Interfaces\PostInterface;
use Botble\Blog\Supports\FilterPost;
use Botble\Slug\Facades\SlugHelper;
use Illuminate\Http\Request;

class PostController extends BaseApiController
{
    public function __construct(protected PostInterface $postRepository)
    {
    }

    /**
     * List posts
     *
     * @group Blog
     *
     * @queryParam per_page integer The number of items to return per page (default: 10).
     * @queryParam page integer The page number to retrieve (default: 1).
     *
     * @response 200 {
     *   "error": false,
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Sample Post",
     *       "slug": "sample-post",
     *       "excerpt": "This is a sample post excerpt",
     *       "content": "Full post content here...",
     *       "published_at": "2023-01-01T00:00:00.000000Z",
     *       "author": {
     *         "id": 1,
     *         "name": "John Doe"
     *       },
     *       "categories": [],
     *       "tags": []
     *     }
     *   ],
     *   "message": null
     * }
     */
    public function index(Request $request)
    {
        $data = $this->postRepository
            ->advancedGet([
                'with' => ['tags', 'categories', 'author', 'slugable'],
                'condition' => ['status' => BaseStatusEnum::PUBLISHED],
                'paginate' => [
                    'per_page' => $request->integer('per_page', 10),
                    'current_paged' => $request->integer('page', 1),
                ],
            ]);

        return $this
            ->httpResponse()
            ->setData(ListPostResource::collection($data))
            ->toApiResponse();
    }

    /**
     * Search post
     *
     * @group Blog
     *
     * @bodyParam q string required The search keyword.
     *
     * @response 200 {
     *   "error": false,
     *   "data": {
     *     "items": [
     *       {
     *         "id": 1,
     *         "title": "Sample Post",
     *         "slug": "sample-post",
     *         "excerpt": "This is a sample post excerpt"
     *       }
     *     ],
     *     "query": "sample",
     *     "count": 1
     *   }
     * }
     *
     * @response 400 {
     *   "error": true,
     *   "message": "No search result"
     * }
     */
    public function getSearch(Request $request, PostInterface $postRepository)
    {
        $query = BaseHelper::stringify($request->input('q'));
        $posts = $postRepository->getSearch($query);

        $data = [
            'items' => $posts,
            'query' => $query,
            'count' => $posts->count(),
        ];

        if ($data['count'] > 0) {
            return $this
                ->httpResponse()
                ->setData(apply_filters(BASE_FILTER_SET_DATA_SEARCH, $data));
        }

        return $this
            ->httpResponse()
            ->setError()
            ->setMessage(trans('core/base::layouts.no_search_result'));
    }

    /**
     * Filters posts
     *
     * @group Blog
     * @queryParam page                 Current page of the collection. Default: 1
     * @queryParam per_page             Maximum number of items to be returned in result set.Default: 10
     * @queryParam search               Limit results to those matching a string.
     * @queryParam after                Limit response to posts published after a given ISO8601 compliant date.
     * @queryParam author               Limit result set to posts assigned to specific authors.
     * @queryParam author_exclude       Ensure result set excludes posts assigned to specific authors.
     * @queryParam before               Limit response to posts published before a given ISO8601 compliant date.
     * @queryParam exclude              Ensure result set excludes specific IDs.
     * @queryParam include              Limit result set to specific IDs.
     * @queryParam order                Order sort attribute ascending or descending. Default: desc .One of: asc, desc
     * @queryParam order_by             Sort collection by object attribute. Default: updated_at. One of: author, created_at, updated_at, id,  slug, title
     * @queryParam categories           Limit result set to all items that have the specified term assigned in the categories taxonomy.
     * @queryParam categories_exclude   Limit result set to all items except those that have the specified term assigned in the categories taxonomy.
     * @queryParam tags                 Limit result set to all items that have the specified term assigned in the tags taxonomy.
     * @queryParam tags_exclude         Limit result set to all items except those that have the specified term assigned in the tags taxonomy.
     * @queryParam featured             Limit result set to items that are sticky.
     */
    public function getFilters(Request $request)
    {
        $filters = FilterPost::setFilters($request->input());

        $data = $this->postRepository->getFilters($filters);

        return $this
            ->httpResponse()
            ->setData(ListPostResource::collection($data))
            ->toApiResponse();
    }

    /**
     * Get post by slug
     *
     * @group Blog
     * @queryParam slug Find by slug of post.
     */
    public function findBySlug(string $slug)
    {
        $slug = SlugHelper::getSlug($slug, SlugHelper::getPrefix(Post::class));

        if (! $slug) {
            return $this
                ->httpResponse()
                ->setError()
                ->setCode(404)
                ->setMessage('Not found');
        }

        $post = Post::query()
            ->where([
                'id' => $slug->reference_id,
                'status' => BaseStatusEnum::PUBLISHED,
            ])
            ->first();

        if (! $post) {
            return $this
                ->httpResponse()
                ->setError()
                ->setCode(404)
                ->setMessage('Not found');
        }

        return $this
            ->httpResponse()
            ->setData(new PostResource($post))
            ->toApiResponse();
    }
}
