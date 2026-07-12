@extends('layouts.app')

@section('title', 'Upload #' . $upload->id . ' — Importify')

@push('styles')
<style>
    /* ── Hero ────────────────────────────────────────────────── */
    .detail-hero {
        background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
        border-radius: 1rem;
        padding: 2rem 2.5rem;
        color: #fff;
        margin-bottom: 2rem;
    }
    .detail-hero h1 { font-size: 1.6rem; font-weight: 700; }

    /* ── Info card ───────────────────────────────────────────── */
    .info-card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 4px 24px rgba(0,0,0,.07);
        overflow: hidden;
    }
    .info-card .card-header {
        background: #fff;
        border-bottom: 1px solid #f0f0f5;
        padding: 1rem 1.5rem;
        font-weight: 700;
        font-size: .95rem;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    /* ── Stat pill row ───────────────────────────────────────── */
    .stat-pill {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 1.2rem 1rem;
        border-radius: .75rem;
        flex: 1 1 0;
        min-width: 100px;
    }
    .stat-pill .value { font-size: 1.75rem; font-weight: 700; line-height: 1; }
    .stat-pill .label { font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; opacity: .7; font-weight: 600; margin-top: .25rem; }

    /* ── Section cards ───────────────────────────────────────── */
    .section-card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 4px 24px rgba(0,0,0,.07);
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
    .table tbody tr { vertical-align: middle; }
    .table tbody tr:hover { background: #f8f9ff; }

    /* ── Empty state ─────────────────────────────────────────── */
    .empty-state {
        padding: 3rem 1rem;
        text-align: center;
        color: #adb5bd;
    }
    .empty-state i { font-size: 2.5rem; display: block; margin-bottom: .75rem; }
    .empty-state p { margin: 0; font-size: .9rem; }

    /* ── Progress bar ────────────────────────────────────────── */
    .progress { height: 8px; border-radius: 99px; }
    .progress-bar { border-radius: 99px; }

    /* ── Pagination ──────────────────────────────────────────── */
    .pagination { margin-bottom: 0; }
</style>
@endpush

@section('content')

{{-- ── Back Breadcrumb ─────────────────────────────────────────── --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard.index') }}" class="text-decoration-none">
                <i class="bi bi-speedometer2 me-1"></i>Dashboard
            </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Upload #{{ $upload->id }}</li>
    </ol>
</nav>

{{-- ── Hero ─────────────────────────────────────────────────────── --}}
<div class="detail-hero d-flex flex-column flex-md-row justify-content-md-between align-items-md-center gap-3">
    <div>
        <h1 class="mb-1">
            <i class="bi bi-cloud-arrow-up-fill me-2"></i>Upload #{{ $upload->id }}
        </h1>
        <p class="mb-0 opacity-75 small">{{ $upload->original_filename }}</p>
    </div>
    <x-status-badge :status="$upload->status" />
</div>

{{-- ── Upload Information Card ─────────────────────────────────── --}}
<div class="card info-card mb-4">
    <div class="card-header">
        <i class="bi bi-info-circle text-primary"></i>
        Upload Information
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <dl class="row mb-0">
                    <dt class="col-sm-5 text-muted small fw-semibold">Original Filename</dt>
                    <dd class="col-sm-7 fw-semibold">{{ $upload->original_filename }}</dd>

                    <dt class="col-sm-5 text-muted small fw-semibold">Stored Filename</dt>
                    <dd class="col-sm-7 font-monospace small text-muted">{{ $upload->stored_filename }}</dd>

                    <dt class="col-sm-5 text-muted small fw-semibold">Status</dt>
                    <dd class="col-sm-7"><x-status-badge :status="$upload->status" /></dd>

                    <dt class="col-sm-5 text-muted small fw-semibold">Uploaded At</dt>
                    <dd class="col-sm-7 small">{{ $upload->created_at->format('d M Y, H:i:s') }}</dd>

                    <dt class="col-sm-5 text-muted small fw-semibold">Last Updated</dt>
                    <dd class="col-sm-7 small">{{ $upload->updated_at->format('d M Y, H:i:s') }}</dd>
                </dl>
            </div>

            <div class="col-12 col-md-6">
                {{-- Progress Summary Pills --}}
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <div class="stat-pill bg-light">
                        <span class="value text-dark">{{ $upload->total_records ?? 0 }}</span>
                        <span class="label">Total</span>
                    </div>
                    <div class="stat-pill bg-info-subtle">
                        <span class="value text-info">{{ $upload->processed_records ?? 0 }}</span>
                        <span class="label">Processed</span>
                    </div>
                    <div class="stat-pill bg-success-subtle">
                        <span class="value text-success">{{ $upload->successful_records ?? 0 }}</span>
                        <span class="label">Successful</span>
                    </div>
                    <div class="stat-pill bg-danger-subtle">
                        <span class="value text-danger">{{ $upload->failed_records ?? 0 }}</span>
                        <span class="label">Failed</span>
                    </div>
                </div>

                {{-- Progress Bar --}}
                @if($upload->total_records > 0)
                    @php
                        $successPct = round(($upload->successful_records / $upload->total_records) * 100);
                        $failedPct  = round(($upload->failed_records  / $upload->total_records) * 100);
                    @endphp
                    <div class="mb-1 d-flex justify-content-between small text-muted">
                        <span>Import Progress</span>
                        <span>{{ $successPct }}% success</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: {{ $successPct }}%"
                             title="{{ $successPct }}% successful"></div>
                        <div class="progress-bar bg-danger" style="width: {{ $failedPct }}%"
                             title="{{ $failedPct }}% failed"></div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── Products Table ───────────────────────────────────────────── --}}
<div class="card section-card mb-4">
    <div class="card-header">
        <i class="bi bi-box-seam text-info"></i>
        Products
        <span class="badge-count">{{ $products->total() }}</span>
    </div>
    <div class="card-body p-0">
        @if($products->isEmpty())
            <div class="empty-state">
                <i class="bi bi-box"></i>
                <p>No products found for this upload.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Title</th>
                            <th>SKU</th>
                            <th>Vendor</th>
                            <th>Type</th>
                            <th class="text-end">Price</th>
                            <th>Status</th>
                            <th class="pe-4">Shopify ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td class="ps-4 fw-semibold">{{ Str::limit($product->title, 35) ?? '—' }}</td>
                            <td class="small font-monospace text-muted">{{ $product->sku ?? '—' }}</td>
                            <td class="small">{{ $product->vendor ?? '—' }}</td>
                            <td class="small text-muted">{{ $product->product_type ?? '—' }}</td>
                            <td class="text-end fw-semibold small">
                                {{ $product->price !== null ? '$'.number_format($product->price, 2) : '—' }}
                            </td>
                            <td><x-status-badge :status="$product->status" /></td>
                            <td class="pe-4 small font-monospace text-muted">
                                {{ $product->shopify_product_id ?? '—' }}
                            </td>
                        </tr>
                        @if($product->error_message)
                        <tr class="table-danger">
                            <td colspan="7" class="ps-4 small text-danger py-1">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                {{ $product->error_message }}
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
                <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                    <span class="small text-muted">
                        Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }}
                    </span>
                    {{ $products->appends(request()->except('products_page'))->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

{{-- ── Import Logs Table ────────────────────────────────────────── --}}
<div class="card section-card mb-4">
    <div class="card-header">
        <i class="bi bi-journal-text text-secondary"></i>
        Import Logs
        <span class="badge-count">{{ $logs->total() }}</span>
    </div>
    <div class="card-body p-0">
        @if($logs->isEmpty())
            <div class="empty-state">
                <i class="bi bi-journal"></i>
                <p>No log entries found for this upload.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Level</th>
                            <th>Event</th>
                            <th>Message</th>
                            <th class="pe-4">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr>
                            <td class="ps-4"><x-log-badge :level="$log->level" /></td>
                            <td class="small fw-semibold text-muted">{{ $log->event ?? '—' }}</td>
                            <td class="small">{{ $log->message }}</td>
                            <td class="pe-4 small text-muted text-nowrap">
                                {{ $log->created_at->format('H:i:s') }}<br>
                                <span class="opacity-75">{{ $log->created_at->format('d M Y') }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                    <span class="small text-muted">
                        Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }}
                    </span>
                    {{ $logs->appends(request()->except('logs_page'))->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

@endsection
