<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestFloatController extends Controller
{
    /**
     * Test the formatFloat helper with a given float value.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testFormatFloat(Request $request)
    {
        // require_once app_path('helper.php');

        $value = $request->query('value', 123456.98765432441);
        $rounded = round($value, 2);


        return response()->json([
            'original' => $value,
            'rounded' => $rounded,
        ]);
    }
}
