<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadCsvRequest;
use App\Services\UploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Exception;

class UploadController extends Controller
{
    protected UploadService $uploadService;

    /**
     * UploadController constructor.
     */
    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    /**
     * Render the CSV upload index view.
     */
    public function index(): View
    {
        return view('upload.index');
    }

    /**
     * Handle the CSV upload submission.
     */
    public function store(UploadCsvRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $file = $request->file('csv_file');

            if (!$file) {
                throw new Exception('No file was uploaded.');
            }

            $result = $this->uploadService->upload($file);

            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'CSV file uploaded successfully.',
                    'data' => $result,
                ]);
            }

            return back()->with('success', 'CSV file uploaded successfully as: ' . $result['stored_filename']);
        } catch (Exception $e) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['csv_file' => $e->getMessage()]);
        }
    }
}
