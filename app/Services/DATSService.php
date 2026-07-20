<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Collection;

class DATSService
{
    public function getDocuments(): Collection
    {
        $sheetId = '16ISdNhTvV-BoC1wSlm8_VexMYz63FCWpKfI1e0t8Sfk';
        $sheetName = 'Sheet1';

        $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/gviz/tq?tqx=out:csv&sheet={$sheetName}";

        $handle = fopen($csvUrl, 'r');

        if (!$handle) {
            throw new Exception('Unable to open Google Sheet CSV.');
        }

        $headers = fgetcsv($handle);

        if (!$headers) {
            throw new Exception('Headers not found.');
        }

        $data = [];

        while (($row = fgetcsv($handle)) !== false) {

            if (count(array_filter($row)) === 0) {
                continue;
            }

            $row = array_pad($row, count($headers), null);
            $row = array_slice($row, 0, count($headers));

            $data[] = array_combine($headers, $row);
        }

        fclose($handle);

        return collect($data);
    }
}