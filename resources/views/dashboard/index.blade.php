@extends('layouts.app')

@section('title', 'Dashboard — Importify')

@push('styles')
<style>
    /* ── Page chrome ─────────────────────────────────────────── */
    .dashboard-hero {
        background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
        border-radius: 1rem;
        padding: 2rem 2.5rem;
        color: #fff;
        margin-bottom: 2rem;
    }
    .dashboard-hero h1 { font-size: 1.75rem; font-weight: 700; }
    .dashboard-hero p  { opacity: .85; margin-bottom: 0; }

    /* ── Stat cards ──────────────────────────────────────────── */
    .stat-card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 4px 24px rgba(0,0,0,.07);
        transition: transform .2s ease, box-shadow .2s ease;
        overflow: hidden;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 32px rgba(0,0,0,.12);
    }
    .stat-card .card-body { padding: 1.5rem; }
    .stat-icon {
        width: 54px; height: 54px;
        border-radius: .75rem;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    .stat-value { font-size: 2rem; font-weight: 700; line-height: 1; }
    .stat-label { font-size: .8rem; text-transform: uppercase; letter-spacing: .05em; opacity: .65; font-weight: 600; }

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

    /* ── Table tweaks ────────────────────────────────────────── */
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

    /* ── Pagination ──────────────────────────────────────────── */
    .pagination { margin-bottom: 0; }
</style>
@endpush

@section('content')

{{-- ── Hero ──────────────────────────────────────────────────── --}}
<div class="dashboard-hero d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3">
    <div>
        <h1 class="mb-1">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </h1>
        <p>Monitor your Shopify CSV import pipeline at a glance.</p>
    </div>
    <a href="{{ route('upload.index') }}" class="btn btn-light btn-sm fw-semibold px-4 align-self-start align-self-md-center">
        <i class="bi bi-upload me-2"></i>New Upload
    </a>
</div>

{{-- ── Section 1 — Summary Cards ───────────────────────────────── --}}
<div class="row g-4 mb-4">

    {{-- Total Uploads --}}
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary">
                    <i class="bi bi-cloud-arrow-up-fill"></i>
                </div>
                <div>
                    <div class="stat-value text-primary">{{ number_format($statistics['total_uploads']) }}</div>
                    <div class="stat-label">Total Uploads</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Total Products --}}
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-info-subtle text-info">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
                <div>
                    <div class="stat-value text-info">{{ number_format($statistics['total_products']) }}</div>
                    <div class="stat-label">Total Products</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Successful Imports --}}
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success-subtle text-success">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <div class="stat-value text-success">{{ number_format($statistics['successful_imports']) }}</div>
                    <div class="stat-label">Successful Imports</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Failed Imports --}}
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-danger-subtle text-danger">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
                <div>
                    <div class="stat-value text-danger">{{ number_format($statistics['failed_imports']) }}</div>
                    <div class="stat-label">Failed Imports</div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── Section 2 — Recent Uploads ──────────────────────────────── --}}
<div class="card section-card mb-4">
    <div class="card-header">
        <i class="bi bi-cloud-upload text-primary"></i>
        Recent Uploads
        <span class="badge-count">{{ $recentUploads->count() }}</span>
    </div>
    <div class="card-body p-0">
        @if($recentUploads->isEmpty())
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>No uploads yet. <a href="{{ route('upload.index') }}">Upload your first CSV</a>.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Filename</th>
                            <th>Status</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Processed</th>
                            <th class="text-end">Successful</th>
                            <th class="text-end">Failed</th>
                            <th>Date</th>
                            <th class="pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentUploads as $upload)
                        <tr>
                            <td class="ps-4 text-muted small">{{ $upload->id }}</td>
                            <td>
                                <span class="fw-semibold" title="{{ $upload->original_filename }}">
                                    {{ Str::limit($upload->original_filename, 30) }}
                                </span>
                            </td>
                            <td><x-status-badge :status="$upload->status" /></td>
                            <td class="text-end">{{ $upload->total_records ?? '—' }}</td>
                            <td class="text-end">{{ $upload->processed_records ?? '—' }}</td>
                            <td class="text-end text-success fw-semibold">{{ $upload->successful_records ?? '—' }}</td>
                            <td class="text-end text-danger fw-semibold">{{ $upload->failed_records ?? '—' }}</td>
                            <td class="small text-muted">{{ $upload->created_at->format('d M Y, H:i') }}</td>
                            <td class="pe-4">
                                <a href="{{ route('dashboard.show', $upload->id) }}"
                                   class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- ── Section 3 — Recent Products ─────────────────────────────── --}}
<div class="card section-card mb-4">
    <div class="card-header">
        <i class="bi bi-box-seam text-info"></i>
        Recent Products
        <span class="badge-count">{{ $recentProducts->total() }}</span>
    </div>
    <div class="card-body p-0">
        @if($recentProducts->isEmpty())
            <div class="empty-state">
                <i class="bi bi-box"></i>
                <p>No products have been imported yet.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Title</th>
                            <th>SKU</th>
                            <th>Vendor</th>
                            <th class="text-end">Price</th>
                            <th>Status</th>
                            <th class="pe-4">Shopify ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentProducts as $product)
                        <tr>
                            <td class="ps-4 fw-semibold">{{ Str::limit($product->title, 35) ?? '—' }}</td>
                            <td class="text-muted small font-monospace">{{ $product->sku ?? '—' }}</td>
                            <td>{{ $product->vendor ?? '—' }}</td>
                            <td class="text-end fw-semibold">
                                {{ $product->price !== null ? '$'.number_format($product->price, 2) : '—' }}
                            </td>
                            <td><x-status-badge :status="$product->status" /></td>
                            <td class="pe-4 small font-monospace text-muted">
                                {{ $product->shopify_product_id ?? '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($recentProducts->hasPages())
                <div class="d-flex justify-content-end px-4 py-3 border-top">
                    {{ $recentProducts->appends(request()->except('products_page'))->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

{{-- ── Section 4 — Recent Import Logs ─────────────────────────── --}}
<div class="card section-card mb-4">
    <div class="card-header">
        <i class="bi bi-journal-text text-secondary"></i>
        Recent Import Logs
        <span class="badge-count">{{ $recentLogs->count() }}</span>
    </div>
    <div class="card-body p-0">
        @if($recentLogs->isEmpty())
            <div class="empty-state">
                <i class="bi bi-journal"></i>
                <p>No log entries found.</p>
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
                        @foreach($recentLogs as $log)
                        <tr>
                            <td class="ps-4"><x-log-badge :level="$log->level" /></td>
                            <td class="small fw-semibold text-muted">{{ $log->event ?? '—' }}</td>
                            <td class="small">{{ Str::limit($log->message, 80) }}</td>
                            <td class="pe-4 small text-muted text-nowrap">
                                {{ $log->created_at->diffForHumans() }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection
