<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Display the main dashboard.
     */
    public function index(): View
    {
        $statistics    = $this->dashboardService->getStatistics();
        $recentUploads = $this->dashboardService->getRecentUploads();
        $recentProducts = $this->dashboardService->getRecentProducts();
        $recentLogs    = $this->dashboardService->getRecentLogs();

        return view('dashboard.index', compact(
            'statistics',
            'recentUploads',
            'recentProducts',
            'recentLogs'
        ));
    }

    /**
     * Display a single upload's detail page.
     */
    public function show(int $upload): View
    {
        $details = $this->dashboardService->getUploadDetails($upload);

        return view('dashboard.show', $details);
    }
}
