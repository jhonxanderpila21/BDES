<?php

namespace App\Http\Controllers;

use App\Services\ExpenseLedgerService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseController extends Controller
{
    /**
     * Display the consolidated expenses ledger.
     */
    public function index(Request $request)
    {
        $filters = [
            'category'     => $request->get('category', 'all'),
            'search'       => $request->get('search'),
            'date_from'    => $request->get('date_from'),
            'date_to'      => $request->get('date_to'),
            'min_amount'   => $request->get('min_amount'),
            'max_amount'   => $request->get('max_amount'),
            'has_receipts' => $request->boolean('has_receipts'),
            'sort_by'      => $request->get('sort_by', 'date'),
            'sort_dir'     => $request->get('sort_dir', 'desc'),
        ];

        $fullLedger = ExpenseLedgerService::getLedger($filters);
        $kpis = ExpenseLedgerService::getSummaryKpis($fullLedger);

        $page = (int) $request->get('page', 1);
        $expenses = ExpenseLedgerService::paginate($fullLedger, 15, $page, [
            'path'  => $request->url(),
            'query' => $request->query(),
        ]);

        return view('admin.financials.expenses', compact('expenses', 'kpis', 'filters'));
    }

    /**
     * Export the filtered expenses ledger to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $filters = [
            'category'     => $request->get('category', 'all'),
            'search'       => $request->get('search'),
            'date_from'    => $request->get('date_from'),
            'date_to'      => $request->get('date_to'),
            'min_amount'   => $request->get('min_amount'),
            'max_amount'   => $request->get('max_amount'),
            'has_receipts' => $request->boolean('has_receipts'),
            'sort_by'      => $request->get('sort_by', 'date'),
            'sort_dir'     => $request->get('sort_dir', 'desc'),
        ];

        $ledger = ExpenseLedgerService::getLedger($filters);
        $filename = 'bdes-expenses-ledger-' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($ledger) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for proper Excel rendering of ₱ and special characters
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'Date',
                'Category',
                'Item / Description',
                'Reference / Code',
                'Quantity',
                'Unit Cost (PHP)',
                'Total Cost (PHP)',
                'Recorded By',
                'Receipts Attached',
                'Notes',
            ]);

            foreach ($ledger as $row) {
                fputcsv($handle, [
                    $row->formatted_date,
                    $row->category_label,
                    self::sanitizeCsvCell($row->item_name),
                    self::sanitizeCsvCell($row->reference),
                    $row->quantity,
                    number_format($row->unit_cost, 2, '.', ''),
                    number_format($row->total_cost, 2, '.', ''),
                    self::sanitizeCsvCell($row->recorded_by ?? 'N/A'),
                    $row->receipts_count > 0 ? 'Yes (' . $row->receipts_count . ' file/s)' : 'No',
                    self::sanitizeCsvCell($row->notes ?? ''),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Sanitize cell against CSV injection (=, +, -, @).
     */
    protected static function sanitizeCsvCell(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $trimmed = trim($value);
        if (preg_match('/^[\=\+\-\@\t\r]/', $trimmed)) {
            return "'" . $trimmed;
        }

        return $trimmed;
    }
}

