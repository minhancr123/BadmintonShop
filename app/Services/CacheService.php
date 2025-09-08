<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheService
{
    // Cache durations in minutes
    const CACHE_PRODUCTS = 60;
    const CACHE_CATEGORIES = 120;
    const CACHE_FEATURED_PRODUCTS = 30;
    const CACHE_PRODUCT_DETAILS = 60;
    const CACHE_SEARCH_RESULTS = 15;
    const CACHE_USER_ORDERS = 30;

    /**
     * Cache products list
     */
    public function cacheProducts($key, $data, $duration = self::CACHE_PRODUCTS)
    {
        return Cache::tags(['products'])->put($key, $data, now()->addMinutes($duration));
    }

    /**
     * Get cached products
     */
    public function getCachedProducts($key)
    {
        return Cache::tags(['products'])->get($key);
    }

    /**
     * Cache categories
     */
    public function cacheCategories($key, $data, $duration = self::CACHE_CATEGORIES)
    {
        return Cache::tags(['categories'])->put($key, $data, now()->addMinutes($duration));
    }

    /**
     * Get cached categories
     */
    public function getCachedCategories($key)
    {
        return Cache::tags(['categories'])->get($key);
    }

    /**
     * Cache featured products
     */
    public function cacheFeaturedProducts($data, $duration = self::CACHE_FEATURED_PRODUCTS)
    {
        return Cache::tags(['products', 'featured'])->put('featured_products', $data, now()->addMinutes($duration));
    }

    /**
     * Get cached featured products
     */
    public function getCachedFeaturedProducts()
    {
        return Cache::tags(['products', 'featured'])->get('featured_products');
    }

    /**
     * Cache product details
     */
    public function cacheProductDetails($productId, $data, $duration = self::CACHE_PRODUCT_DETAILS)
    {
        $key = "product_details_{$productId}";
        return Cache::tags(['products', 'product_details'])->put($key, $data, now()->addMinutes($duration));
    }

    /**
     * Get cached product details
     */
    public function getCachedProductDetails($productId)
    {
        $key = "product_details_{$productId}";
        return Cache::tags(['products', 'product_details'])->get($key);
    }

    /**
     * Cache search results
     */
    public function cacheSearchResults($query, $filters, $data, $duration = self::CACHE_SEARCH_RESULTS)
    {
        $key = "search_" . md5($query . serialize($filters));
        return Cache::tags(['search'])->put($key, $data, now()->addMinutes($duration));
    }

    /**
     * Get cached search results
     */
    public function getCachedSearchResults($query, $filters)
    {
        $key = "search_" . md5($query . serialize($filters));
        return Cache::tags(['search'])->get($key);
    }

    /**
     * Cache user orders
     */
    public function cacheUserOrders($userId, $data, $duration = self::CACHE_USER_ORDERS)
    {
        $key = "user_orders_{$userId}";
        return Cache::tags(['orders', "user_{$userId}"])->put($key, $data, now()->addMinutes($duration));
    }

    /**
     * Get cached user orders
     */
    public function getCachedUserOrders($userId)
    {
        $key = "user_orders_{$userId}";
        return Cache::tags(['orders', "user_{$userId}"])->get($key);
    }

    /**
     * Invalidate product caches
     */
    public function invalidateProductCaches()
    {
        Cache::tags(['products'])->flush();
    }

    /**
     * Invalidate category caches
     */
    public function invalidateCategoryCaches()
    {
        Cache::tags(['categories'])->flush();
    }

    /**
     * Invalidate search caches
     */
    public function invalidateSearchCaches()
    {
        Cache::tags(['search'])->flush();
    }

    /**
     * Invalidate user-specific caches
     */
    public function invalidateUserCaches($userId)
    {
        Cache::tags(["user_{$userId}"])->flush();
    }

    /**
     * Invalidate order caches
     */
    public function invalidateOrderCaches()
    {
        Cache::tags(['orders'])->flush();
    }

    /**
     * Cache remember pattern - get from cache or execute callback
     */
    public function remember($key, $duration, $callback, $tags = [])
    {
        if (!empty($tags)) {
            return Cache::tags($tags)->remember($key, now()->addMinutes($duration), $callback);
        }
        
        return Cache::remember($key, now()->addMinutes($duration), $callback);
    }

    /**
     * Get cache statistics (for admin dashboard)
     */
    public function getCacheStats()
    {
        try {
            $redis = Redis::connection();
            $info = $redis->info();
            
            return [
                'status' => 'connected',
                'memory_used' => $info['used_memory_human'] ?? 'N/A',
                'total_keys' => $redis->dbsize(),
                'hits' => $info['keyspace_hits'] ?? 0,
                'misses' => $info['keyspace_misses'] ?? 0,
                'hit_rate' => $this->calculateHitRate($info['keyspace_hits'] ?? 0, $info['keyspace_misses'] ?? 0),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'disconnected',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Calculate cache hit rate
     */
    private function calculateHitRate($hits, $misses)
    {
        $total = $hits + $misses;
        if ($total === 0) return 0;
        
        return round(($hits / $total) * 100, 2);
    }

    /**
     * Clear all cache
     */
    public function clearAllCache()
    {
        Cache::flush();
    }

    /**
     * Warm up cache with popular data
     */
    public function warmUpCache()
    {
        // This method can be called from a scheduled job
        // to pre-populate cache with frequently accessed data
        
        // Example: Cache featured products
        if (!$this->getCachedFeaturedProducts()) {
            $featuredProducts = \App\Models\Product::where('is_featured', true)
                ->where('is_active', true)
                ->with('category')
                ->take(10)
                ->get();
            
            $this->cacheFeaturedProducts($featuredProducts);
        }

        // Example: Cache active categories
        if (!$this->getCachedCategories('active_categories')) {
            $categories = \App\Models\Category::where('is_active', true)
                ->orderBy('name')
                ->get();
            
            $this->cacheCategories('active_categories', $categories);
        }
    }

    /**
     * Increment page view counter (for popular products)
     */
    public function incrementProductViews($productId)
    {
        $key = "product_views_{$productId}";
        
        // Use Redis for fast increment
        try {
            $redis = Redis::connection();
            $redis->incr($key);
            $redis->expire($key, 86400); // Expire after 24 hours
        } catch (\Exception $e) {
            // Fallback to cache
            $views = Cache::get($key, 0);
            Cache::put($key, $views + 1, now()->addDay());
        }
    }

    /**
     * Get product view count
     */
    public function getProductViews($productId)
    {
        $key = "product_views_{$productId}";
        
        try {
            return Redis::connection()->get($key) ?? 0;
        } catch (\Exception $e) {
            return Cache::get($key, 0);
        }
    }

    /**
     * Cache shopping cart for guest users
     */
    public function cacheGuestCart($sessionId, $cartData)
    {
        $key = "guest_cart_{$sessionId}";
        Cache::put($key, $cartData, now()->addDays(7)); // 7 days for guest cart
    }

    /**
     * Get guest cart from cache
     */
    public function getGuestCart($sessionId)
    {
        $key = "guest_cart_{$sessionId}";
        return Cache::get($key, []);
    }

    /**
     * Remove guest cart from cache
     */
    public function clearGuestCart($sessionId)
    {
        $key = "guest_cart_{$sessionId}";
        Cache::forget($key);
    }
}
