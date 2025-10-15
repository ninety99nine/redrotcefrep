<?php

namespace App\Services;

use App\Models\Store;
use App\Models\PageView;
use App\Models\StoreVisitor;
use App\Enums\AnalyticsType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticService extends BaseService
{
    /**
     * Determine the interval based on the date range.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return string
     */
    protected function determineInterval(Carbon $startDate, Carbon $endDate): string
    {
        if ($startDate->isSameMonth($endDate)) {
            return 'daily';
        } elseif ($startDate->isSameYear($endDate)) {
            return 'monthly';
        } else {
            return 'yearly';
        }
    }

    /**
     * Show analytics.
     *
     * @param array $data
     * @return array
     */
    public function showAnalytics(array $data): array
    {
        $type = AnalyticsType::from($data['type']);
        $store = Store::findOrFail($data['store_id']);
        $endDate = Carbon::parse($data['end_date'])->endOfDay();
        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $interval = $this->determineInterval($startDate, $endDate);

        switch ($type) {
            case AnalyticsType::PAGE_VIEWS:
                $query = PageView::where('store_id', $store->id)
                    ->whereBetween('created_at', [$startDate, $endDate]);
                $data = $this->aggregateByInterval($query, $interval, 'created_at');
                break;

            case AnalyticsType::STORE_VISITORS:
                $query = StoreVisitor::where('store_id', $store->id)
                    ->whereBetween('last_visited_at', [$startDate, $endDate]);
                $data = $this->aggregateByInterval($query, $interval, 'last_visited_at', 'distinct COALESCE(user_id, guest_id)');
                break;

            case AnalyticsType::TOP_PAGES:
                $data = PageView::where('store_id', $store->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->select('name')
                    ->selectRaw('COUNT(*) as count')
                    ->groupBy('name')
                    ->orderByDesc('count')
                    ->limit(10)
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [$item->name => $item->count];
                    })
                    ->toArray();
                break;

            default:
                $data = [];
                break;
        }

        if ($type !== AnalyticsType::TOP_PAGES) {
            $data = $this->fillMissingDates($data, $startDate, $endDate, $interval);
        }

        return [
            'labels' => array_keys($data),
            'values' => array_values($data)
        ];
    }

    /**
     * Aggregate data by the specified interval.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $interval
     * @param string $dateColumn
     * @param string|null $countColumn
     * @return array
     */
    protected function aggregateByInterval($query, string $interval, string $dateColumn, ?string $countColumn = null): array
    {
        $dateFormat = match ($interval) {
            'daily' => '%d %b %Y',    // e.g., "04 Jan 2025"
            'monthly' => '%b %Y',     // e.g., "Jan 2025"
            'yearly' => '%Y',         // e.g., "2025"
            default => '%d %b %Y'
        };

        $results = $query
            ->selectRaw("DATE_FORMAT({$dateColumn}, '{$dateFormat}') as date")
            ->selectRaw($countColumn ? "COUNT({$countColumn}) as count" : 'COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->date => $item->count];
            })
            ->toArray();

        return $results;
    }

    /**
     * Fill missing dates with zero counts.
     *
     * @param array $data
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $interval
     * @return array
     */
    protected function fillMissingDates(array $data, Carbon $startDate, Carbon $endDate, string $interval): array
    {
        $dates = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $key = match ($interval) {
                'daily' => $current->startOfDay()->format('d M Y'),    // e.g., "04 Jan 2025"
                'monthly' => $current->startOfMonth()->format('M Y'),    // e.g., "Jan 2025"
                'yearly' => $current->startOfYear()->format('Y'),       // e.g., "2025"
                default => $current->startOfDay()->format('d M Y')
            };
            $dates[$key] = $data[$key] ?? 0;
            match ($interval) {
                'daily' => $current->addDay(),
                'monthly' => $current->addMonth(),
                'yearly' => $current->addYear(),
                default => $current->addDay()
            };
        }

        return $dates;
    }
}
