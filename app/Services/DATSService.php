<?php

namespace App\Services;

use Exception;
use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Google\Service\Sheets\BatchUpdateValuesRequest;
use Google\Service\Sheets\BatchClearValuesRequest;

class DATSService
{
    /**
     * The Google Spreadsheet ID (the long string in the sheet's URL).
     */
    private const SHEET_ID = '16ISdNhTvV-BoC1wSlm8_VexMYz63FCWpKfI1e0t8Sfk';

    /**
     * The tab name inside the spreadsheet.
     */
    private const SHEET_NAME = 'Sheet6';

    /**
     * Read all rows from the Google Sheet (public CSV export, no credentials needed).
     *
     * The first row is used as the headers, and every following row becomes
     * an associative array keyed by those headers. Empty rows are skipped.
     *
     * @return Collection<int, array<string, string|null>>
     *
     * @throws Exception If the sheet cannot be opened or has no header row.
     */
    public function getDocuments(): Collection
    {
        $csvUrl = 'https://docs.google.com/spreadsheets/d/' . self::SHEET_ID
            . '/gviz/tq?tqx=out:csv&sheet=' . self::SHEET_NAME;

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

            // Skip completely empty rows
            if (count(array_filter($row)) === 0) {
                continue;
            }

            // Make sure the row has exactly as many columns as the headers
            $row = array_pad($row, count($headers), null);
            $row = array_slice($row, 0, count($headers));

            $data[] = array_combine($headers, $row);
        }

        fclose($handle);

        return collect($data);
    }

    /**
     * Build an authenticated Google Sheets API client using a service account.
     *
     * Requires the credentials JSON at storage/app/google/credentials.json,
     * and the spreadsheet must be shared (Editor access) with the service
     * account's email address.
     *
     * @return Sheets
     */
    private function getSheetsService(): Sheets
    {
        $client = new Client();
        $client->setAuthConfig(storage_path('app/services/credentials.json'));

        $client->setScopes([Sheets::SPREADSHEETS]);

        return new Sheets($client);
    }

    /**
     * Add rows to the bottom of the sheet, after the last row that has data.
     *
     * Existing data is kept. Useful for logs or for adding new documents.
     *
     * @param  array<int, array<int, mixed>>  $rows  List of rows, each row is a list of cell values.
     * @return int  Number of cells that were added.
     */
    public function appendRows(array $rows): int
    {
        $sheets = $this->getSheetsService();

        $response = $sheets->spreadsheets_values->append(
            self::SHEET_ID,
            self::SHEET_NAME . '!A1836',
            new ValueRange(['values' => $rows]),
            [
                'valueInputOption' => 'USER_ENTERED',
                'insertDataOption' => 'INSERT_ROWS',
            ]
        );

        return $response->getUpdates()->getUpdatedCells();
    }

    /**
     * Write rows into the sheet starting at a specific cell, overwriting
     * whatever is there.
     *
     * The default start cell is A2, so the header row in row 1 is preserved.
     *
     * @param  array<int, array<int, mixed>>  $rows       List of rows, each row is a list of cell values.
     * @param  string                         $startCell  Where to start writing (e.g. "A2" or "C5").
     * @return int  Number of cells that were updated.
     */
    public function updateRows(array $rows, string $startCell = 'A2'): int
    {
        $sheets = $this->getSheetsService();

        $response = $sheets->spreadsheets_values->update(
            self::SHEET_ID,
            self::SHEET_NAME . '!' . $startCell,
            new ValueRange(['values' => $rows]),
            ['valueInputOption' => 'USER_ENTERED']
        );

        return $response->getUpdatedCells();
    }

    /**
     * Fetch data from an external API and replace the sheet's data rows with it.
     *
     * Steps:
     *  1. Call the API and read the JSON response (expects a list of records).
     *  2. Pick only the requested keys, in order, so they match the sheet columns.
     *  3. Clear the old data (row 2 and below, header row is kept).
     *  4. Write the new rows starting at A2.
     *
     * @param  string        $apiUrl   The API endpoint that returns a JSON list.
     * @param  array<string> $columns  API keys to write, in the same order as the sheet columns
     *                                 (e.g. ['id', 'name', 'email']).
     * @return int  Number of rows written.
     *
     * @throws Exception If the API request fails.
     */
    public function syncFromApi(string $apiUrl, array $columns): int
    {
        $response = Http::get($apiUrl);

        if ($response->failed()) {
            throw new Exception('API request failed with status ' . $response->status());
        }

        $rows = collect($response->json())
            ->map(fn(array $item) => array_map(
                fn(string $key) => $item[$key] ?? '',
                $columns
            ))
            ->values()
            ->toArray();

        $sheets = $this->getSheetsService();

        // Clear old data but keep the header row
        $sheets->spreadsheets_values->clear(
            self::SHEET_ID,
            self::SHEET_NAME . '!A30:Z',
            new Sheets\ClearValuesRequest()
        );

        if (!empty($rows)) {
            $this->updateRows($rows, 'A30');
        }

        return count($rows);
    }

    ///////////////////////////////////////////////////
    /**
     * Log in to the RDATS website and return an access token.
     *
     * The token is cached for 50 minutes so we don't log in on every request.
     * Adjust the cache time to match how long the token really lasts.
     *
     * @return string  The access token.
     *
     * @throws Exception If login fails or no token is found in the response.
     */
    private function getRdatsToken(): string
    {
        // Access token lasts 15 minutes, so cache for 10 to be safe
        return Cache::remember('rdats_token', now()->addMinutes(10), function () {
            $response = Http::acceptJson()->post(config('services.rdats.url') . '/auth', [
                'username' => config('services.rdats.username'),
                'password' => config('services.rdats.password'),
            ]);

            if ($response->failed()) {
                throw new Exception('RDATS login failed: ' . $response->status());
            }

            $token = $response->json('accessToken');

            if (!$token) {
                // Don't put the response body here, it contains the tokens
                throw new Exception('No accessToken found in RDATS login response.');
            }

            return $token;
        });
    }

    /**
     * Fetch data from a protected RDATS endpoint using the login token.
     *
     * If the server answers 401 (token expired), it logs in again once and retries.
     *
     * @param  string  $endpoint  Path after the domain, e.g. "/api/documents".
     * @param  array   $query     Optional query string parameters.
     * @return array  The decoded JSON response.
     *
     * @throws Exception If the request fails.
     */
    public function fetchFromRdats(string $endpoint, array $query = []): array
    {
        $url = config('services.rdats.url') . $endpoint;

        $response = Http::acceptJson()->withToken($this->getRdatsToken())->get($url, $query);

        if ($response->status() === 401) {
            Cache::forget('rdats_token');
            $response = Http::acceptJson()->withToken($this->getRdatsToken())->get($url, $query);
        }

        if ($response->failed()) {
            throw new Exception('RDATS request failed: ' . $response->status() . ' - ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Pull data from RDATS and write it into the Google Sheet starting at A30.
     *
     * @param  string         $endpoint  The RDATS data endpoint.
     * @param  array<string>  $columns   Keys to write, in the same order as the sheet columns.
     * @return int  Number of rows written.
     */
    public function syncFromRdats(string $endpoint, array $columns): int
    {
        $data = $this->fetchFromRdats($endpoint);

        // Some APIs wrap the list, e.g. {"data": [...]}
        $items = $data['data'] ?? $data;

        $rows = collect($items)
            ->map(fn(array $item) => array_map(fn(string $key) => $item[$key] ?? '', $columns))
            ->values()
            ->all();

        if (!empty($rows)) {
            $this->updateRows($rows, 'A30');
        }

        return count($rows);
    }

    /**
     * Fetch every page from the paginated RDATS endpoint and return all records.
     *
     * RDATS returns records under "success" and paging info under "pagination"
     * (total_pages, has_more). The loop stops when has_more is false, a page is
     * empty, or the safety limit is reached.
     *
     * @param  string  $endpoint  Path after the domain, e.g. "/dats".
     * @param  int     $perPage   Records per page.
     * @param  int     $maxPages  Safety limit to prevent endless loops.
     * @return array<int, array<string, mixed>>  All records from all pages.
     *
     * @throws Exception If any request fails.
     */
    public function fetchAllFromRdats(string $endpoint, int $perPage = 5, int $maxPages = 10): array
    {
        $all  = [];
        $page = 1;

        do {
            $response = $this->fetchFromRdats($endpoint, [
                'per_page' => $perPage,
                'page'     => $page,
            ]);

            $items = $response['success'] ?? [];

            if (empty($items) || !is_array($items)) {
                break;
            }

            foreach ($items as $item) {
                if (is_array($item)) {
                    $all[] = $item;
                }
            }

            $hasMore = (bool) ($response['pagination']['has_more'] ?? false);
            $page++;
        } while ($hasMore && $page <= $maxPages);

        return $all;
    }
    /**
     * Pull ALL records from a paginated RDATS endpoint and write them to the sheet.
     *
     * Rows are written in chunks of 500 starting at the given row, one chunk
     * right below the other. Nested arrays are converted to JSON text.
     * If no columns are given, all keys of the first record are used.
     *
     * @param  string         $endpoint  Path after the domain, e.g. "/dats".
     * @param  array<string>  $columns   Keys to write, in sheet column order (empty = all keys).
     * @param  int            $startRow  Row where writing begins (default 30).
     * @return int  Number of rows written.
     *
     * @throws Exception If fetching or writing fails.
     */
    public function syncAllFromRdats(string $endpoint, array $columns = [], int $startRow = 30): int
    {
        $items = $this->fetchAllFromRdats($endpoint);

        if (empty($items)) {
            return 0;
        }

        if (empty($columns)) {
            $columns = array_keys($items[0]);
        }

        $rows = array_map(function (array $item) use ($columns) {
            return array_map(function (string $key) use ($item) {
                $value = $item[$key] ?? '';
                return is_array($value) ? json_encode($value) : $value;
            }, $columns);
        }, $items);

        $row = $startRow;

        foreach (array_chunk($rows, 500) as $chunk) {
            $this->updateRows($chunk, 'A' . $row);
            $row += count($chunk);
        }

        return count($rows);
    }

    //===========================================

    /**
     * Read the division's documents straight from the region_dats database.
     *
     * Uses the same query the RDATS API runs, plus an ORDER BY so the result is
     * stable. Only documents handled by the given division are returned.
     *
     * @param  int  $division  Division id (6 in your case).
     * @return array<int, array<string, mixed>>  One array per document.
     */
    public function fetchDocumentsFromDatabase(
        int $division = 6,
        ?int $limit = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        $sql = "
    SELECT
        a.id, a.docid, a.originating_office,
        DATE_FORMAT(a.date_received, '%M %e, %Y') AS date_received,
        DATE_FORMAT(a.time_received, '%h:%i %p') AS time_received,
        a.sender,
        a.subject,
        s.action_required,
        a.addressee,
        a.doc_type,
        a.attachment,
        d.category,
        d.id AS category_id,
        a.sender_address,
        a.urgent,
        a.create_at,
        DATE_FORMAT(a.update_at, '%M %e, %Y') AS update_at,
        b.id AS create_id, b.role,
        CONCAT(b.first_name, ' ', UCASE(LEFT(b.middle_name, 1)), '.', ' ', b.last_name) AS fullname,
        c.division, c.id AS division_id,
        CASE WHEN MIN(e.completed) = 1 THEN '1' ELSE '0' END AS status,
        a.location
    FROM region_dats.doc a
    JOIN region_dats.employee b ON a.id_creator = b.id
    JOIN region_dats.division c ON b.division = c.id
    JOIN region_dats.category d ON a.category = d.id
    LEFT JOIN region_dats.dats_tags e ON e.dats_id = a.docid
    LEFT JOIN region_dats.subdoc s ON s.doc_id = a.docid
    WHERE s.division = ?";

        $bindings = [$division];

        // If a start date is given but no end date, the end date is today
        if ($dateFrom !== null && $dateTo === null) {
            $dateTo = now()->toDateString();
        }

        if ($dateFrom !== null) {
            $sql .= " AND a.date_received >= ?";
            $bindings[] = $dateFrom;
        }

        if ($dateTo !== null) {
            // Includes the whole end day
            $sql .= " AND a.date_received < DATE_ADD(?, INTERVAL 1 DAY)";
            $bindings[] = $dateTo;
        }

        $sql .= "
    GROUP BY a.location, s.action_required, a.id, a.docid, a.originating_office,
             a.date_received, a.time_received, a.sender, a.subject, a.addressee,
             a.doc_type, a.attachment, d.category, d.id, a.sender_address,
             a.urgent, a.create_at, a.update_at, b.id, b.first_name,
             b.middle_name, b.last_name, b.role, c.division, c.id
    ORDER BY a.date_received DESC, a.time_received DESC, a.id";

        if ($limit !== null) {
            $sql .= " LIMIT " . (int) $limit;
        }

        return array_map(
            fn($row) => (array) $row,
            DB::connection('region_dats')->select($sql, $bindings)
        );
    }
    /**
     * Clear the given columns from $startRow down to the end of the sheet.
     *
     * @param array<string> $letters  Column letters, e.g. ['A', 'C', 'D']
     */
    private function clearColumns(array $letters, int $startRow): void
    {
        $ranges = array_map(
            fn(string $l) => self::SHEET_NAME . "!{$l}{$startRow}:{$l}",
            $letters
        );

        $this->getSheetsService()->spreadsheets_values->batchClear(
            self::SHEET_ID,
            new BatchClearValuesRequest(['ranges' => $ranges])
        );
    }
    public function syncToColumns(
        array $columnMap,
        int $startRow = 30,
        ?int $limit = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): int {
        $items = $this->fetchDocumentsFromDatabase(6, $limit, $dateFrom, $dateTo);

        // Remove duplicate documents (one row per docid)
        $items = collect($items)->unique('docid')->values()->all();

        // Wipe old data first so leftover rows from earlier runs don't remain
        $this->clearColumns(array_keys($columnMap), $startRow);

        if (empty($items)) {
            return 0;
        }

        $sheets = $this->getSheetsService();
        $row    = $startRow;

        foreach (array_chunk($items, 1000) as $chunk) {
            $data = [];
            $end  = $row + count($chunk) - 1;

            foreach ($columnMap as $letter => $field) {
                $data[] = new ValueRange([
                    'range'  => self::SHEET_NAME . "!{$letter}{$row}:{$letter}{$end}",
                    'values' => array_map(fn(array $item) => [$item[$field] ?? ''], $chunk),
                ]);
            }

            $sheets->spreadsheets_values->batchUpdate(
                self::SHEET_ID,
                new BatchUpdateValuesRequest([
                    'valueInputOption' => 'USER_ENTERED',
                    'data'             => $data,
                ])
            );

            $row = $end + 1;
        }

        return count($items);
    }

    public function previewRows(
        int $division = 6,
        ?int $limit = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        return array_map(fn($r) => [
            'docid'              => $r['docid'],
            'date_received'      => $r['date_received'],
            'originating_office' => $r['originating_office'],
            'sender'             => $r['sender'],
            'subject'            => $r['subject'],
            'action_required'    => $r['action_required'],
        ], $this->fetchDocumentsFromDatabase($division, $limit, $dateFrom, $dateTo));
    }

    /**
     * Write documents from the database into the sheet, starting at the given row.
     *
     * @param  array<string>  $columns   Keys to write, in sheet column order (empty = all).
     * @param  int            $startRow  Row where writing begins.
     * @return int  Number of rows written.
     */
    public function syncFromDatabase(array $columns = [], int $startRow = 1900): int
    {
        $items = $this->fetchDocumentsFromDatabase(6);

        if (empty($items)) {
            return 0;
        }

        $columns = $columns ?: array_keys($items[0]);

        $rows = array_map(
            fn(array $item) => array_map(fn(string $key) => $item[$key] ?? '', $columns),
            $items
        );

        $row = $startRow;
        foreach (array_chunk($rows, 500) as $chunk) {
            $this->updateRows($chunk, 'A' . $row);
            $row += count($chunk);
        }

        return count($rows);
    }
    public function previewFromRdats(DATSService $dats)
    {
        try {
            $rows = $dats->previewRows();
            return response()->json(['data' => $rows, 'count' => count($rows)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
