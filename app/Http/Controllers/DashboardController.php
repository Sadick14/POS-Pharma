<?php

namespace App\Http\Controllers;

use App\Services\AlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(protected AlertService $alertService)
    {
    }

    public function index()
    {
        $metrics = $this->alertService->getDashboardMetrics();
        $lowStockMedicines = $this->alertService->getLowStockMedicines()->take(5);
        $expiringBatches = $this->alertService->getExpiringBatches(30)->take(5);

        return view('dashboard', compact('metrics', 'lowStockMedicines', 'expiringBatches'));
    }
}
