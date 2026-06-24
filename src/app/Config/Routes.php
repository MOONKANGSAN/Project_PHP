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
    $routes->get('dashboard',         'Backoffice::dashboard');
    $routes->get('restaurants',       'Backoffice::restaurants');
    $routes->get('spots',             'Backoffice::spots');
    $routes->get('festivals',         'Backoffice::festivals');
    $routes->get('members',           'Backoffice::members');
    $routes->get('withdrawn-members', 'Backoffice::withdrawnMembers');
    $routes->get('inquiries',         'Backoffice::inquiries');
    $routes->get('faqs',              'Backoffice::faqs');
    $routes->get('error-logs',        'Backoffice::errorLogs');
    $routes->get('site-config',       'Backoffice::siteConfig');
});