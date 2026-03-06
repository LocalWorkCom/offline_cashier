<?php

namespace App\Http\Controllers\Api\DeliveryAPIs;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Services\HR_Services\TimetableService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class DeliveryRouteController extends Controller
{
    public function getOptimizedRoute(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if (!$employee) {
            return RespondWithUnauthorizedRequest($lang, 4);
        }

        $branch = Employee::where('id', $employee->id)->with('branch')->first();

        $branchLat = $branch->branch->latitute ?? null;
        $branchLng = $branch->branch->longitute ?? null;

        if (!$branchLat || !$branchLng) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'Branch location not found' : 'لم يتم العثور على موقع الفرع.',
                'errorData' => ['error' => $lang == 'en' ? 'Branch location not found' : 'لم يتم العثور على موقع الفرع.'],
                'data' => null
            ], 200);
        }

        $today = Carbon::today();
        $shiftDetails = TimetableService::getTimetableForDate($employee->id, $today);

        $ordersQuery = Order::where('delivery_id', $employee->id)
            ->with(['Client', 'address', 'tracking'])
            ->where('status', 'packing')
            ->whereDoesntHave('tracking', function ($query) {
                $query->whereIn('order_status', ['delivered', 'completed', 'cancelled']);
            });

        $orders = $ordersQuery->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => $lang == 'en' ? 'No orders found.' : 'لا يوجد طلبات.',
                'data' => [
                    'optimized_route' => []
                ]
            ], 200);
        }

        $deliveryPoints = [];
        foreach ($orders as $order) {
            $clientAddress = $order->address;
            if ($clientAddress) {
                $deliveryPoints[] = [
                    'order_id' => $order->id,
                    'tracking_status' => array_search(
                        $order->tracking->last()->order_status,
                        OrderTracking::$statusMap
                    ) ?? null,
                    'created_at' => $order->created_at,
                    'client_address_id' => $clientAddress->id,
                    'lat' => $clientAddress->latitude,
                    'lng' => $clientAddress->longtitude,
                    'order_number' => $order->order_number,
                    'address' => $clientAddress->address,
                    'address_notes' => $clientAddress->notes,
                    'client_id' => $order->client_id ?? null,
                    'client_phone' => $order->Client->flag != 'unknown' ? $order->Client->phone : $clientAddress->address_phone,
                    'client_address_phone' => $clientAddress->address_phone,
                    'client_name' => $order->Client->flag != 'unknown' ? $order->Client->name : $order->address->user_name,
                ];
            } else {
                Log::warning("Client address not found for Order ID: {$order->id}");
            }
        }

        if (empty($deliveryPoints)) {
            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => $lang == 'en' ? 'No orders found.' : 'لا يوجد طلبات.',
                'data' => [
                    'optimized_route' => []
                ]
            ], 200);
        }

        $route = getSmartDeliveryRoute($branchLat, $branchLng, $deliveryPoints);

        if (isset($route['error'])) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'message' => $lang == 'en' ? 'Error fetching optimized route' : 'خطأ في استرجاع المسار الأمثل.',
                'errorData' => ['error' => $route['error']],
                'data' => null
            ], 500);
        }

        $optimizedRoute = $route['optimized_route'];
        $legDistances = $route['leg_distances'] ?? []; // New: Extract leg distances from API

        $data = [];
        $cumulativeDistance = 0;

        foreach ($optimizedRoute as $index => $point) {
            $order = $orders->firstWhere('id', $point['order_id']);
            $clientAddress = $order->address;

            // Use API-provided distance for this leg, if available
            $legDistance = isset($legDistances[$index]) ? $legDistances[$index] : null;
            if ($legDistance !== null) {
                $cumulativeDistance += $legDistance; // Accumulate total distance
            }

            $data[] = [
                'order_id' => $point['order_id'],
                'tracking_status' => array_search(
                    $order->tracking->last()->order_status,
                    OrderTracking::$statusMap
                ) ?? null,
                'created_at' => $order->created_at,
                'order_number' => $order->order_number,
                'address' => $clientAddress->address,
                'address_notes' => $clientAddress->notes,
                'client_id' => $order->client_id ?? null,
                'client_phone' => $order->Client->flag != 'unknown' ? $order->Client->phone : $clientAddress->address_phone,
                'client_address_phone' => $clientAddress->address_phone,
                'client_name' => $order->Client->flag != 'unknown' ? $order->Client->name : $order->address->user_name,
                'lat' => $point['lat'],
                'lng' => $point['lng'],
                'leg_distance_meters' => $legDistance, // Distance to this point from previous
                'cumulative_distance_meters' => $legDistance !== null ? $cumulativeDistance : null, // Total distance up to this point
            ];
        }

        $response = [
            'optimized_route' => $data,
            'google_map_link' => $route['google_map_link'],
            'branch_info' => [
                'id' => $branch->branch->id,
                'name' => $lang == 'en' ? $branch->branch->name_en : $branch->branch->name_ar,
                'lat' => $branch->branch->latitute,
                'lng' => $branch->branch->longitute,
            ]
        ];

        return ResponseWithSuccessData($lang, $response, 1);
    }
}
