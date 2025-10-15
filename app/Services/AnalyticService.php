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
     * Show analytics.
     *
     * @param array $data
     * @return array
     */
    public function showAnalytics(array $data): array
    {
        $interval = $data['interval'];
        $type = AnalyticsType::from($data['type']);
        $store = Store::findOrFail($data['store_id']);
        $endDate = Carbon::parse($data['end_date'])->endOfDay();
        $startDate = Carbon::parse($data['start_date'])->startOfDay();

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

            default:
                $data = [];
                break;
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
            'weekly' => '%Y-W%V',     // e.g., "2025-W01"
            'monthly' => '%b',        // e.g., "Jan"
            default => '%d %b %Y'
        };

        $results = $query
            ->selectRaw("DATE_FORMAT({$dateColumn}, '{$dateFormat}') as date")
            ->selectRaw($countColumn ? "COUNT({$countColumn}) as count" : 'COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(function ($item) use ($interval) {
                return [$item->date => $item->count];
            })
            ->toArray();

        return $this->fillMissingDates($results, $query->getModel()->newQuery()->min($dateColumn), Carbon::now(), $interval);
    }

    /**
     * Fill missing dates with zero counts.
     *
     * @param array $data
     * @param string|null $startDate
     * @param Carbon $endDate
     * @param string $interval
     * @return array
     */
    protected function fillMissingDates(array $data, ?string $startDate, Carbon $endDate, string $interval): array
    {
        $start = $startDate ? Carbon::parse($startDate) : $endDate->copy()->subDays(30);
        $dates = [];
        $current = $start->copy();

        while ($current <= $endDate) {
            $key = match ($interval) {
                'daily' => $current->format('d M Y'),    // e.g., "04 Jan 2025" (using full month name for readability)
                'weekly' => $current->format('Y-W'),     // e.g., "2025-W01"
                'monthly' => $current->format('M'),      // e.g., "Jan" (short month name)
                default => $current->format('d M Y')
            };
            $dates[$key] = $data[$key] ?? 0;
            match ($interval) {
                'daily' => $current->addDay(),
                'weekly' => $current->addWeek(),
                'monthly' => $current->addMonth(),
                default => $current->addDay()
            };
        }

        return $dates;
    }
}
