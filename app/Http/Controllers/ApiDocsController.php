<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Route;

class ApiDocsController extends BaseController
{
    public function index()
    {
        $routes = collect(Route::getRoutes())->filter(function ($route) {
            return str_starts_with($route->uri(), 'api/') && $route->methods()[0] !== 'HEAD';
        })->map(function ($route) {
            return [
                'method' => implode(', ', $route->methods()),
                'uri' => '/' . $route->uri(),
                'name' => $route->getName(),
                'middleware' => collect($route->middleware())->filter(fn ($m) => !str_contains($m, 'Illuminate'))->values()->toArray(),
            ];
        })->sortBy('uri')->values();

        $grouped = [
            'Auth' => $routes->filter(fn ($r) => str_contains($r['uri'], '/login') || str_contains($r['uri'], '/user') || str_contains($r['uri'], '/logout')),
            'Products' => $routes->filter(fn ($r) => str_contains($r['uri'], '/products')),
            'Categories' => $routes->filter(fn ($r) => str_contains($r['uri'], '/categories')),
            'Customers' => $routes->filter(fn ($r) => str_contains($r['uri'], '/customers')),
            'Orders' => $routes->filter(fn ($r) => str_contains($r['uri'], '/orders')),
            'Payments' => $routes->filter(fn ($r) => str_contains($r['uri'], '/payment')),
            'Tables' => $routes->filter(fn ($r) => str_contains($r['uri'], '/tables')),
            'Webhooks' => $routes->filter(fn ($r) => str_contains($r['uri'], '/webhooks')),
            'POS Internal' => $routes->filter(fn ($r) => str_contains($r['uri'], '/api/pos')),
        ];

        return view('pages.api-docs', compact('grouped'));
    }
}
