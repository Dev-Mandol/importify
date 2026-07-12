@extends('layouts.app')

@section('title', ($collectionName ?? 'Shopify Collection') . ' — Importify')

@push('styles')
    <style>
        /* ── Hero ────────────────────────────────────────────────── */
        .collection-hero {
            background: linear-gradient(135deg, #2d6a4f 0%, #1b4332 100%);
            border-radius: 1rem;
            padding: 2rem 2.5rem;
            color: #fff;
            margin-bottom: 2rem;
        }

        .collection-hero h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: .25rem;
        }

        .collection-hero p {
            opacity: .8;
            margin-bottom: 0;
            font-size: .95rem;
        }

        /* ── Stat card ───────────────────────────────────────────── */
        .stat-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .07);
        }

        .stat-card .card-body {
            padding: 1.5rem;
        }

        .stat-icon {
            width: 54px;
            height: 54px;
            border-radius: .75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }

        .stat-label {
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            opacity: .65;
            font-weight: 600;
        }

        /* ── Section card ────────────────────────────────────────── */
        .section-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .07);
            overflow: hidden;
        }

        .section-card .card-header {
            background: #fff;
            border-bottom: 1px solid #f0f0f5;
            padding: 1.1rem 1.5rem;
            font-weight: 700;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .section-card .card-header .badge-count {
            font-size: .7rem;
            background: #f0f0f5;
            color: #555;
            border-radius: 999px;
            padding: .2em .6em;
            font-weight: 600;
        }

        /* ── Product image thumbnail ─────────────────────────────── */
        .product-thumb {
            width: 52px;
            height: 52px;
            object-fit: cover;
            border-radius: .5rem;
            border: 1px solid #e9ecef;
            flex-shrink: 0;
        }

        .product-thumb-placeholder {
            width: 52px;
            height: 52px;
            border-radius: .5rem;
            background: #f0f0f5;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #adb5bd;
            font-size: 1.4rem;
            flex-shrink: 0;
            border: 1px solid #e9ecef;
        }

        /* ── Table ───────────────────────────────────────────────── */
        .table thead th {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #6c757d;
            font-weight: 700;
            background: #fafafa;
            border-bottom: 2px solid #f0f0f5;
            white-space: nowrap;
        }

        .table tbody tr {
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: #f6fff9;
        }

        /* ── Status badge map ────────────────────────────────────── */
        .badge-active {
            background: #198754;
        }

        .badge-draft {
            background: #6c757d;
        }

        .badge-archived {
            background: #fd7e14;
            color: #fff;
        }

        /* ── Empty state ─────────────────────────────────────────── */
        .empty-state {
            padding: 4rem 1rem;
            text-align: center;
            color: #adb5bd;
        }

        .empty-state i {
            font-size: 3rem;
            display: block;
            margin-bottom: 1rem;
        }

        .empty-state p {
            margin: 0;
            font-size: .95rem;
        }

        /* ── Product ID mono ─────────────────────────────────────── */
        .shopify-id {
            font-family: monospace;
            font-size: .7rem;
            color: #6c757d;
            word-break: break-all;
            max-width: 160px;
            display: inline-block;
        }
    </style>
@endpush

@section('content')

    {{-- ── Hero ──────────────────────────────────────────────────── --}}
    <div class="collection-hero d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3">
        <div>
            <h1>
                <i class="bi bi-shop me-2"></i>
                {{ $collectionName ?? 'Shopify Collection' }}
            </h1>
            <p>Products synced from your Shopify store collection.</p>
        </div>
        <a href="{{ route('upload.index') }}"
            class="btn btn-light btn-sm fw-semibold px-4 align-self-start align-self-md-center">
            <i class="bi bi-upload me-2"></i>Import More
        </a>
    </div>

    {{-- ── API Error Alert ──────────────────────────────────────── --}}
    @if($error)
        <div class="alert alert-danger d-flex align-items-start gap-3 rounded-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-octagon-fill fs-4 flex-shrink-0 mt-1"></i>
            <div>
                <strong>Shopify API Error</strong><br>
                <span class="small">{{ $error }}</span>
            </div>
        </div>
    @endif

    {{-- ── Stats Row ────────────────────────────────────────────── --}}
    @unless($error)
        <div class="row g-4 mb-4">
            <div class="col-12 col-md-4 col-lg-3">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-success-subtle text-success">
                            <i class="bi bi-box-seam-fill"></i>
                        </div>
                        <div>
                            <div class="stat-value text-success">{{ count($products) }}</div>
                            <div class="stat-label">Total Products</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endunless

    {{-- ── Products Table ───────────────────────────────────────── --}}
    @unless($error)
        <div class="card section-card mb-4">
            <div class="card-header">
                <i class="bi bi-grid text-success"></i>
                Products in Collection
                <span class="badge-count">{{ count($products) }}</span>
            </div>
            <div class="card-body p-0">

                @if(count($products) === 0)
                    {{-- Empty State --}}
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>No products found in this Shopify collection.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4" style="width:60px;">Image</th>
                                    <th>Product Title</th>
                                    <th>SKU</th>
                                    <th>Vendor</th>
                                    <th>Type</th>
                                    <th class="text-end">Price</th>
                                    <th>Handle</th>
                                    <th>Status</th>
                                    <th>Shopify ID</th>
                                    <th class="pe-4">View</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)
                                    @php
                                        $storefrontUrl = 'https://' . env('SHOPIFY_SHOP_DOMAIN') . '/products/' . $product['handle'];

                                        $statusClass = match ($product['status']) {
                                            'active' => 'bg-success',
                                            'draft' => 'bg-secondary',
                                            'archived' => 'bg-warning text-dark',
                                            default => 'bg-secondary',
                                        };
                                        $statusIcon = match ($product['status']) {
                                            'active' => 'bi-check-circle-fill',
                                            'draft' => 'bi-pencil-fill',
                                            'archived' => 'bi-archive-fill',
                                            default => 'bi-circle-fill',
                                        };

                                        // Extract numeric ID from GID for display
                                        $numericId = last(explode('/', $product['id']));
                                    @endphp
                                    <tr>
                                        {{-- Image --}}
                                        <td class="ps-4">
                                            @if($product['image_url'])
                                                <img src="{{ $product['image_url'] }}"
                                                    alt="{{ $product['image_alt'] ?: $product['title'] }}" class="product-thumb"
                                                    loading="lazy">
                                            @else
                                                <div class="product-thumb-placeholder">
                                                    <i class="bi bi-image"></i>
                                                </div>
                                            @endif
                                        </td>

                                        {{-- Title --}}
                                        <td class="fw-semibold">{{ Str::limit($product['title'], 40) }}</td>

                                        {{-- SKU --}}
                                        <td class="small font-monospace text-muted">
                                            {{ $product['sku'] ?: '—' }}
                                        </td>

                                        {{-- Vendor --}}
                                        <td class="small">{{ $product['vendor'] ?: '—' }}</td>

                                        {{-- Type --}}
                                        <td class="small text-muted">{{ $product['product_type'] ?: '—' }}</td>

                                        {{-- Price --}}
                                        <td class="text-end fw-semibold small">
                                            @if($product['price'] !== null && $product['price'] !== '')
                                                ${{ number_format((float) $product['price'], 2) }}
                                            @else
                                                —
                                            @endif
                                        </td>

                                        {{-- Handle --}}
                                        <td class="small text-muted font-monospace">
                                            {{ Str::limit($product['handle'], 24) }}
                                        </td>

                                        {{-- Status --}}
                                        <td>
                                            <span class="badge {{ $statusClass }} d-inline-flex align-items-center gap-1 px-2 py-1">
                                                <i class="bi {{ $statusIcon }}"></i>
                                                {{ ucfirst($product['status']) }}
                                            </span>
                                        </td>

                                        {{-- Shopify Product ID --}}
                                        <td>
                                            <span class="shopify-id" title="{{ $product['id'] }}">
                                                {{ $numericId }}
                                            </span>
                                        </td>

                                        {{-- View --}}
                                        <td class="pe-4">
                                            <a href="{{ $storefrontUrl }}" target="_blank" rel="noopener noreferrer"
                                                class="btn btn-sm btn-outline-success rounded-pill px-3">
                                                <i class="bi bi-box-arrow-up-right me-1"></i>View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-between mt-4">

                            @if($pageInfo['has_previous_page'])
                                    <a href="{{ route('shopify.collection', [
                                    'cursor' => $pageInfo['start_cursor'],
                                    'direction' => 'previous',
                                    'per_page' => $perPage
                                ]) }}" class="btn btn-outline-secondary">
                                        ← Previous
                                    </a>
                            @else
                                <span></span>
                            @endif

                            @if($pageInfo['has_next_page'])
                                    <a href="{{ route('shopify.collection', [
                                    'cursor' => $pageInfo['end_cursor'],
                                    'direction' => 'next',
                                    'per_page' => $perPage
                                ]) }}" class="btn btn-success">
                                        Next →
                                    </a>
                            @endif

                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endunless

@endsection