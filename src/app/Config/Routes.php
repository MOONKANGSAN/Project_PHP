<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('/users', 'Users::list');

// 서비스 페이지
$routes->get('/restaurants',         'Service::restaurants');
$routes->get('/restaurants/(:num)',  'Service::restaurantView/$1');
$routes->get('/restaurants/suggest', 'Service::suggest');
$routes->get('/spots',               'Service::spots');
$routes->get('/spots/(:num)',        'Service::spotView/$1');
$routes->get('/spots/suggest',       'Service::spotsSuggest');
$routes->get('/festivals',           'Service::festivals');
$routes->get('/festivals/(:num)',    'Service::festivalView/$1');
$routes->get('/festivals/suggest',   'Service::festivalsSuggest');

// 지역별 탐색 공개 API (메인 페이지용, 로그인 불필요)
$routes->get('/api/region-explore',                'BackofficeRegionExplore::apiRegions');
$routes->get('/api/region-explore/(:num)/top5',    'BackofficeRegionExplore::apiTop5/$1');

// 지역별 핫플레이스
$routes->get('/hotplace',          'Service::hotplace');
$routes->get('/hotplace/(:num)',   'Service::hotplace/$1');

// 여행코스
$routes->get('/travel-courses',        'Service::travelCourses');
$routes->get('/travel-courses/(:num)', 'Service::travelCourseView/$1');

// 고객센터
$routes->get('/customer',                      'Customer::index');
$routes->get('/customer/ajax/notice',          'Customer::ajaxNotice');
$routes->post('/customer/notice/(:num)/view',  'Customer::noticeView/$1');
$routes->get('/customer/ajax/faq',             'Customer::ajaxFaq');
$routes->get('/customer/ajax/inquiry',         'Customer::ajaxInquiry');
$routes->post('/customer/inquiry/store',       'Customer::inquiryStore');

// 메인 인증
$routes->post('/auth/register', 'Auth::register');
$routes->post('/auth/login',    'Auth::login');
$routes->get('/auth/logout',    'Auth::logout');

// 백오피스 — 인증 불필요
$routes->get('backoffice',            'Backoffice::index');
$routes->get('backoffice/login',      'Backoffice::login');
$routes->post('backoffice/login',     'Backoffice::doLogin');
$routes->get('backoffice/logout',     'Backoffice::logout');
$routes->get('backoffice/add-admin',  'Backoffice::addAdmin');
$routes->post('backoffice/add-admin', 'Backoffice::doAddAdmin');

// 백오피스 — 로그인 필요 (BackofficeAuthFilter 적용)
$routes->group('backoffice', ['filter' => 'backofficeauth'], static function ($routes) {
    // 대시보드·기타
    $routes->get('dashboard',         'Backoffice::dashboard');

    // 회원 관리
    $routes->get('members',                              'BackofficeMember::list');
    $routes->post('members/(:num)/state',               'BackofficeMember::toggleState/$1');
    $routes->post('members/(:num)/login-as',            'BackofficeMember::loginAs/$1');
    $routes->post('members/(:num)/reset-password',      'BackofficeMember::resetPassword/$1');
    $routes->post('members/(:num)/withdraw',            'BackofficeMember::withdraw/$1');
    $routes->post('members/(:num)/restore',             'BackofficeMember::restore/$1');

    // 탈퇴회원 관리
    $routes->get('withdrawn-members', 'BackofficeMember::withdrawnList');
    // 고객문의 관리
    $routes->get('inquiries',                          'BackofficeInquiry::list');
    $routes->get('inquiries/(:num)',                   'BackofficeInquiry::view/$1');
    $routes->post('inquiries/(:num)/answer',           'BackofficeInquiry::saveAnswer/$1');
    $routes->post('inquiries/(:num)/answer/delete',    'BackofficeInquiry::deleteAnswer/$1');
    $routes->post('inquiries/(:num)/state',            'BackofficeInquiry::toggleState/$1');
    $routes->post('inquiries/(:num)/delete',           'BackofficeInquiry::delete/$1');

    // 공지사항 관리
    $routes->get('notices',                    'BackofficeNotice::list');
    $routes->get('notices/register',           'BackofficeNotice::register');
    $routes->post('notices/register',          'BackofficeNotice::store');
    $routes->get('notices/(:num)/edit',        'BackofficeNotice::edit/$1');
    $routes->post('notices/(:num)/edit',       'BackofficeNotice::update/$1');
    $routes->post('notices/(:num)/state',      'BackofficeNotice::toggleState/$1');
    $routes->post('notices/(:num)/pin',        'BackofficeNotice::togglePin/$1');
    $routes->post('notices/(:num)/delete',     'BackofficeNotice::delete/$1');

    // FAQ 관리
    $routes->get('faqs',                    'BackofficeFaq::list');
    $routes->get('faqs/register',           'BackofficeFaq::register');
    $routes->post('faqs/register',          'BackofficeFaq::store');
    $routes->get('faqs/(:num)/edit',        'BackofficeFaq::edit/$1');
    $routes->post('faqs/(:num)/edit',       'BackofficeFaq::update/$1');
    $routes->post('faqs/(:num)/state',      'BackofficeFaq::toggleState/$1');
    $routes->post('faqs/(:num)/delete',     'BackofficeFaq::delete/$1');

    // 휴지통
    $routes->get('trash',                              'BackofficeTrash::index');
    $routes->post('trash/inquiry/(:num)/restore',      'BackofficeTrash::restoreInquiry/$1');
    $routes->post('trash/faq/(:num)/restore',          'BackofficeTrash::restoreFaq/$1');

    // 에러 로그 관리
    $routes->get('error-logs',                         'BackofficeErrorLog::list');
    $routes->post('error-logs/(:num)/state',           'BackofficeErrorLog::toggleState/$1');
    $routes->post('error-logs/(:num)/feedback',        'BackofficeErrorLog::saveFeedback/$1');

    // 배너 관리
    $routes->get('banners',                 'BackofficeBanner::list');
    $routes->get('banners/register',        'BackofficeBanner::register');
    $routes->post('banners/register',       'BackofficeBanner::store');
    $routes->get('banners/(:num)/edit',     'BackofficeBanner::edit/$1');
    $routes->post('banners/(:num)/edit',    'BackofficeBanner::update/$1');
    $routes->post('banners/(:num)/state',   'BackofficeBanner::toggleState/$1');
    $routes->post('banners/(:num)/delete',  'BackofficeBanner::delete/$1');

    $routes->get('site-config',       'Backoffice::siteConfig');

    // 맛집 관리
    $routes->get('restaurants',                    'BackofficeRestaurant::list');
    $routes->get('restaurants/register',           'BackofficeRestaurant::register');
    $routes->post('restaurants/register',          'BackofficeRestaurant::store');
    $routes->get('restaurants/(:num)/edit',        'BackofficeRestaurant::edit/$1');
    $routes->post('restaurants/(:num)/edit',       'BackofficeRestaurant::update/$1');
    $routes->post('restaurants/(:num)/state',      'BackofficeRestaurant::toggleState/$1');

    // 관광지 관리
    $routes->get('spots',                    'BackofficePlace::list');
    $routes->get('spots/register',           'BackofficePlace::register');
    $routes->post('spots/register',          'BackofficePlace::store');
    $routes->get('spots/(:num)/edit',        'BackofficePlace::edit/$1');
    $routes->post('spots/(:num)/edit',       'BackofficePlace::update/$1');
    $routes->post('spots/(:num)/state',      'BackofficePlace::toggleState/$1');

    // 행사·축제 관리
    $routes->get('festivals',                    'BackofficeEvent::list');
    $routes->get('festivals/register',           'BackofficeEvent::register');
    $routes->post('festivals/register',          'BackofficeEvent::store');
    $routes->get('festivals/(:num)/edit',        'BackofficeEvent::edit/$1');
    $routes->post('festivals/(:num)/edit',       'BackofficeEvent::update/$1');
    $routes->post('festivals/(:num)/state',      'BackofficeEvent::toggleState/$1');

    // 해시태그 API (자동완성)
    $routes->get('hashtags/search', 'BackofficeHashtag::search');

    // 네이버 Geocoding 프록시 (주소 → 위도/경도)
    $routes->get('geo/search', 'GeoController::search');

    // 지역별 탐색 관리
    $routes->get('region-explore',                              'BackofficeRegionExplore::index');
    $routes->get('region-explore/(:num)/top5',                  'BackofficeRegionExplore::getTop5/$1');
    $routes->post('region-explore/(:num)/top5/save',            'BackofficeRegionExplore::saveTop5/$1');
    $routes->post('region-explore/(:num)/state',                'BackofficeRegionExplore::toggleState/$1');
    $routes->get('region-explore/search',                       'BackofficeRegionExplore::search');

    // 여행코스 관리
    $routes->get('travel-courses',                          'BackofficeTravelCourse::list');
    $routes->get('travel-courses/register',                 'BackofficeTravelCourse::register');
    $routes->post('travel-courses/register',                'BackofficeTravelCourse::store');
    $routes->get('travel-courses/content-search',           'BackofficeTravelCourse::contentSearch');
    $routes->get('travel-courses/(:num)/edit',              'BackofficeTravelCourse::edit/$1');
    $routes->post('travel-courses/(:num)/edit',             'BackofficeTravelCourse::update/$1');
    $routes->post('travel-courses/(:num)/state',            'BackofficeTravelCourse::toggleState/$1');
    $routes->post('travel-courses/(:num)/delete',           'BackofficeTravelCourse::delete/$1');
});