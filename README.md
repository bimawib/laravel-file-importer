# ⚙️ Laravel Product Importer 

##  **⏺️ Project Overview**

> **Laravel Product Importer** is a backend service built with Laravel 10 that lets users upload product CSV files,  
> validates them securely, and processes imports asynchronously using RabbitMQ.  
> It includes JWT authentication and RBAC authorization for fine-grained access control.

**Built With:**
- Laravel 10
- PostgreSQL
- RabbitMQ (as queue broker)
- JWT (Tymon/jwt-auth)
- Spatie/laravel-permission (RBAC)
- League/Csv (CSV parsing)
- RabbitMQ 4

## **⏺️ System Architecture**

This project follows an asynchronous, event-driven architecture that separates file upload, queue processing, and status tracking.  
It ensures scalability and fault tolerance even for large CSV imports.

### **⏩ Architecture Flow**

1. **User uploads CSV** → `/api/import/products`
2. **Laravel validates & stores file** → creates a new `import_jobs` record (status = `pending`)
3. **Background worker** (`ProcessProductImportJob`) reads the CSV and dispatches `SaveProductRowJob` for each row
4. **Each SaveProductRowJob** validates & inserts product data → logs validation failures to `import_errors`
5. **Scheduler** (`CheckImportCompletionJob`) runs every minute → checks if all rows are processed  
   → updates `import_jobs.status` to `completed` or `failed`

### **⏩ Simplified Data Flow**

**1. User uploads a CSV file**
- Endpoint: `/api/import/products`
- File stored in: `storage/app/imports/products/`
- New record added to `import_jobs` with status `pending`
**2. Queue processing begins**
- Laravel dispatches `ProcessProductImportJob` to RabbitMQ
- Each row in the CSV is sent as a separate `SaveProductRowJob`
**3. Data validation & persistence**
- ✅ Valid rows → inserted into `products` table  
- ❌ Invalid rows → logged into `import_errors` table (with details)
 **4. Periodic status check**
- `CheckImportCompletionJob` runs every 1 minute:
  - If `success + failed == total`, mark job as:
    - ✅ `completed` (if any succeeded)
    - ❌ `failed` (if all rows failed)


## **⏺️ Setup & Installation**

Follow these steps to set up and run the Laravel File Importer project locally.

---

### **Prerequisites**

Make sure the following are installed on your system:

- **PHP ≥ 8.1** (with extensions below enabled)
- **Composer**
- **Docker** (for PostgreSQL and RabbitMQ)
- **Git**

#### 🧩 Recommended PHP Extensions
Enable these in your `php.ini`:
```
memory_limit = 512M
upload_max_filesize = 10M
post_max_size = 20M
max_execution_time = 300
file_uploads = On
display_errors = On

extension=pgsql
extension=pdo_pgsql
extension=openssl
extension=mbstring
extension=fileinfo
extension=gd
extension=curl
extension=sockets
```

### 🐳 **1️⃣ Run Docker Containers**

Run **PostgreSQL** and **RabbitMQ** containers using the following commands:

> **Note:** If you already have PostgreSQL installed locally, you can skip the PostgreSQL container and only run the RabbitMQ container.
```bash
# PostgreSQL (Skip this if you have PostgreSQL installed locally)
docker run -d --name postgresql-local \
  -e POSTGRES_USER=admindetik \
  -e POSTGRES_PASSWORD=AdminDetik1! \
  -e POSTGRES_DB=laravel_file_importer \
  -p 5432:5432 \
  -v postgres_data:/var/lib/postgresql/data \
  postgres:15

# RabbitMQ (with Management UI)
docker run -d --name rabbitmq-local --hostname rabbitmq-local \
  -p 5672:5672 -p 15672:15672 \
  -e RABBITMQ_DEFAULT_USER=admin \
  -e RABBITMQ_DEFAULT_PASS=AdminDetik1! \
  rabbitmq:4-management
```


### 💾 **2️⃣ Clone and Install Dependencies**
```
# clone the repository to your local machine
git clone https://github.com/yourname/laravel-file-importer.git

# change directory to already cloned repo
cd laravel-file-importer

# install all dependencies required
composer install
```

### ⚙️ **3️⃣ Environment Setup**
```
#copy the .env.example to create your own .env file
cp .env.example .env

# generate application key
php artisan key:generate
```

Then update .env with your database and RabbitMQ credentials:
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel_file_importer
DB_USERNAME=admindetik
DB_PASSWORD=AdminDetik1!

QUEUE_CONNECTION=rabbitmq
RABBITMQ_HOST=127.0.0.1
RABBITMQ_PORT=5672
RABBITMQ_USER=admin
RABBITMQ_PASSWORD=AdminDetik1!
RABBITMQ_QUEUE=default
```

### 📁 **4️⃣ Run Migrations and Seed Data**
```
php artisan migrate --seed
```
This will create 2 initial user with their own permission to certain access:
| Role | Email | Password | Permissions |
|------|--------|-----------|-------------|
| Administrator | `admin@detik.com` | `Admin123!` | Full access (`users:manage`, `products:import`, `products:view`, `import_jobs:view`, `import_errors:view`) |
| Guest | `guest@detik.com` | `Guest123!` | Read-only access (`products:view`, `import_jobs:view`, `import_errors:view`) |

### 🏃 **5️⃣ Start the Application**
Run all of these 3 command in separate terminals:
```
# 1. Serve API
php artisan serve

# 2. Start RabbitMQ worker
php artisan queue:work rabbitmq --queue=default

# 3. Run scheduler to check import completion
php artisan schedule:work
```
### 🚀 **6️⃣ Verify Setup**
### 🌐 Check Application & Services

### Laravel Application
- **Visit:** `http://localhost:8000` in your web browser
- **Expected Result:** The main page of the Laravel application should be displayed

### RabbitMQ Management UI
- **Visit:** `http://localhost:15672` in your web browser
- **Expected Result:** The RabbitMQ Management Login page should be displayed (You may need to log in to see the dashboard, depending on your setup)

### 🔑 Test API Login

You can test the login functionality using the provided credentials via a curl command in your terminal.

### Credentials
- **Email:** `admin@detik.com`
- **Password:** `Admin123!`

### Command
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@detik.com",
    "password": "Admin123!"
  }'
```

### Expected Result (Success)
You should receive a JSON response, typically containing an authentication token (like a Bearer Token) or a success message, which indicates the API is operational and correctly processing login requests.

## **⏺️ API Endpoints**


| Endpoint | Method | Description | Permission | Auth |
|----------|--------|-------------|------------|------|
| `/api/login` | POST | Login & get JWT | - | ❌ |
| `/api/import/products` | POST | Upload CSV | `products:import` | ✅ |
| `/api/import/products/template` | GET | Download import template | `products:view` | ✅ |
| `/api/import/status/{id}` | GET | Get import job status | `products:view` | ✅ |
| `/api/import/jobs` | GET | List all import jobs | `products:view` | ✅ |
| `/api/import/errors` | GET | List all import errors | `products:view` | ✅ |
| `/api/products` | GET | View product list | `products:view` | ✅ |

### 📚 Full API Documentation

For complete API documentation including request/response examples, error codes, and detailed usage instructions, please visit:

**[View Full API Documentation](https://documenter.getpostman.com/view/21243999/2sB3WsR146)**

## **⏺️ RBAC Roles**

Briefly show what roles exist.

### **Administrator**
- Manage users, roles, permissions
- Import & view all data

### **Guest**
- Read-only access to product & import data

---

## **⏺️ How the Queue Works**

Short explanation so interviewers see you get the async design.

The import process is fully asynchronous using **RabbitMQ**.

- Each CSV row is dispatched as a separate job to improve scalability
- Failures are logged per row, and a scheduled job updates the overall import status
- This design ensures non-blocking operations and better handling of large file imports

## **⏺️ Project Structure**
```
app/
 ├── Http/
 │   ├── Controllers/
 │   │   ├── ProductController.php
 │   │   └── AuthController.php
 │   ├── Middleware/
 │   └── Requests/
 ├── Exceptions/
 │   └── Handler.php
 ├── Helpers/
 │   └── ApiResponse.php
 ├── Jobs/
 │   ├── ProcessProductImportJob.php
 │   ├── SaveProductRowJob.php
 │   └── CheckImportCompletionJob.php
 ├── Models/
 ├── Services/
 └── Rules/
```