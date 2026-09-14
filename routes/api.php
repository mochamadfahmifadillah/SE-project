<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompareController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\SavedSoftwareController;
use App\Http\Controllers\Api\SoftwareController;
use App\Http\Controllers\Api\ImplementationRequestController;
use App\Http\Controllers\Api\ReviewController;

/*
|--------------------------------------------------------------------------
| Admin Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminLeadsController;
use App\Http\Controllers\Api\AdminSoftwareController;
use App\Http\Controllers\Api\AdminVendorController;
use App\Http\Controllers\Api\AdminCategoryController;
use App\Http\Controllers\Api\AdminFeatureController;
use App\Http\Controllers\Api\AdminIndustryController;
use App\Http\Controllers\Api\AdminBusinessSizeController;
use App\Http\Controllers\Api\AdminIntegrationController;
use App\Http\Controllers\Api\AdminPartnerController;
use App\Http\Controllers\Api\AdminArticleController;
use App\Http\Controllers\Api\AdminReviewController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\SettingsController;

/*
|--------------------------------------------------------------------------
| Audit Log Controller
|--------------------------------------------------------------------------
|
| AuditLogController saat ini berada di:
|
| App\Http\Controllers\Api\V1\Admin
|
*/

use App\Http\Controllers\Api\V1\Admin\AuditLogController;


/*
|--------------------------------------------------------------------------
| API V1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | PUBLIC API
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | Software
    |--------------------------------------------------------------------------
    */

    Route::get('/software', [
        SoftwareController::class,
        'index',
    ])->name('software.index');

    Route::get('/software/{slug}', [
        SoftwareController::class,
        'show',
    ])->name('software.show');


    /*
    |--------------------------------------------------------------------------
    | Software Reviews - Public
    |--------------------------------------------------------------------------
    */

    Route::get('/software/{software}/reviews', [
        ReviewController::class,
        'index',
    ])->name('reviews.index');


    /*
    |--------------------------------------------------------------------------
    | Recommendations
    |--------------------------------------------------------------------------
    */

    Route::post('/recommendations', [
        RecommendationController::class,
        'store',
    ])->name('recommendations.store');


    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    Route::post('/register', [
        AuthController::class,
        'register',
    ])->name('auth.register');

    Route::post('/login', [
        AuthController::class,
        'login',
    ])->name('auth.login');


    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATED API
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Current User
        |--------------------------------------------------------------------------
        */

        Route::get('/me', [
            AuthController::class,
            'me',
        ])->name('auth.me');

        Route::post('/logout', [
            AuthController::class,
            'logout',
        ])->name('auth.logout');


        /*
        |--------------------------------------------------------------------------
        | Comparisons
        |--------------------------------------------------------------------------
        |
        | GET  /api/v1/me/comparisons
        | POST /api/v1/compare
        |
        */

        Route::get('/me/comparisons', [
            CompareController::class,
            'index',
        ])->name('comparisons.index');

        Route::post('/compare', [
            CompareController::class,
            'store',
        ])->name('comparisons.store');


        /*
        |--------------------------------------------------------------------------
        | Saved Software
        |--------------------------------------------------------------------------
        |
        | GET    /api/v1/me/saved-software
        | POST   /api/v1/me/saved-software
        | DELETE /api/v1/me/saved-software/{software}
        |
        */

        Route::get('/me/saved-software', [
            SavedSoftwareController::class,
            'index',
        ])->name('saved-software.index');

        Route::post('/me/saved-software', [
            SavedSoftwareController::class,
            'store',
        ])->name('saved-software.store');

        Route::delete('/me/saved-software/{software}', [
            SavedSoftwareController::class,
            'destroy',
        ])->name('saved-software.destroy');


        /*
        |--------------------------------------------------------------------------
        | Implementation Requests
        |--------------------------------------------------------------------------
        |
        | GET  /api/v1/me/implementation-requests
        | POST /api/v1/implementation-requests
        |
        */

        Route::get('/me/implementation-requests', [
            ImplementationRequestController::class,
            'index',
        ])->name('implementation-requests.index');

        Route::post('/implementation-requests', [
            ImplementationRequestController::class,
            'store',
        ])->name('implementation-requests.store');


        /*
        |--------------------------------------------------------------------------
        | Reviews - Authenticated User
        |--------------------------------------------------------------------------
        */

        Route::post('/software/{software}/reviews', [
            ReviewController::class,
            'store',
        ])->name('reviews.store');

        Route::delete('/reviews/{review}', [
            ReviewController::class,
            'destroy',
        ])->name('reviews.destroy');


        /*
        |--------------------------------------------------------------------------
        | ADMIN API
        |--------------------------------------------------------------------------
        |
        | Semua endpoint admin:
        |
        | 1. User harus authenticated
        | 2. User harus mempunyai role admin
        | 3. User harus mempunyai permission sesuai endpoint
        |
        */

        Route::prefix('admin')
            ->name('admin.')
            ->middleware('role:admin')
            ->group(function () {

                /*
                |--------------------------------------------------------------------------
                | Dashboard
                |--------------------------------------------------------------------------
                */

                Route::get('/dashboard', [
                    AdminDashboardController::class,
                    'index',
                ])
                    ->middleware('permission:dashboard.view')
                    ->name('dashboard');


                /*
                |--------------------------------------------------------------------------
                | Analytics
                |--------------------------------------------------------------------------
                |
                | GET /api/v1/admin/analytics
                |
                | Digunakan oleh Admin Portal untuk:
                |
                | - Total software
                | - Total users
                | - Total reviews
                | - Total implementation requests
                | - Total vendors
                | - Total partners
                | - Total articles
                | - Total comparisons
                |
                */

                Route::get('/analytics', [
                    AnalyticsController::class,
                    'index',
                ])
                    ->middleware('permission:analytics.view')
                    ->name('analytics');


                /*
                |--------------------------------------------------------------------------
                | Settings
                |--------------------------------------------------------------------------
                |
                | GET /api/v1/admin/settings
                | PUT /api/v1/admin/settings
                |
                | Digunakan oleh Admin Portal untuk:
                |
                | - Platform name
                | - Platform URL
                | - Platform email
                | - Timezone
                | - Maintenance mode
                | - Email notifications
                | - Review notifications
                | - Lead notifications
                |
                */

                Route::get('/settings', [
                    SettingsController::class,
                    'index',
                ])
                    ->middleware('permission:settings.view')
                    ->name('settings.index');

                Route::put('/settings', [
                    SettingsController::class,
                    'update',
                ])
                    ->middleware('permission:settings.update')
                    ->name('settings.update');


                /*
                |--------------------------------------------------------------------------
                | User Management
                |--------------------------------------------------------------------------
                */

                Route::get('/users', [
                    AdminUserController::class,
                    'index',
                ])
                    ->middleware('permission:users.view')
                    ->name('users.index');

                Route::post('/users', [
                    AdminUserController::class,
                    'store',
                ])
                    ->middleware('permission:users.create')
                    ->name('users.store');

                Route::get('/users/{user}', [
                    AdminUserController::class,
                    'show',
                ])
                    ->middleware('permission:users.view')
                    ->name('users.show');

                Route::put('/users/{user}', [
                    AdminUserController::class,
                    'update',
                ])
                    ->middleware('permission:users.update')
                    ->name('users.update');

                Route::delete('/users/{user}', [
                    AdminUserController::class,
                    'destroy',
                ])
                    ->middleware('permission:users.delete')
                    ->name('users.destroy');


                /*
                |--------------------------------------------------------------------------
                | Role Options
                |--------------------------------------------------------------------------
                */

                Route::get('/roles', [
                    AdminUserController::class,
                    'roles',
                ])
                    ->middleware('permission:users.view')
                    ->name('roles.index');


                /*
                |--------------------------------------------------------------------------
                | Audit Logs
                |--------------------------------------------------------------------------
                |
                | GET /api/v1/admin/audit-logs
                | GET /api/v1/admin/audit-logs/{id}
                |
                */

                Route::get('/audit-logs', [
                    AuditLogController::class,
                    'index',
                ])
                    ->middleware('permission:audit-logs.view')
                    ->name('audit-logs.index');

                Route::get('/audit-logs/{id}', [
                    AuditLogController::class,
                    'show',
                ])
                    ->middleware('permission:audit-logs.view')
                    ->name('audit-logs.show');


                /*
                |--------------------------------------------------------------------------
                | Implementation Leads
                |--------------------------------------------------------------------------
                */

                Route::get('/leads', [
                    AdminLeadsController::class,
                    'index',
                ])
                    ->middleware('permission:leads.view')
                    ->name('leads.index');

                Route::get('/leads/{lead}', [
                    AdminLeadsController::class,
                    'show',
                ])
                    ->middleware('permission:leads.view')
                    ->name('leads.show');

                Route::put('/leads/{lead}', [
                    AdminLeadsController::class,
                    'update',
                ])
                    ->middleware('permission:leads.update')
                    ->name('leads.update');

                Route::patch('/leads/{lead}/assign', [
                    AdminLeadsController::class,
                    'assign',
                ])
                    ->middleware('permission:leads.update')
                    ->name('leads.assign');

                Route::patch('/leads/{lead}/status', [
                    AdminLeadsController::class,
                    'updateStatus',
                ])
                    ->middleware('permission:leads.update')
                    ->name('leads.status');

                Route::delete('/leads/{lead}', [
                    AdminLeadsController::class,
                    'destroy',
                ])
                    ->middleware('permission:leads.delete')
                    ->name('leads.destroy');


                /*
                |--------------------------------------------------------------------------
                | Software Management
                |--------------------------------------------------------------------------
                */

                Route::get('/software', [
                    AdminSoftwareController::class,
                    'index',
                ])
                    ->middleware('permission:software.view')
                    ->name('software.index');

                Route::post('/software', [
                    AdminSoftwareController::class,
                    'store',
                ])
                    ->middleware('permission:software.create')
                    ->name('software.store');

                Route::get('/software/{software}', [
                    AdminSoftwareController::class,
                    'show',
                ])
                    ->middleware('permission:software.view')
                    ->name('software.show');

                Route::put('/software/{software}', [
                    AdminSoftwareController::class,
                    'update',
                ])
                    ->middleware('permission:software.update')
                    ->name('software.update');

                Route::delete('/software/{software}', [
                    AdminSoftwareController::class,
                    'destroy',
                ])
                    ->middleware('permission:software.delete')
                    ->name('software.destroy');


                /*
                |--------------------------------------------------------------------------
                | Vendor Management
                |--------------------------------------------------------------------------
                */

                Route::get('/vendors', [
                    AdminVendorController::class,
                    'index',
                ])
                    ->middleware('permission:vendors.view')
                    ->name('vendors.index');

                Route::post('/vendors', [
                    AdminVendorController::class,
                    'store',
                ])
                    ->middleware('permission:vendors.create')
                    ->name('vendors.store');

                Route::get('/vendors/{vendor}', [
                    AdminVendorController::class,
                    'show',
                ])
                    ->middleware('permission:vendors.view')
                    ->name('vendors.show');

                Route::put('/vendors/{vendor}', [
                    AdminVendorController::class,
                    'update',
                ])
                    ->middleware('permission:vendors.update')
                    ->name('vendors.update');

                Route::delete('/vendors/{vendor}', [
                    AdminVendorController::class,
                    'destroy',
                ])
                    ->middleware('permission:vendors.delete')
                    ->name('vendors.destroy');


                /*
                |--------------------------------------------------------------------------
                | Category Management
                |--------------------------------------------------------------------------
                */

                Route::get('/categories', [
                    AdminCategoryController::class,
                    'index',
                ])
                    ->middleware('permission:categories.view')
                    ->name('categories.index');

                Route::post('/categories', [
                    AdminCategoryController::class,
                    'store',
                ])
                    ->middleware('permission:categories.create')
                    ->name('categories.store');

                Route::get('/categories/{category}', [
                    AdminCategoryController::class,
                    'show',
                ])
                    ->middleware('permission:categories.view')
                    ->name('categories.show');

                Route::put('/categories/{category}', [
                    AdminCategoryController::class,
                    'update',
                ])
                    ->middleware('permission:categories.update')
                    ->name('categories.update');

                Route::delete('/categories/{category}', [
                    AdminCategoryController::class,
                    'destroy',
                ])
                    ->middleware('permission:categories.delete')
                    ->name('categories.destroy');


                /*
                |--------------------------------------------------------------------------
                | Feature Management
                |--------------------------------------------------------------------------
                */

                Route::get('/features', [
                    AdminFeatureController::class,
                    'index',
                ])
                    ->middleware('permission:features.view')
                    ->name('features.index');

                Route::post('/features', [
                    AdminFeatureController::class,
                    'store',
                ])
                    ->middleware('permission:features.create')
                    ->name('features.store');

                Route::get('/features/{feature}', [
                    AdminFeatureController::class,
                    'show',
                ])
                    ->middleware('permission:features.view')
                    ->name('features.show');

                Route::put('/features/{feature}', [
                    AdminFeatureController::class,
                    'update',
                ])
                    ->middleware('permission:features.update')
                    ->name('features.update');

                Route::delete('/features/{feature}', [
                    AdminFeatureController::class,
                    'destroy',
                ])
                    ->middleware('permission:features.delete')
                    ->name('features.destroy');


                /*
                |--------------------------------------------------------------------------
                | Industry Management
                |--------------------------------------------------------------------------
                */

                Route::get('/industries', [
                    AdminIndustryController::class,
                    'index',
                ])
                    ->middleware('permission:industries.view')
                    ->name('industries.index');

                Route::post('/industries', [
                    AdminIndustryController::class,
                    'store',
                ])
                    ->middleware('permission:industries.create')
                    ->name('industries.store');

                Route::get('/industries/{industry}', [
                    AdminIndustryController::class,
                    'show',
                ])
                    ->middleware('permission:industries.view')
                    ->name('industries.show');

                Route::put('/industries/{industry}', [
                    AdminIndustryController::class,
                    'update',
                ])
                    ->middleware('permission:industries.update')
                    ->name('industries.update');

                Route::delete('/industries/{industry}', [
                    AdminIndustryController::class,
                    'destroy',
                ])
                    ->middleware('permission:industries.delete')
                    ->name('industries.destroy');


                /*
                |--------------------------------------------------------------------------
                | Business Size Management
                |--------------------------------------------------------------------------
                */

                Route::get('/business-sizes', [
                    AdminBusinessSizeController::class,
                    'index',
                ])
                    ->middleware('permission:business-sizes.view')
                    ->name('business-sizes.index');

                Route::post('/business-sizes', [
                    AdminBusinessSizeController::class,
                    'store',
                ])
                    ->middleware('permission:business-sizes.create')
                    ->name('business-sizes.store');

                Route::get('/business-sizes/{businessSize}', [
                    AdminBusinessSizeController::class,
                    'show',
                ])
                    ->middleware('permission:business-sizes.view')
                    ->name('business-sizes.show');

                Route::put('/business-sizes/{businessSize}', [
                    AdminBusinessSizeController::class,
                    'update',
                ])
                    ->middleware('permission:business-sizes.update')
                    ->name('business-sizes.update');

                Route::delete('/business-sizes/{businessSize}', [
                    AdminBusinessSizeController::class,
                    'destroy',
                ])
                    ->middleware('permission:business-sizes.delete')
                    ->name('business-sizes.destroy');


                /*
                |--------------------------------------------------------------------------
                | Integration Management
                |--------------------------------------------------------------------------
                */

                Route::get('/integrations', [
                    AdminIntegrationController::class,
                    'index',
                ])
                    ->middleware('permission:integrations.view')
                    ->name('integrations.index');

                Route::post('/integrations', [
                    AdminIntegrationController::class,
                    'store',
                ])
                    ->middleware('permission:integrations.create')
                    ->name('integrations.store');

                Route::get('/integrations/{integration}', [
                    AdminIntegrationController::class,
                    'show',
                ])
                    ->middleware('permission:integrations.view')
                    ->name('integrations.show');

                Route::put('/integrations/{integration}', [
                    AdminIntegrationController::class,
                    'update',
                ])
                    ->middleware('permission:integrations.update')
                    ->name('integrations.update');

                Route::delete('/integrations/{integration}', [
                    AdminIntegrationController::class,
                    'destroy',
                ])
                    ->middleware('permission:integrations.delete')
                    ->name('integrations.destroy');


                /*
                |--------------------------------------------------------------------------
                | Partner Management
                |--------------------------------------------------------------------------
                */

                Route::get('/partners', [
                    AdminPartnerController::class,
                    'index',
                ])
                    ->middleware('permission:partners.view')
                    ->name('partners.index');

                Route::post('/partners', [
                    AdminPartnerController::class,
                    'store',
                ])
                    ->middleware('permission:partners.create')
                    ->name('partners.store');

                Route::get('/partners/{partner}', [
                    AdminPartnerController::class,
                    'show',
                ])
                    ->middleware('permission:partners.view')
                    ->name('partners.show');

                Route::put('/partners/{partner}', [
                    AdminPartnerController::class,
                    'update',
                ])
                    ->middleware('permission:partners.update')
                    ->name('partners.update');

                Route::delete('/partners/{partner}', [
                    AdminPartnerController::class,
                    'destroy',
                ])
                    ->middleware('permission:partners.delete')
                    ->name('partners.destroy');


                /*
                |--------------------------------------------------------------------------
                | Article Management
                |--------------------------------------------------------------------------
                */

                Route::get('/articles', [
                    AdminArticleController::class,
                    'index',
                ])
                    ->middleware('permission:articles.view')
                    ->name('articles.index');

                Route::post('/articles', [
                    AdminArticleController::class,
                    'store',
                ])
                    ->middleware('permission:articles.create')
                    ->name('articles.store');

                Route::get('/articles/{article}', [
                    AdminArticleController::class,
                    'show',
                ])
                    ->middleware('permission:articles.view')
                    ->name('articles.show');

                Route::put('/articles/{article}', [
                    AdminArticleController::class,
                    'update',
                ])
                    ->middleware('permission:articles.update')
                    ->name('articles.update');

                Route::delete('/articles/{article}', [
                    AdminArticleController::class,
                    'destroy',
                ])
                    ->middleware('permission:articles.delete')
                    ->name('articles.destroy');


                /*
                |--------------------------------------------------------------------------
                | Review Management
                |--------------------------------------------------------------------------
                */

                Route::get('/reviews', [
                    AdminReviewController::class,
                    'index',
                ])
                    ->middleware('permission:reviews.view')
                    ->name('reviews.index');

                Route::get('/reviews/{review}', [
                    AdminReviewController::class,
                    'show',
                ])
                    ->middleware('permission:reviews.view')
                    ->name('reviews.show');

                Route::put('/reviews/{review}', [
                    AdminReviewController::class,
                    'update',
                ])
                    ->middleware('permission:reviews.update')
                    ->name('reviews.update');

                Route::delete('/reviews/{review}', [
                    AdminReviewController::class,
                    'destroy',
                ])
                    ->middleware('permission:reviews.delete')
                    ->name('reviews.destroy');
            });
    });
});