<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ShopifyService
{
    public function importProduct(array $productData): array
    {
        Log::info('Import csv product');
        Log::info('Product Data', $productData);

        try {
            if (empty($productData['title'])) {
                throw new Exception('Product title is required.');
            }

            $shopUrl = env('SHOPIFY_SHOP_DOMAIN');
            $accessToken = env('SHOPIFY_ACCESS_TOKEN');
            $endpoint = "https://{$shopUrl}/admin/api/2026-07/graphql.json";

            $mutation = $this->buildProductSetMutation();

            // Map the data with the shopify data
            $status = (isset($productData['published']) && strtoupper($productData['published']) === 'FALSE') ? 'DRAFT' : 'ACTIVE';

            $input = [
                'title' => $productData['title'],
                'descriptionHtml' => $productData['body html'] ?? null,
                'vendor' => $productData['vendor'] ?? null,
                'productType' => $productData['product type'] ?? null,
                'status' => $status,
                'tags' => !empty($productData['tags'])
                    ? array_map('trim', explode(',', $productData['tags']))
                    : [],
            ];

            // Update the product if we have id.
            if (!empty($productData['shopify_product_id'])) {
                $input['id'] = str_contains($productData['shopify_product_id'], 'gid://')
                    ? $productData['shopify_product_id']
                    : "gid://shopify/Product/" . $productData['shopify_product_id'];
            }

            if (empty($productData['shopify_product_id']) && !empty($productData['handle'])) {
                $input['handle'] = trim($productData['handle']);
            }


            $optionName = $productData['option_name'] ?? 'Title';
            $optionValue = $productData['option_value'] ?? 'Default Title';

            $input['productOptions'] = [
                [
                    'name' => $optionName,
                    'values' => [['name' => $optionValue]]
                ]
            ];


            if (!empty($productData['variant sku']) || !empty($productData['variant price'])) {

                // Build the variant layout structure matching ProductVariantSetInput specifications
                $variantData = [
                    'price' => (string) ($productData['variant price'] ?? '0.00'),
                    'compareAtPrice' => !empty($productData['variant compare at price']) ? (string) $productData['variant compare at price'] : null,
                    'sku' => $productData['variant sku'] ?? null,
                    'taxable' => (isset($productData['variant taxable']) && strtoupper($productData['variant taxable']) === 'TRUE'),
                    'optionValues' => [
                        [
                            'optionName' => $optionName,
                            'name' => $optionValue
                        ]
                    ]
                ];

                if (!empty($productData['variant weight'])) {
                    $rawUnit = strtolower(trim($productData['variant weight unit'] ?? 'kg'));
                    $shopifyUnit = 'KILOGRAMS';

                    if ($rawUnit === 'g' || $rawUnit === 'grams') {
                        $shopifyUnit = 'GRAMS';
                    } elseif ($rawUnit === 'lb' || $rawUnit === 'lbs' || $rawUnit === 'pounds') {
                        $shopifyUnit = 'POUNDS';
                    } elseif ($rawUnit === 'oz' || $rawUnit === 'ounces') {
                        $shopifyUnit = 'OUNCES';
                    }

                    $variantData['inventoryItem'] = [
                        'measurement' => [
                            'weight' => [
                                'value' => (float) $productData['variant weight'],
                                'unit' => $shopifyUnit
                            ]
                        ]
                    ];
                }

                // update for variante data
                if (!empty($productData['shopify_variant_id'])) {
                    $variantData['id'] = str_contains($productData['shopify_variant_id'], 'gid://')
                        ? $productData['shopify_variant_id']
                        : "gid://shopify/ProductVariant/" . $productData['shopify_variant_id'];
                }

                $input['variants'] = [$variantData];
            }

            // map the images
            if (!empty($productData['image src'])) {
                $input['files'] = [
                    [
                        'originalSource' => $productData['image src'],
                        'contentType' => 'IMAGE',
                        'alt' => $productData['image alt text'] ?? $productData['title']
                    ]
                ];
            }

            // Connect to given collection
            $input['collections'] = [
                'gid://shopify/Collection/464337174767',
            ];

            $variables = [
                'input' => $input,
            ];

            Log::info('GraphQL Request', [
                'mutation' => 'PRODUCT_SET',
                'variables' => $variables,
            ]);

            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $accessToken,
                'Content-Type' => 'application/json',
            ])
                ->withoutVerifying()
                ->post($endpoint, [
                    'query' => $mutation,
                    'variables' => $variables,
                ]);

            Log::info('Shopify response', [
                'response' => $response->body(),
            ]);

            if ($response->failed()) {
                throw new Exception(
                    "Shopify GraphQL request failed with status: {$response->status()}"
                );
            }

            $body = $response->json();

            if (!empty($body['errors'])) {
                throw new Exception(
                    collect($body['errors'])->pluck('message')->implode(', ')
                );
            }

            $result = $body['data']['productSet'] ?? [];

            if (!empty($result['userErrors'])) {
                return [
                    'success' => false,
                    'shopify_product_id' => null,
                    'errors' => array_column($result['userErrors'], 'message'),
                ];
            }

            return [
                'success' => true,
                'shopify_product_id' => $result['product']['id'] ?? null,
                'errors' => [],
            ];

        } catch (Exception $e) {
            Log::error('Shopify Service Exception: ' . $e->getMessage(), [
                'product_data' => $productData,
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    protected function buildProductSetMutation()
    {
        return <<<'GRAPHQL'
        mutation productSet($input: ProductSetInput!) {
            productSet(input: $input) {
                product {
                    id
                    title
                    handle
                    vendor
                    productType
                    variants(first: 10) {
                        edges {
                            node {
                                id
                                title
                                price
                                sku
                            }
                        }
                    }
                }
                userErrors {
                    field
                    message
                }
            }
        }
        GRAPHQL;
    }

    // Get the collection from shopify
    public function getCollectionProducts(string $cursor = null, int $perPage = 10, string $direction = 'next')
    {
        $shopUrl = env('SHOPIFY_SHOP_DOMAIN');
        $accessToken = env('SHOPIFY_ACCESS_TOKEN');
        $collectionId = env('SHOPIFY_COLLECTION_ID');

        if (empty($shopUrl) || empty($accessToken) || empty($collectionId)) {
            throw new \Exception('Shopify credentials or collection ID are not configured.');
        }

        $endpoint = "https://{$shopUrl}/admin/api/2026-07/graphql.json";

        $variables = [
            'collectionId' => $collectionId,
            'first' => null,
            'last' => null,
            'after' => null,
            'before' => null,
        ];

        if ($direction === 'previous') {
            $variables['last'] = $perPage;
            $variables['before'] = $cursor;
        } else {
            $variables['first'] = $perPage;
            $variables['after'] = $cursor;
        }

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
            'Content-Type' => 'application/json',
        ])
            ->withoutVerifying()
            ->post($endpoint, [
                'query' => $this->buildCollectionQuery(),
                'variables' => $variables,
            ]);

        if ($response->failed()) {
            Log::error('Shopify collection fetch HTTP failure', [
                'status' => $response->status(),
                'collection_id' => $collectionId,
            ]);
            throw new \Exception(
                "Shopify API request failed with HTTP status: " . $response->status()
            );
        }

        $body = $response->json();

        // if api have any error
        if (!empty($body['errors'])) {
            $messages = array_map(fn($e) => $e['message'] ?? 'Unknown GraphQL error', $body['errors']);
            Log::error('Shopify GraphQL protocol errors', [
                'errors' => $body['errors'],
                'collection_id' => $collectionId,
            ]);
            throw new \Exception('Shopify API error: ' . implode(' | ', $messages));
        }

        $collection = $body['data']['collection'] ?? null;

        if ($collection === null) {
            Log::warning('Shopify collection not found', ['collection_id' => $collectionId]);
            throw new \Exception("Collection not found: {$collectionId}");
        }

        $collectionName = $collection['title'] ?? 'Unknown Collection';
        $productsConn = $collection['products'];
        $edges = $productsConn['edges'] ?? [];
        $pageInfo = $productsConn['pageInfo'] ?? [];

        $products = array_map(function (array $edge): array {
            $node = $edge['node'];
            $variant = $node['variants']['edges'][0]['node'] ?? [];
            $image = $node['featuredImage'] ?? null;

            return [
                'id' => $node['id'],
                'title' => $node['title'] ?? '',
                'handle' => $node['handle'] ?? '',
                'vendor' => $node['vendor'] ?? '',
                'product_type' => $node['productType'] ?? '',
                'status' => strtolower($node['status'] ?? 'unknown'),
                'image_url' => $image['url'] ?? null,
                'image_alt' => $image['altText'] ?? '',
                'sku' => $variant['sku'] ?? null,
                'price' => $variant['price'] ?? null,
                'cursor' => $edge['cursor'],
            ];
        }, $edges);

        // Log::info('Shopify collection products fetched', [
        //     'collection_id' => $collectionId,
        //     'collection_name' => $collectionName,
        //     'product_count' => count($products),
        //     'has_next_page' => $pageInfo['hasNextPage'] ?? false,
        // ]);

        return [
            'collection_name' => $collectionName,
            'products' => $products,
            'page_info' => [
                'has_next_page' => $pageInfo['hasNextPage'] ?? false,
                'has_previous_page' => $pageInfo['hasPreviousPage'] ?? false,
                'end_cursor' => $pageInfo['endCursor'] ?? null,
                'start_cursor' => $pageInfo['startCursor'] ?? null,
            ],
            'per_page' => $perPage,
        ];
    }

    private function buildCollectionQuery()
    {
        return <<<'GRAPHQL'
        query getCollectionProducts(
            $collectionId: ID!,
            $first: Int,
            $last: Int,
            $after: String,
            $before: String
        ) {
            collection(id: $collectionId) {
                title
                products(
                    first: $first,
                    last: $last,
                    after: $after,
                    before: $before
                ) {
                    pageInfo {
                        hasNextPage
                        hasPreviousPage
                        startCursor
                        endCursor
                    }
                    edges {
                        cursor
                        node {
                            id
                            title
                            handle
                            vendor
                            productType
                            status
                            featuredImage {
                                url
                                altText
                            }
                            variants(first: 1) {
                                edges {
                                    node {
                                        sku
                                        price
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        GRAPHQL;
    }
}
