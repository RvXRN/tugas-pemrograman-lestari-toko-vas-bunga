<?php

use Laravel\Octane\Contracts\OperationTerminated;
use Laravel\Octane\Events\RequestHandled;
use Laravel\Octane\Events\RequestReceived;
use Laravel\Octane\Events\RequestTerminated;
use Laravel\Octane\Events\TaskReceived;
use Laravel\Octane\Events\TaskTerminated;
use Laravel\Octane\Events\TickReceived;
use Laravel\Octane\Events\TickTerminated;
use Laravel\Octane\Events\WorkerErrorOccurred;
use Laravel\Octane\Events\WorkerStarting;
use Laravel\Octane\Events\WorkerStopping;
use Laravel\Octane\Listeners\CollectGarbage;
use Laravel\Octane\Listeners\DisconnectFromDatabases;
use Laravel\Octane\Listeners\EnsureUploadedFilesAreValid;
use Laravel\Octane\Listeners\EnsureUploadedFilesCanBeMoved;
use Laravel\Octane\Listeners\FlushAuthenticationState;
use Laravel\Octane\Listeners\FlushContextState;
use Laravel\Octane\Listeners\FlushDatabaseRecordModificationState;
use Laravel\Octane\Listeners\FlushDatabaseSessionHandler;
use Laravel\Octane\Listeners\FlushLogContext;
use Laravel\Octane\Listeners\FlushMonologState;
use Laravel\Octane\Listeners\FlushQueuedCookies;
use Laravel\Octane\Listeners\FlushSessionState;
use Laravel\Octane\Listeners\FlushTemporaryContainerInstances;
use Laravel\Octane\Listeners\FlushTranslationCache;
use Laravel\Octane\Listeners\GiveNewApplicationInstanceToAuthorizationGate;
use Laravel\Octane\Listeners\GiveNewApplicationInstanceToBroadcastManager;
use Laravel\Octane\Listeners\GiveNewApplicationInstanceToCacheManager;
use Laravel\Octane\Listeners\GiveNewApplicationInstanceToConsoleKernel;
use Laravel\Octane\Listeners\GiveNewApplicationInstanceToDatabaseManager;
use Laravel\Octane\Listeners\GiveNewApplicationInstanceToFilesystemManager;
use Laravel\Octane\Listeners\GiveNewApplicationInstanceToHttpClient;
use Laravel\Octane\Listeners\GiveNewApplicationInstanceToMailManager;
use Laravel\Octane\Listeners\GiveNewApplicationInstanceToNotificationChannelManager;
use Laravel\Octane\Listeners\GiveNewApplicationInstanceToQueueManager;
use Laravel\Octane\Listeners\GiveNewApplicationInstanceToRouter;
use Laravel\Octane\Listeners\GiveNewApplicationInstanceToValidationFactory;
use Laravel\Octane\Listeners\GiveNewApplicationInstanceToViewFactory;
use Laravel\Octane\Listeners\ReportException;
use Laravel\Octane\Listeners\StopWorkerIfNecessary;
use Laravel\Octane\Octane;

return [

    /*
    |--------------------------------------------------------------------------
    | Octane Server
    |--------------------------------------------------------------------------
    | Menggunakan FrankenPHP sebagai HTTP server berbasis Go + Caddy.
    | FrankenPHP berjalan sebagai single binary, tidak perlu Nginx/Apache.
    | Mode: frankenphp | swoole | roadrunner
    */
    'server' => env('OCTANE_SERVER', 'frankenphp'),

    /*
    |--------------------------------------------------------------------------
    | Force HTTPS
    |--------------------------------------------------------------------------
    | Jika true, FrankenPHP akan otomatis provision TLS via Let's Encrypt.
    | Set ke false jika TLS di-terminate oleh reverse proxy (Nginx/Cloudflare).
    */
    'https' => env('OCTANE_HTTPS', false),

    /*
    |--------------------------------------------------------------------------
    | Octane Workers
    |--------------------------------------------------------------------------
    | Jumlah worker process FrankenPHP. 'auto' = jumlah CPU core.
    | Rekomendasi server 4 CPU (bersama frontend): set ke 3.
    */
    'workers' => env('OCTANE_WORKERS', 'auto'),

    /*
    |--------------------------------------------------------------------------
    | Task Workers
    |--------------------------------------------------------------------------
    | Worker tambahan untuk concurrentTask() dan tick. 'auto' = sama dengan workers.
    */
    'task_workers' => env('OCTANE_TASK_WORKERS', 'auto'),

    /*
    |--------------------------------------------------------------------------
    | Max Requests Per Worker
    |--------------------------------------------------------------------------
    | Worker akan di-restart setelah menangani sejumlah request ini.
    | Mencegah memory leak jangka panjang pada long-running PHP process.
    | Default: 500 request per worker lifecycle.
    */
    'max_requests' => env('OCTANE_MAX_REQUESTS', 500),

    /*
    |--------------------------------------------------------------------------
    | Request Warming
    |--------------------------------------------------------------------------
    | Service yang di-boot saat worker start, bukan saat request datang.
    | Mengurangi latency request pertama di setiap worker.
    */
    'warm' => class_exists(Octane::class) ? [
        ...Octane::defaultServicesToWarm(),
    ] : [],

    /*
    |--------------------------------------------------------------------------
    | Garbage Collection Threshold
    |--------------------------------------------------------------------------
    | Jalankan gc_collect_cycles() setiap N request.
    */
    'garbage' => 50,

    /*
    |--------------------------------------------------------------------------
    | Event Listeners (Stateless Guard)
    |--------------------------------------------------------------------------
    | Listener ini memastikan state di-flush SETIAP request selesai,
    | mencegah kebocoran data antar user di long-running worker.
    | KRITIS untuk keamanan Octane production.
    */
    'listeners' => class_exists(Octane::class) ? [

        WorkerStarting::class => [
            EnsureUploadedFilesAreValid::class,
            EnsureUploadedFilesCanBeMoved::class,
        ],

        RequestReceived::class => [
            ...Octane::prepareApplicationForNextRequest(),
        ],

        RequestHandled::class => [
            FlushTemporaryContainerInstances::class,
            CollectGarbage::class,
        ],

        RequestTerminated::class => [
            FlushQueuedCookies::class,
            FlushSessionState::class,
            FlushAuthenticationState::class,
            FlushTranslationCache::class,
            FlushContextState::class,
        ],

        TaskReceived::class => [
            ...Octane::prepareApplicationForNextTask(),
        ],

        TaskTerminated::class => [
            FlushTemporaryContainerInstances::class,
            CollectGarbage::class,
        ],

        TickReceived::class => [
            ...Octane::prepareApplicationForNextTask(),
        ],

        TickTerminated::class => [
            FlushTemporaryContainerInstances::class,
            CollectGarbage::class,
        ],

        OperationTerminated::class => [
            FlushDatabaseRecordModificationState::class,
            FlushDatabaseSessionHandler::class,
            DisconnectFromDatabases::class,
            FlushLogContext::class,
            FlushMonologState::class,
            StopWorkerIfNecessary::class,
        ],

        WorkerErrorOccurred::class => [
            ReportException::class,
            StopWorkerIfNecessary::class,
        ],

        WorkerStopping::class => [
            //
        ],
    ] : [],

    /*
    |--------------------------------------------------------------------------
    | FrankenPHP Options
    |--------------------------------------------------------------------------
    | Konfigurasi spesifik FrankenPHP.
    | num_threads: null = ikuti OCTANE_WORKERS.
    | worker_count: alias dari num_threads untuk FrankenPHP v2.
    */
    'frankenphp' => [
        'https'         => env('OCTANE_HTTPS', false),
        'http_port'     => env('OCTANE_PORT', 8000),
        'https_port'    => env('OCTANE_HTTPS_PORT', 443),
        'admin'         => env('OCTANE_FRANKEN_ADMIN', false),
        'num_threads'   => env('OCTANE_WORKERS', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Octane Cache (Shared Memory Table)
    |--------------------------------------------------------------------------
    | Digunakan oleh Cache::store('octane'). Tersimpan di shared memory
    | antar worker, ultra-fast, tapi hilang saat server restart.
    | Cocok untuk data yang bisa di-regenerate (bukan session user).
    */
    'cache' => [
        'rows'  => 1000,
        'bytes' => 10000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Octane Tables (Shared Memory)
    |--------------------------------------------------------------------------
    | Format: 'nama_tabel:kapasitas_rows'
    */
    'tables' => [
        'example:1000',
    ],

];
