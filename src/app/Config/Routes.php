<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('/users', 'Users::list');

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
});