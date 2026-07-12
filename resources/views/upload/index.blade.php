@extends('layouts.app')

@section('title', 'Upload CSV - Importify')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Card Header -->
            <div class="card-header bg-gradient bg-primary py-4 text-center border-0">
                <h4 class="text-white mb-1 fw-semibold">
                    <i class="bi bi-filetype-csv me-2"></i>CSV Upload Module
                </h4>
                <p class="text-white-50 mb-0 small">Securely upload your product CSV files for processing</p>
            </div>

            <!-- Card Body -->
            <div class="card-body p-4 p-md-5">
                <!-- Client-side / AJAX Alert Messages -->
                <div id="uploadAlert" class="alert d-none alert-dismissible fade show" role="alert">
                    <span id="uploadAlertMessage"></span>
                    <button type="button" class="btn-close" aria-label="Close" onclick="hideAlert()"></button>
                </div>

                <form id="csvUploadForm" action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Drag & Drop Zone -->
                    <div id="dropZone" class="upload-dropzone d-flex flex-column align-items-center justify-content-center border-2 border-dashed rounded-4 p-5 text-center cursor-pointer transition">
                        <input type="file" name="csv_file" id="csvFileInput" class="d-none" accept=".csv" aria-label="Choose CSV file">
                        
                        <div class="icon-wrapper mb-3 text-primary bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 72px; height: 72px;">
                            <i class="bi bi-cloud-arrow-up fs-1 animate-bounce"></i>
                        </div>
                        
                        <h5 class="fw-bold mb-1">Drag & Drop file here</h5>
                        <p class="text-muted small mb-3">or click to browse from your device</p>
                        
                        <div class="badge bg-light text-secondary border px-3 py-2 rounded-pill small">
                            Max size: {{ round(config('uploads.max_size', 10240) / 1024, 1) }}MB (CSV only)
                        </div>
                    </div>

                    <!-- Selected File Preview -->
                    <div id="filePreview" class="d-none mt-4 p-3 bg-light border rounded-3 align-items-center justify-content-between">
                        <div class="d-flex align-items-center overflow-hidden">
                            <i class="bi bi-file-earmark-bar-graph-fill text-success fs-2 me-3 flex-shrink-0"></i>
                            <div class="overflow-hidden">
                                <h6 id="previewFileName" class="mb-0 fw-semibold text-truncate">products.csv</h6>
                                <span id="previewFileSize" class="text-muted small">0 KB</span>
                            </div>
                        </div>
                        <button type="button" id="removeFileBtn" class="btn btn-outline-danger btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Remove File" aria-label="Remove selected file">
                            <i class="bi bi-trash3-fill"></i>
                        </button>
                    </div>

                    <!-- Submit / Loading Button -->
                    <div class="mt-4">
                        <button type="submit" id="submitBtn" class="btn btn-primary w-100 py-3 rounded-3 fw-bold transition shadow-sm d-flex align-items-center justify-content-center">
                            <span id="btnText"><i class="bi bi-upload me-2"></i>Upload File</span>
                            <div id="btnSpinner" class="spinner-border spinner-border-sm text-white ms-2 d-none" role="status">
                                <span class="visually-hidden">Uploading...</span>
                            </div>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .cursor-pointer {
        cursor: pointer;
    }
    
    .transition {
        transition: all 0.25s ease-in-out;
    }

    /* Drag & Drop Styling */
    .upload-dropzone {
        border-color: #dee2e6;
        background-color: #f8f9fa;
    }
    
    .upload-dropzone:hover, .upload-dropzone.dragover {
        border-color: #0d6efd;
        background-color: #e9ecef;
    }

    .upload-dropzone.dragover .icon-wrapper {
        transform: scale(1.1);
        transition: transform 0.2s ease;
    }

    .icon-wrapper i {
        display: inline-block;
    }

    /* Bounce micro-animation */
    @keyframes bounce {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-5px);
        }
    }
    
    .upload-dropzone:hover .animate-bounce {
        animation: bounce 1.5s infinite ease-in-out;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('csvFileInput');
        const filePreview = document.getElementById('filePreview');
        const previewFileName = document.getElementById('previewFileName');
        const previewFileSize = document.getElementById('previewFileSize');
        const removeFileBtn = document.getElementById('removeFileBtn');
        const uploadForm = document.getElementById('csvUploadForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');

        // Config limit passed dynamically from Laravel configuration (in Kilobytes)
        const MAX_SIZE_KB = {{ config('uploads.max_size', 10240) }};

        // Open file selector when clicking the dropzone
        dropZone.addEventListener('click', () => fileInput.click());

        // Drag & Drop event handlers
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.remove('dragover');
            }, false);
        });

        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length) {
                fileInput.files = files;
                handleFileSelection(files[0]);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length) {
                handleFileSelection(e.target.files[0]);
            }
        });

        // Remove selected file handler
        removeFileBtn.addEventListener('click', (e) => {
            e.stopPropagation(); // Avoid triggering file chooser dialog
            resetForm();
        });

        // Handle selected file details and client-side validation
        function handleFileSelection(file) {
            hideAlert();

            if (!file) {
                resetForm();
                return;
            }

            // Extension validation
            const ext = file.name.split('.').pop().toLowerCase();
            if (ext !== 'csv') {
                showAlert('danger', 'Error: Invalid file type. Only files with .csv extension are allowed.');
                resetForm();
                return;
            }

            // Size validation (convert bytes to KB)
            const fileSizeKb = file.size / 1024;
            if (fileSizeKb > MAX_SIZE_KB) {
                const maxMb = (MAX_SIZE_KB / 1024).toFixed(1);
                showAlert('danger', `Error: The file is too large. Maximum allowed size is ${maxMb}MB.`);
                resetForm();
                return;
            }

            // Format size for preview
            let sizeString = '';
            if (fileSizeKb < 1024) {
                sizeString = `${fileSizeKb.toFixed(1)} KB`;
            } else {
                sizeString = `${(fileSizeKb / 1024).toFixed(1)} MB`;
            }

            // Display file preview
            previewFileName.textContent = file.name;
            previewFileSize.textContent = sizeString;
            
            // Toggle view visibility
            dropZone.classList.add('d-none');
            filePreview.classList.remove('d-none');
            filePreview.classList.add('d-flex');
        }

        function resetForm() {
            fileInput.value = '';
            dropZone.classList.remove('d-none');
            filePreview.classList.remove('d-flex');
            filePreview.classList.add('d-none');
        }

        // Form Submit handler via AJAX (Fetch API)
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            hideAlert();

            const file = fileInput.files[0];

            // Client-side empty validation
            if (!file) {
                showAlert('danger', 'Error: Please choose or drag a CSV file first.');
                return;
            }

            // Show loading state
            setLoading(true);

            const formData = new FormData(uploadForm);

            fetch(uploadForm.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'An error occurred during file upload.');
                }
                return data;
            })
            .then(data => {
                showAlert('success', `<i class="bi bi-check-circle-fill me-2"></i>${data.message}<br><small class="d-block mt-1">Saved as: <code>${data.data.stored_filename}</code></small>`);
                resetForm();
            })
            .catch(error => {
                showAlert('danger', `<i class="bi-exclamation-triangle-fill me-2"></i>${error.message}`);
            })
            .finally(() => {
                setLoading(false);
            });
        });

        // Set Loading state on UI controls
        function setLoading(isLoading) {
            if (isLoading) {
                submitBtn.disabled = true;
                removeFileBtn.disabled = true;
                btnText.textContent = 'Uploading CSV...';
                btnSpinner.classList.remove('d-none');
            } else {
                submitBtn.disabled = false;
                removeFileBtn.disabled = false;
                btnText.innerHTML = '<i class="bi bi-upload me-2"></i>Upload File';
                btnSpinner.classList.add('d-none');
            }
        }
    });

    // Helper alerts visibility
    function showAlert(type, message) {
        const alertDiv = document.getElementById('uploadAlert');
        const alertMsg = document.getElementById('uploadAlertMessage');
        
        // Remove existing alert styling classes
        alertDiv.classList.remove('alert-success', 'alert-danger', 'd-none');
        alertDiv.classList.add(`alert-${type}`);
        alertMsg.innerHTML = message;
    }

    function hideAlert() {
        const alertDiv = document.getElementById('uploadAlert');
        alertDiv.classList.add('d-none');
    }
</script>
@endpush