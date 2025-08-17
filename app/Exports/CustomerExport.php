<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CustomerExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    protected $query;
    protected $exportMode;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        return $this->exportCustomers();
    }

    public function headings(): array
    {
        return [ 'ID', 'First Name', 'Last Name', 'Email', 'Mobile', 'Birthday', 'Notes', 'Orders', 'Total Spend', 'Total Avg Spend', 'Last Order Date', 'Referral Code', 'Tags', 'Created Date'];
    }

    private function exportCustomers()
    {
        return $this->query->with(['tags'])->get()->map(function ($customer) {
            return [
                'ID' => $customer->id,
                'First Name' => $customer->first_name,
                'Last Name' => $customer->last_name,
                'Email' => $customer->email,
                'Mobile' => $customer->mobile_number ? $customer->mobile_number->formatE164() : null,
                'Birthday' => $customer->birthday ? $customer->birthday->format('Y-m-d') : null,
                'Notes' => $customer->notes,
                'Orders' => $customer->total_orders,
                'Total Spend' => $customer->total_spend->amount_without_currency,
                'Total Avg Spend' => $customer->total_average_spend->amount_without_currency,
                'Last Order Date' => $customer->last_order_at ? $customer->last_order_at->format('Y-m-d') : null,
                'Referral Code' => $customer->referral_code,
                'Tags' => $customer->tags->map(fn ($tag) => $tag->name)->implode(', '),
                'Created Date' => $customer->created_at->format('Y-m-d'),
            ];
        });
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Force landscape mode for PDF
                $event->sheet->getDelegate()->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            }
        ];
    }
}
