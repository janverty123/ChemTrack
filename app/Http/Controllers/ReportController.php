<?php

namespace App\Http\Controllers;

use App\Models\Programme;
use App\Services\ProgressReport;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private function data(Request $request, Programme $programme): array
    {
        return app(ProgressReport::class)->forProgramme($request->user(), $programme);
    }

    public function index(Request $request, Programme $programme)
    {
        return view('teacher.results', $this->data($request, $programme));
    }

    public function export(Request $request, Programme $programme)
    {
        $data = $this->data($request, $programme);

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Student reference', 'Activity', 'Type', 'Cycle', 'Cycle completion percent', 'Stage', 'Stage complete', 'Reading percent (estimate)', 'Score percent', 'Started UTC', 'Submitted UTC', 'Completed UTC', 'Status', 'Evidence'], ',', '"', '');
            foreach ($data['rows'] as $row) {
                $values = [$row['student'], $row['activity'], $row['type'], $row['cycle'], $row['cycle_percent'], $row['stage'], $row['stage_complete'] === null ? '' : ($row['stage_complete'] ? 'yes' : 'no'), $row['reading'], $row['score'], $row['started_at']?->utc()->toIso8601String(), $row['submitted_at']?->utc()->toIso8601String(), $row['completed_at']?->utc()->toIso8601String(), $row['status'], $row['evidence']];
                $values = array_map(fn ($value) => is_string($value) && preg_match('/^[\s]*[=+@-]/u', $value) ? "'".$value : $value, $values);
                fputcsv($out, $values, ',', '"', '');
            }
            foreach ($data['stageRows'] as $stage) {
                $values = [$stage['student'], 'Stage summary', '', $stage['cycle'], $stage['cycle_percent'], $stage['stage'], $stage['status'] === 'complete' ? 'yes' : 'no', '', '', '', '', '', $stage['status'], ''];
                $values = array_map(fn ($value) => is_string($value) && preg_match('/^[\s]*[=+@-]/u', $value) ? "'".$value : $value, $values);
                fputcsv($out, $values, ',', '"', '');
            }
            fclose($out);
        }, 'chemtrack-programme-'.$programme->id.'-compliance.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'no-store']);
    }
}
