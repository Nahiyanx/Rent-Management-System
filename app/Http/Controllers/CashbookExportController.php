<?php

namespace App\Http\Controllers;

use App\Support\CashbookReport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CashbookExportController extends Controller
{
    public function pdf(Request $request): Response
    {
        $month = $this->month($request);

        return response(CashbookReport::pdf($month), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.CashbookReport::filename($month, 'pdf').'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    public function xlsx(Request $request): BinaryFileResponse
    {
        $month = $this->month($request);

        return response()
            ->download(CashbookReport::xlsxPath($month), CashbookReport::filename($month, 'xlsx'))
            ->deleteFileAfterSend(true);
    }

    private function month(Request $request): string
    {
        $month = (string) $request->query('month', '2026-11');

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            abort(404);
        }

        return $month;
    }
}
