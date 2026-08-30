<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OrdersExport;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Order;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    public function index(Request $request): View
    {
        [$from, $to, $filters] = $this->parseFilters($request);

        return view('admin.reports.index', [
            'from' => $from,
            'to' => $to,
            'filters' => $filters,
            'summary' => $this->reports->summarize($from, $to, $filters),
            'byLocation' => $this->reports->byLocation($from, $to),
            'byRoom' => $this->reports->byRoom($from, $to, $filters['location_id'] ?? null),
            'orders' => $this->ordersQuery($request)->paginate(15)->withQueryString(),
            'locations' => Location::query()->orderBy('name')->get(),
        ]);
    }

    public function export(Request $request, string $type): BinaryFileResponse|Response
    {
        $orders = $this->ordersQuery($request)->get();

        return match ($type) {
            'csv' => Excel::download(new OrdersExport($orders), 'dinelify-orders-report.csv', \Maatwebsite\Excel\Excel::CSV),
            'xlsx' => Excel::download(new OrdersExport($orders), 'dinelify-orders-report.xlsx'),
            'pdf' => Pdf::loadView('admin.reports.pdf', ['orders' => $orders])->download('dinelify-orders-report.pdf'),
        };
    }

    private function ordersQuery(Request $request)
    {
        [$from, $to, $filters] = $this->parseFilters($request);

        return Order::query()
            ->with(['user.location', 'user.room', 'orderDetails'])
            ->whereDate('order_date', '>=', $from->format('Y-m-d'))
            ->whereDate('order_date', '<=', $to->format('Y-m-d'))
            ->where('status', '!=', 'cancelled')
            ->when(! empty($filters['location_id']), fn ($q) => $q->whereHas('user', fn ($q2) => $q2->where('location_id', $filters['location_id'])))
            ->when(! empty($filters['meal_type']), fn ($q) => $q->whereHas('orderDetails', fn ($q2) => $q2->where('meal_type', $filters['meal_type'])))
            ->when(! empty($filters['food_type']), fn ($q) => $q->whereHas('orderDetails', fn ($q2) => $q2->where('food_type', $filters['food_type'])))
            ->orderByDesc('order_date');
    }

    private function parseFilters(Request $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->string('from')) : Carbon::today();
        $to = $request->filled('to') ? Carbon::parse($request->string('to')) : Carbon::today();

        $filters = array_filter([
            'location_id' => $request->integer('location_id') ?: null,
            'meal_type' => $request->string('meal_type')->toString() ?: null,
            'food_type' => $request->string('food_type')->toString() ?: null,
        ]);

        return [$from, $to, $filters];
    }
}
