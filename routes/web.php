<?php

use App\Http\Controllers\EmailController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::POST('/parent/payme/response', 'Backend\Payment\PayMeController@processResponse')->name('parent.payme.response');

/*
|--------------------------------------------------------------------------
| Third Party Recharge Routes (Public - No Authentication Required)
|--------------------------------------------------------------------------
*/
Route::get('/third-party/recharge', 'Backend\Payment\ThirdPartyRechargeController@showSearchForm')->name('third-party.search');
Route::post('/third-party/search-student', 'Backend\Payment\ThirdPartyRechargeController@searchStudent')->name('third-party.search-student');
Route::get('/third-party/recharge-form', 'Backend\Payment\ThirdPartyRechargeController@showRechargeForm')->name('third-party.recharge');
Route::post('/third-party/payme/initialize', 'Backend\Payment\ThirdPartyRechargeController@initializePayment')->name('third-party.payme.initialize');
Route::post('/third-party/payme/response', 'Backend\Payment\ThirdPartyRechargeController@processResponse')->name('third-party.payme.response');

/*
|--------------------------------------------------------------------------
| administrator
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['role:administrator']], function () {
    Route::GET('/users', 'Backend\Users\UsersController@index')->name('users');
    Route::GET('/users/add', 'Backend\Users\UsersController@add')->name('users.add');
    Route::POST('/users/create', 'Backend\Users\UsersController@create')->name('users.create');
    Route::GET('/users/edit/{id}', 'Backend\Users\UsersController@edit')->name('users.edit');
    Route::POST('/users/update', 'Backend\Users\UsersController@update')->name('users.update');
    Route::GET('/users/delete/{id}', 'Backend\Users\UsersController@delete')->name('users.delete');

    Route::GET('/settings', 'Backend\Setting\SettingsController@index')->name('settings');
    Route::POST('/settings/update', 'Backend\Setting\SettingsController@update')->name('settings.update');
    Route::POST('/settings/store', 'SettingController@store')->name('settings.store');

    Route::GET('/analytic', 'Backend\Analytic\AnalyticController@index')->name('analytic');
});

/*
|--------------------------------------------------------------------------
| administrator|admin|editor|guest
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['role:administrator|admin|estudiante|guest']], function () {
    Route::GET('/checkProductVerify', 'MainController@checkProductVerify')->name('checkProductVerify');

    Route::GET('/profile/details', 'Backend\Profile\ProfileController@details')->name('profile.details');
    Route::POST('/profile/update', 'Backend\Profile\ProfileController@update')->name('profile.update');
});


/*
|--------------------------------------------------------------------------
| administrator|admin|staff
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['role:administrator|admin|estudiante|guest']], function () {
    Route::GET('/attendances', 'Backend\Attendance\AttendanceController@index')->name('attendances');

    // Credits routes
    //Route::GET('/credits/recharge', 'Backend\Credits\CreditsController@recharge')->name('credits.recharge');
    Route::POST('/credits/store', 'Backend\Credits\CreditsController@store')->name('credits.store');
});

/*
|--------------------------------------------------------------------------
| administrator|admin
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['role:administrator|admin']], function () {
    Route::GET('/histories', 'Backend\History\HistoryController@index')->name('histories');
    Route::GET('/histories/inactive', 'Backend\History\HistoryController@inactive')->name('histories.inactive');
    Route::GET('/histories/pending', 'Backend\History\HistoryController@pending')->name('histories.pending');
    Route::GET('/histories/add', 'Backend\History\HistoryController@add')->name('histories.add');
    Route::POST('/histories/create', 'Backend\History\HistoryController@create')->name('histories.create');
    Route::GET('/histories/show/{id}', 'Backend\History\HistoryController@show')->name('histories.show');
    Route::GET('/histories/edit/{id}', 'Backend\History\HistoryController@edit')->name('histories.edit');
    Route::POST('/histories/update', 'Backend\History\HistoryController@update')->name('histories.update');
    Route::GET('/histories/delete/{id}', 'Backend\History\HistoryController@delete')->name('histories.delete');
    Route::post('/histories/set-inactive', 'Backend\History\HistoryController@setInactive')->name('histories.setInactive');
    Route::post('/histories/set-active', 'Backend\History\HistoryController@setActive')->name('histories.setActive');
    Route::GET('/histories/download-contract/{id}', 'Backend\History\HistoryController@downloadContract')->name('histories.download-contract');

    Route::GET('/histories/import', 'Backend\History\HistoryController@import')->name('histories.import');
    Route::POST('/histories/importData', 'Backend\History\HistoryController@importData')->name('histories.importData');

    // AJAX Cascade: Zona -> Colegio -> Ruta -> Paradero
    Route::GET('/histories/zonas/{zonaId}/colegios', 'Backend\History\HistoryController@getColegiosByZona')->name('histories.colegiosByZona');
    Route::GET('/histories/colegios/{colegioId}/rutas', 'Backend\History\HistoryController@getRutasByColegio')->name('histories.rutasByColegio');
    Route::GET('/histories/rutas/{rutaId}/paraderos', 'Backend\History\HistoryController@getParaderosByRuta')->name('histories.paraderosByRuta');

    // Zonas Routes
    Route::GET('/zonas', 'Backend\Zona\ZonaController@index')->name('zonas.index');
    Route::GET('/zonas/add', 'Backend\Zona\ZonaController@add')->name('zonas.add');
    Route::POST('/zonas/create', 'Backend\Zona\ZonaController@create')->name('zonas.create');
    Route::GET('/zonas/edit/{id}', 'Backend\Zona\ZonaController@edit')->name('zonas.edit');
    Route::POST('/zonas/update', 'Backend\Zona\ZonaController@update')->name('zonas.update');
    Route::GET('/zonas/delete/{id}', 'Backend\Zona\ZonaController@delete')->name('zonas.delete');

    // Colegios Routes
    Route::GET('/colegios', 'Backend\Colegio\ColegioController@index')->name('colegios.index');
    Route::GET('/colegios/add', 'Backend\Colegio\ColegioController@add')->name('colegios.add');
    Route::POST('/colegios/create', 'Backend\Colegio\ColegioController@create')->name('colegios.create');
    Route::GET('/colegios/edit/{id}', 'Backend\Colegio\ColegioController@edit')->name('colegios.edit');
    Route::POST('/colegios/update', 'Backend\Colegio\ColegioController@update')->name('colegios.update');
    Route::GET('/colegios/{id}/students', 'Backend\Colegio\ColegioController@viewStudents')->name('colegios.students');
    Route::GET('/colegios/delete/{id}', 'Backend\Colegio\ColegioController@delete')->name('colegios.delete');

    // Becas Routes
    Route::GET('/becas', 'Backend\Beca\BecaController@index')->name('becas.index');
    Route::GET('/becas/add', 'Backend\Beca\BecaController@add')->name('becas.add');
    Route::POST('/becas/create', 'Backend\Beca\BecaController@create')->name('becas.create');
    Route::GET('/becas/edit/{id}', 'Backend\Beca\BecaController@edit')->name('becas.edit');
    Route::POST('/becas/update', 'Backend\Beca\BecaController@update')->name('becas.update');
    Route::GET('/becas/delete/{id}', 'Backend\Beca\BecaController@delete')->name('becas.delete');

    // Tarifas Routes
    Route::GET('/tarifas', 'Backend\Tarifa\TarifaController@index')->name('tarifas.index');
    Route::GET('/tarifas/add', 'Backend\Tarifa\TarifaController@add')->name('tarifas.add');
    Route::POST('/tarifas/create', 'Backend\Tarifa\TarifaController@create')->name('tarifas.create');
    Route::GET('/tarifas/edit/{id}', 'Backend\Tarifa\TarifaController@edit')->name('tarifas.edit');
    Route::POST('/tarifas/update', 'Backend\Tarifa\TarifaController@update')->name('tarifas.update');
    Route::GET('/tarifas/{id}/students', 'Backend\Tarifa\TarifaController@viewStudents')->name('tarifas.students');
    Route::GET('/tarifas/delete/{id}', 'Backend\Tarifa\TarifaController@delete')->name('tarifas.delete');

    // Paraderos Routes
    Route::GET('/paraderos', 'Backend\Paradero\ParaderoController@index')->name('paraderos.index');
    Route::GET('/paraderos/add', 'Backend\Paradero\ParaderoController@add')->name('paraderos.add');
    Route::POST('/paraderos/create', 'Backend\Paradero\ParaderoController@create')->name('paraderos.create');
    Route::GET('/paraderos/edit/{id}', 'Backend\Paradero\ParaderoController@edit')->name('paraderos.edit');
    Route::POST('/paraderos/update', 'Backend\Paradero\ParaderoController@update')->name('paraderos.update');
    Route::GET('/paraderos/delete/{id}', 'Backend\Paradero\ParaderoController@delete')->name('paraderos.delete');
    Route::GET('/paraderos/ruta/{rutaId}', 'Backend\Paradero\ParaderoController@porRuta')->name('paraderos.por-ruta');
    Route::POST('/paraderos/reordenar', 'Backend\Paradero\ParaderoController@reordenar')->name('paraderos.reordenar');
    Route::POST('/paraderos/duplicar', 'Backend\Paradero\ParaderoController@duplicar')->name('paraderos.duplicar');
});

Route::post('reinputkey/index/{code}', 'Utils\Activity\ReinputKeyController@index');

Route::get('/email', [EmailController::class, 'executeSendEmail']);

/*
|--------------------------------------------------------------------------
| Parent Dashboard Routes (for guest role)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['role:guest']], function () {
    // Parent Profile Management
    Route::GET('/parent/profile', 'Backend\Parent\ParentProfileController@index')->name('parent.profile');
    Route::POST('/parent/profile/update', 'Backend\Parent\ParentProfileController@update')->name('parent.profile.update');
    Route::GET('/parent/profile/reminder', 'Backend\Parent\ParentProfileController@showCompletionReminder')->name('parent.profile.reminder');
    Route::GET('/parent/profile/check', 'Backend\Parent\ParentProfileController@checkProfileComplete')->name('parent.profile.check');
    Route::GET('/parent/profile/view', 'Backend\Parent\ParentProfileController@viewOnly')->name('parent.profile.view');

    // Parent Dashboard
    Route::GET('/parent/dashboard', 'Backend\Parent\ParentDashboardController@index')->name('parent.dashboard');

    // My Children - Profile, QR, Contracts
    Route::GET('/parent/my-children', 'Backend\Parent\ParentDashboardController@myChildren')->name('parent.my-children');

    // Assignment System
    Route::GET('/parent/assign-children', 'Backend\Parent\ParentDashboardController@assignChildren')->name('parent.assign-children');
    Route::GET('/parent/search-students', 'Backend\Parent\ParentDashboardController@searchStudents')->name('parent.search-students');
    Route::POST('/parent/request-relationship', 'Backend\Parent\ParentDashboardController@requestRelationship')->name('parent.request-relationship');

    // Student Transactions
    Route::GET('/parent/student/{studentId}/transactions', 'Backend\Parent\ParentDashboardController@studentTransactions')->name('parent.student-transactions');

    // Student Profile & QR Management
    Route::GET('/parent/student/{studentId}/profile', 'Backend\Parent\ParentDashboardController@studentProfile')->name('parent.student-profile');
    Route::GET('/parent/download-contract-template', 'Backend\Parent\ParentDashboardController@downloadContractTemplate')->name('parent.download-contract-template');
    Route::POST('/parent/student/{studentId}/upload-contract', 'Backend\Parent\ParentDashboardController@uploadStudentContract')->name('parent.student.upload-contract');
    Route::GET('/parent/student/{studentId}/download-contract', 'Backend\Parent\ParentDashboardController@downloadStudentContract')->name('parent.student.download-contract');
    Route::GET('/parent/student/{studentId}/qr', 'Backend\Parent\ParentDashboardController@getStudentQR')->name('parent.student.qr');

    // PayMe Payment Routes for Guest Role
    Route::GET('/parent/recharge-credits', 'Backend\Payment\PayMeController@showRechargeForm')->name('parent.recharge-credits');
    Route::POST('/parent/payme/initialize', 'Backend\Payment\PayMeController@initializePayment')->name('parent.payme.initialize');
    Route::GET('/parent/payment-history', 'Backend\Payment\PayMeController@getPaymentHistory')->name('parent.payment-history');
});

/*
|--------------------------------------------------------------------------
| estudiante
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['role:estudiante']], function () {
    // Student Dashboard
    Route::GET('/student/dashboard', 'Backend\Student\StudentDashboardController@index')->name('student.dashboard');
    Route::GET('/student/download-contract-template', 'Backend\Student\StudentDashboardController@downloadContractTemplate')->name('student.download-contract-template');
    Route::POST('/student/upload-contract', 'Backend\Student\StudentDashboardController@uploadContract')->name('student.upload-contract');
    Route::GET('/student/download-contract', 'Backend\Student\StudentDashboardController@downloadContract')->name('student.download-contract');
    Route::GET('/student/download-qr', 'Backend\Student\StudentDashboardController@downloadQR')->name('student.download-qr');
    // Firma digital
    Route::GET('/student/sign-contract', 'Backend\Student\StudentDashboardController@showSignContract')->name('student.sign-contract');
    Route::POST('/student/save-signature', 'Backend\Student\StudentDashboardController@saveSignature')->name('student.save-signature');
});

/*
|--------------------------------------------------------------------------
| Admin Routes for Parent-Child Relationship Management
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['role:administrator|admin']], function () {
    // Parent-Child Relationship Management
    Route::GET('/admin/parent-requests', 'Backend\Admin\ParentRequestController@index')->name('admin.parent-requests');
    Route::POST('/admin/parent-requests/{id}/approve', 'Backend\Admin\ParentRequestController@approve')->name('admin.parent-requests.approve');
    Route::POST('/admin/parent-requests/{id}/reject', 'Backend\Admin\ParentRequestController@reject')->name('admin.parent-requests.reject');
    Route::GET('/admin/parent-requests/{id}', 'Backend\Admin\ParentRequestController@show')->name('admin.parent-requests.show');

    // Parent Management
    Route::GET('/admin/parents', 'Backend\Admin\ParentManagementController@index')->name('admin.parents.index');
    Route::GET('/admin/parents/create', 'Backend\Admin\ParentManagementController@create')->name('admin.parents.create');
    Route::POST('/admin/parents/store', 'Backend\Admin\ParentManagementController@store')->name('admin.parents.store');

    // Static routes must come before dynamic routes
    Route::POST('/admin/parents/assign-children', 'Backend\Admin\ParentManagementController@assignChildren')->name('admin.parents.assign-children');
    Route::DELETE('/admin/parents/remove-relationship', 'Backend\Admin\ParentManagementController@removeRelationship')->name('admin.parents.remove-relationship');
    Route::GET('/admin/parents-search-students', 'Backend\Admin\ParentManagementController@searchStudents')->name('admin.parents.search-students');

    // Dynamic routes come after static ones to prevent conflicts
    Route::GET('/admin/parents/{id}', 'Backend\Admin\ParentManagementController@show')->name('admin.parents.show');
    Route::GET('/admin/parents/{id}/edit', 'Backend\Admin\ParentManagementController@edit')->name('admin.parents.edit');
    Route::POST('/admin/parents/{id}/update', 'Backend\Admin\ParentManagementController@update')->name('admin.parents.update');
    Route::DELETE('/admin/parents/{id}', 'Backend\Admin\ParentManagementController@destroy')->name('admin.parents.destroy');

    // Credit Transactions Management
    Route::GET('/admin/transactions', 'Backend\Transaction\TransactionController@index')->name('transactions.index');
    Route::GET('/admin/transactions/export', 'Backend\Transaction\TransactionController@export')->name('transactions.export');
    Route::GET('/admin/transactions/{id}', 'Backend\Transaction\TransactionController@show')->name('transactions.show');
    Route::PATCH('/admin/transactions/{id}/verify', 'Backend\Transaction\TransactionController@verify')->name('transactions.verify');
    Route::PATCH('/admin/transactions/{id}/reject', 'Backend\Transaction\TransactionController@reject')->name('transactions.reject');

    // Student-User Link Tool (for linking histories with users)
    Route::GET('/admin/student-user-link', 'Backend\Admin\StudentUserLinkController@index')->name('admin.student-user-link');
    Route::GET('/admin/student-user-link/search', 'Backend\Admin\StudentUserLinkController@searchUsers')->name('admin.student-user-link.search');
    Route::POST('/admin/student-user-link/link', 'Backend\Admin\StudentUserLinkController@linkUser')->name('admin.student-user-link.link');
    Route::POST('/admin/student-user-link/create', 'Backend\Admin\StudentUserLinkController@createUserForStudent')->name('admin.student-user-link.create');
    Route::POST('/admin/student-user-link/auto', 'Backend\Admin\StudentUserLinkController@autoLinkAll')->name('admin.student-user-link.auto');
});
