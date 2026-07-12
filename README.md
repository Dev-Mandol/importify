# 🚀 Shopify Product Importer

> A Laravel-based CSV Product Import System that imports products into Shopify using the GraphQL Admin API with asynchronous queue processing, import tracking, logging, and a responsive dashboard.

---

<p align="center">

![Laravel](https://img.shields.io/badge/Laravel-12-red?style=for-the-badge\&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.3-blue?style=for-the-badge\&logo=php)
![Shopify](https://img.shields.io/badge/Shopify-GraphQL-success?style=for-the-badge\&logo=shopify)
![MySQL](https://img.shields.io/badge/MySQL-9.1-orange?style=for-the-badge\&logo=mysql)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-purple?style=for-the-badge\&logo=bootstrap)

</p>

---

# 📖 Overview

This project is a Shopify Product Import System built with Laravel. Users can upload a Shopify-compatible CSV file, which is processed asynchronously using Laravel Queues. Each product is validated, imported into Shopify via the GraphQL Admin API, and its import status is tracked within the application.

The application emphasizes scalability, maintainability, and separation of concerns through a Service Layer architecture.

---

# ✨ Features

## Product Import

* Upload Shopify Product CSV
* CSV Validation
* Background Queue Processing
* Shopify GraphQL Integration
* Create Products
* Update Existing Products
* Assign Products to Collection
* Import Status Tracking
* Error Handling
* Event Logging

---

## Dashboard

* Upload History
* Import Status
* Success Count
* Failed Count
* Pending Count
* Processing Count
* Shopify Product ID
* Error Messages
* Product Listing

---

## Logging

* File Upload Events
* Queue Events
* CSV Parsing
* Shopify Requests
* Shopify Responses
* Exceptions
* Import Completion

---

# 🏗 High Level Design (HLD)

```text
                User
                  │
                  ▼
        Upload CSV File
                  │
                  ▼
        UploadController
                  │
                  ▼
          UploadService
                  │
                  ▼
         Store CSV Locally
                  │
                  ▼
        uploads Database
                  │
                  ▼
      Dispatch ImportCsvJob
                  │
                  ▼
          Laravel Queue
                  │
                  ▼
        CsvImportService
                  │
                  ▼
        ShopifyService
                  │
                  ▼
      Shopify GraphQL API
                  │
                  ▼
 Update Product Import Status
                  │
                  ▼
           Dashboard
```

---

# ⚙ Low Level Design (LLD)

## Upload Module

```text
User
 │
 ▼
UploadController
 │
 ▼
UploadService
 │
 ├── Validate CSV
 ├── Validate MIME
 ├── Validate Size
 ├── Store File
 ├── Create Upload Record
 └── Dispatch Queue
```

---

## Import Module

```text
ImportCsvJob
      │
      ▼
Open CSV File
      │
      ▼
Map CSV Headers
      │
      ▼
Process Each Row
      │
      ▼
ShopifyService
      │
      ▼
ProductSet Mutation
      │
      ▼
Update Status
```

---

# 📂 Project Structure

```text
app
│
├── Http
│   └── Controllers
│       ├── UploadController.php
│       ├── DashboardController.php
│       └── ProductController.php
│
├── Jobs
│   └── ImportCsvJob.php
│
├── Services
│   ├── UploadService.php
│   ├── CsvImportService.php
│   └── ShopifyService.php
│
├── Models
│   ├── Upload.php
│   ├── ProductImportStatus.php
│   └── ApplicationLog.php
│
└── database
    └── migrations
```

# 🔄 Import Workflow

```text
Upload CSV
      │
      ▼
Validate File
      │
      ▼
Store CSV
      │
      ▼
Create Upload Record
      │
      ▼
Dispatch Queue Job
      │
      ▼
Read CSV
      │
      ▼
Map Headers
      │
      ▼
Import Product
      │
      ▼
Shopify GraphQL
      │
      ▼
Save Import Status
      │
      ▼
Dashboard
```

---

# 🔌 Shopify Integration

The application communicates with Shopify using the GraphQL Admin API.

Current implementation supports:

* Product Creation
* Product Updates
* Product Collection Assignment
* Product Status Tracking
* GraphQL Error Handling

---

# 🛡 Validation

### Client Side

* CSV Extension
* Maximum File Size

### Server Side

* MIME Validation
* Empty File Validation
* Header Validation
* CSV Structure Validation

### Shopify Validation

* Duplicate Handle
* GraphQL Errors
* User Errors
* Invalid Product Data

---

# 📊 Queue Processing

Laravel Queue is used to process imports asynchronously.

### Benefits

* Faster User Response
* Large CSV Support
* Retry Failed Jobs
* Better Scalability
* Reduced Request Timeout

---

# 📈 Logging Strategy

The application logs every important event.

* Upload Started
* Upload Completed
* Queue Started
* CSV Parsing
* Product Import
* Shopify Request
* Shopify Response
* Exceptions
* Import Completed

---

# 🚀 Installation

```bash
git clone <repository-url>

cd project

composer install

cp .env.example .env

php artisan key:generate

php artisan migrate

php artisan storage:link

php artisan queue:work

php artisan serve
```

---

# 📌 Tech Stack

* Laravel 12
* PHP 8.3
* MySQL
* Bootstrap 5
* Shopify GraphQL Admin API
* Laravel Queues
* Eloquent ORM

---

# 🔮 Future Improvements

* Chunked CSV Processing
* Bulk GraphQL Operations
* Inventory Synchronization
* Retry Failed Imports
* Real-time Progress Bar
* WebSocket Notifications
* Docker Support
* Automated Testing
* Multi-store Shopify Support

---

# 👨‍💻 Author

**Debashish Mandol**

Laravel & PHP Developer

Built as part of a Shopify Product Import assignment demonstrating clean architecture, queue-based processing, and GraphQL integration.
