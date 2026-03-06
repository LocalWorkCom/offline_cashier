<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Dish;
use App\Models\Order;
use App\Models\BranchMenu;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Services\ReportServices\BestSellerDishReportService;

class BestSellerDishReportController extends Controller
{
    protected $bestSellerDishReportService;
    protected $lang;
    protected $checkToken;  // Set to true or false based on your need
    protected $branchService;


    public function __construct(BestSellerDishReportService $bestSellerDishReportService)
    {
        $this->bestSellerDishReportService = $bestSellerDishReportService;
        $this->lang =  app()->getLocale();
        $this->checkToken = false;
    }

    public function index(Request $request)
    {
        $dishes = $this->bestSellerDishReportService->index($request)->get();

        return view('dashboard.reports.bestDish.list', compact('dishes'));
    }
    public function show($id)
    {
        $report = $this->bestSellerDishReportService->show($id);

        return view('dashboard.reports.bestDish.show', [
            'dish' => $report['dish'],
            'branches' => $report['branches'],
            'order' => ['addons' => $report['addons']],
            'totals' => $report['totals']
        ]);
    }

    public function print($id)
    {
        $data = $this->bestSellerDishReportService->print($id);

        return view('dashboard.reports.bestDish.print', $data);
    }
}
