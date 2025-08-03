<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class ProductExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    protected $query;
    protected $exportMode;

    public function __construct($query)
    {
        $this->query = $query;
        $this->exportMode = request()->input('export_mode', 'product_per_line');
    }

    public function collection()
    {
        switch ($this->exportMode) {
            case 'product_per_line':
                return $this->exportProductPerLine();
            default:
                return collect();
        }
    }

    public function headings(): array
    {
        $headings = [
            'product_per_line' => [
                'ID', 'Name', 'Description', 'Visible', 'Unit Regular Price', 'Unit Sale Price', 'Unit Price', 'Stock', 'Quantity Per Order', 'Position', 'Variants', 'Created Date'
            ]
        ];

        return $headings[$this->exportMode] ?? [];
    }

    private function exportProductPerLine()
    {
        return $this->query->doesNotSupportVariants()->withCount('variants')->get()->map(function ($product) {
            return [
                'ID' => $product->id,
                'Name' => '="' . $product->name . '"',
                'Description' => $product->show_description && $product->description != null ? $product->show_description : null,
                'Visible' => $product->visible ? 'yes' : 'no',
                'Unit Regular Price' => $product->unit_regular_price->amount_without_currency,
                'Unit Sale Price' => $product->unit_sale_price->amount_without_currency,
                'Unit Price' => $product->unit_price->amount_without_currency,
                'Stock' => $product->has_stock ? ($product->stock_quantity_type == 'unlimited' ? 'unlimited' : $product->stock_quantity) : 'no stock',
                'Min Order Quantity' => $product->set_min_order_quantity ? $product->min_order_quantity : 'none',
                'Max Order Quantity' => $product->set_max_order_quantity ? $product->max_order_quantity : 'none',
                'Position' => $product->position,
                'Variants' => $product->variants_count ? $product->variants_count : 'none',
                'Created Date' => $product->created_at->format('Y-m-d')
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
