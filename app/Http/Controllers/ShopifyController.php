<?php

namespace App\Http\Controllers;

use App\Services\ShopifyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ShopifyController extends Controller
{
    public function __construct(
        protected ShopifyService $shopifyService
    ) {
    }

    /**
     * Display all products from the configured Shopify collection.
     */
    public function collection(Request $request): View
    {
        $collectionName = null;
        $products = [];
        $pageInfo = [];
        $perPage = (int) $request->query('per_page', 10);
        $cursor = $request->query('cursor') ?: null;
        $direction = $request->query('direction', 'next');
        $error = null;

        // Clamp per_page to sensible bounds (Shopify max is 250 per request)
        $perPage = max(5, min($perPage, 100));

        try {
            // dd($cursor, $perPage, $direction);
            $data = $this->shopifyService->getCollectionProducts($cursor, $perPage, $direction);
            $collectionName = $data['collection_name'];
            $products = $data['products'];
            $pageInfo = $data['page_info'];

        } catch (\Exception $e) {
            Log::error('ShopifyController: failed to load collection products', [
                'message' => $e->getMessage(),
            ]);
            $error = 'Unable to load products from Shopify. Please try again later.';
        }

        return view('shopify.collection', compact(
            'collectionName',
            'products',
            'pageInfo',
            'perPage',
            'cursor',
            'error'
        ));
    }
}
