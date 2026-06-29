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
    $routes->get('members',           'Backoffice::members');
    $routes->get('withdrawn-members', 'Backoffice::withdrawnMembers');
    $routes->get('inquiries',         'Backoffice::inquiries');
    $routes->get('faqs',              'Backoffice::faqs');
    $routes->get('error-logs',        'Backoffice::errorLogs');
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