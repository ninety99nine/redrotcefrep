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
        $this->exportMode = request()->input('export_mode', 'without_variants');
    }

    public function collection()
    {
        switch ($this->exportMode) {
            case 'with_variants':
                return $this->exportWithVariants();
            case 'without_variants':
                return $this->exportWithoutVariants();
            default:
                return collect();
        }
    }

    public function headings(): array
    {
        $headings = [
            'with_variants' => [
                'ID', 'Name', 'Parent Name', 'Free', 'Estimated Price', 'Regular Price', 'Sale Price', 'Cost Price',
                'Visible', 'Type', 'Download Link', 'Sku', 'Barcode', 'Show Description', 'Description',
                'Weight', 'Tax Override', 'Tax Override Amount', 'Show Price Per Unit', 'Unit Value',
                'Unit Type', 'Set Daily Capacity', 'Daily Capacity', 'Stock Type', 'Stock Quantity',
                'Set Min Order Quantity', 'Min Order Quantity', 'Set Max Order Quantity', 'Max Order Quantity',
                'Categories', 'Tags', 'Position', 'Created Date'
            ],
            'without_variants' => [
                'ID', 'Name', 'Free', 'Estimated Price', 'Regular Price', 'Sale Price', 'Cost Price',
                'Visible', 'Type', 'Download Link', 'Sku', 'Barcode', 'Show Description', 'Description',
                'Weight', 'Tax Override', 'Tax Override Amount', 'Show Price Per Unit', 'Unit Value',
                'Unit Type', 'Set Daily Capacity', 'Daily Capacity', 'Stock Type', 'Stock Quantity',
                'Set Min Order Quantity', 'Min Order Quantity', 'Set Max Order Quantity', 'Max Order Quantity',
                'Categories', 'Tags', 'Position', 'Created Date'
            ]
        ];

        return $headings[$this->exportMode] ?? [];
    }

    private function exportWithVariants()
    {
        return $this->query->with(['variants', 'categories', 'tags'])->get()->flatMap(function ($product) {
            $rows = collect();

            // Add parent product if it has no parent (not a variant itself)
            if (!$product->parent_product_id) {
                $rows->push($this->formatProductRow($product));
            }

            // Add all variants
            return $rows->merge($product->variants->map(function ($variant) use ($product) {
                return $this->formatProductRow($variant, $product);
            }));
        });
    }

    private function exportWithoutVariants()
    {
        return $this->query->with(['categories', 'tags'])->get()->map(function ($product) {
            return $this->formatProductRow($product);
        });
    }

    private function formatProductRow($product, $parentProduct = null)
    {
        $row = [
            'ID' => $product->id,
            'Name' => $product->name,
            'Parent Name' => $parentProduct ? $parentProduct->name : null,
            'Free' => $product->is_free ? 'yes' : 'no',
            'Estimated Price' => $product->is_estimated_price ? 'yes' : 'no',
            'Regular Price' => $product->unit_regular_price ? $product->unit_regular_price->amount_without_currency : null,
            'Sale Price' => $product->unit_sale_price ? $product->unit_sale_price->amount_without_currency : null,
            'Cost Price' => $product->unit_cost_price ? $product->unit_cost_price->amount_without_currency : null,
            'Visible' => $product->visible ? 'yes' : 'no',
            'Type' => $product->type,
            'Download Link' => $product->download_link,
            'Sku' => $product->sku,
            'Barcode' => $product->barcode,
            'Show Description' => $product->show_description ? 'yes' : 'no',
            'Description' => $product->description,
            'Weight' => $product->unit_weight,
            'Tax Override' => $product->tax_overide ? 'yes' : 'no',
            'Tax Override Amount' => $product->tax_overide_amount ? $product->tax_overide_amount->amount_without_currency : null,
            'Show Price Per Unit' => $product->show_price_per_unit ? 'yes' : 'no',
            'Unit Value' => $product->unit_value,
            'Unit Type' => $product->unit_type,
            'Set Daily Capacity' => $product->set_daily_capacity ? 'yes' : 'no',
            'Daily Capacity' => $product->daily_capacity,
            'Stock Type' => $product->stock_quantity_type,
            'Stock Quantity' => $product->stock_quantity,
            'Set Min Order Quantity' => $product->set_min_order_quantity ? 'yes' : 'no',
            'Min Order Quantity' => $product->min_order_quantity,
            'Set Max Order Quantity' => $product->set_max_order_quantity ? 'yes' : 'no',
            'Max Order Quantity' => $product->max_order_quantity,
            'Categories' => $product->categories->map(fn ($category) => $category->name)->implode(', '),
            'Tags' => $product->tags->map(fn ($tag) => $tag->name)->implode(', '),
            'Position' => $product->position,
            'Position' => $product->position,
            'Created Date' => $product->created_at->format('Y-m-d'),
        ];

        if($this->exportMode == 'without_variants') {
            unset($row['Parent Name']);
        }

        return $row;
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
