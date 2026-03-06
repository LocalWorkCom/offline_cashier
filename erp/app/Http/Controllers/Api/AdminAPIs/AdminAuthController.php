<?php

namespace App\Http\Controllers\Api\AdminAPIs;

use App\Http\Controllers\Controller;
use App\Services\AuthAppService;
use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    protected $authService;

    public function __construct(AuthAppService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $response = $this->authService->login(
            $request->all(),
            'adminApp',
            'admin',   
            $lang
        );
        return response()->json($response, $response['code']);
    }
}
