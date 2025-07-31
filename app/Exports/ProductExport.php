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
                'ID', 'Name', 'Description', 'Visible', 'Unit Regular Price', 'Unit Sale Price', 'Unit Price', 'Stock', 'Quantity Per Order', 'Position', 'Variations', 'Created Date'
            ]
        ];

        return $headings[$this->exportMode] ?? [];
    }

    private function exportProductPerLine()
    {
        return $this->query->doesNotSupportVariations()->get()->map(function ($product) {
            return [
                'ID' => $product->id,
                'Name' => '="' . $product->name . '"',
                'Description' => $product->show_description && $product->description != null ? $product->show_description : null,
                'Visible' => $product->visible ? 'yes' : 'no',
                'Unit Regular Price' => $product->unit_regular_price->amount_without_currency,
                'Unit Sale Price' => $product->unit_sale_price->amount_without_currency,
                'Unit Price' => $product->unit_price->amount_without_currency,
                'Stock' => $product->has_stock ? ($product->stock_quantity_type == 'unlimited' ? 'unlimited' : $product->stock_quantity) : 'no stock',
                'Quantity Per Order' => $product->allowed_quantity_per_order == 'unlimited' ? 'unlimited' : $product->maximum_allowed_quantity_per_order,
                'Position' => $product->position,
                'Variations' => $product->allow_variations ? $product->total_visible_variations : 'none',
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
