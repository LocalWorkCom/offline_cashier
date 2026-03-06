<?php

namespace App\Http\Controllers\Api\HR_APIs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;

class DeviceController extends Controller
{
    protected $baseUrl;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = config('services.biotime.base_url');
        $this->token = 'Bearer ' . $this->getBioTimeToken(); // Assumes you've stored the token retrieval in a helper or service.
    }

    private function getBioTimeToken()
    {
        return env('BIOTIME_TOKEN');
    }

    public function index(Request $request)
    {
        $response = Http::withHeaders([
            'Authorization' => $this->token,
            'Content-Type' => 'application/json',
        ])->get("{$this->baseUrl}/iclock/api/terminals/", $request->query());

        return response()->json($response->json(), $response->status());
    }

    public function show($id)
    {
        $response = Http::withHeaders([
            'Authorization' => $this->token,
            'Content-Type' => 'application/json',
        ])->get("{$this->baseUrl}/iclock/api/terminals/{$id}/");

        return response()->json($response->json(), $response->status());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sn' => 'required|string',
            'alias' => 'required|string',
            'ip_address' => 'required|ip',
            'terminal_tz' => 'nullable|integer',
            'heartbeat' => 'nullable|integer',
            'area' => 'nullable|integer',
        ]);

        $response = Http::withHeaders([
            'Authorization' => $this->token,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/iclock/api/terminals/", $data);

        return response()->json($response->json(), $response->status());
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'sn' => 'required|string',
            'alias' => 'required|string',
            'ip_address' => 'required|ip',
            'terminal_tz' => 'nullable|integer',
            'heartbeat' => 'nullable|integer',
            'area' => 'nullable|integer',
        ]);

        $response = Http::withHeaders([
            'Authorization' => $this->token,
            'Content-Type' => 'application/json',
        ])->put("{$this->baseUrl}/iclock/api/terminals/{$id}/", $data);

        return response()->json($response->json(), $response->status());
    }

    public function destroy($id)
    {
        $response = Http::withHeaders([
            'Authorization' => $this->token,
            'Content-Type' => 'application/json',
        ])->delete("{$this->baseUrl}/iclock/api/terminals/{$id}/");

        return response()->json($response->json(), $response->status());
    }
}
