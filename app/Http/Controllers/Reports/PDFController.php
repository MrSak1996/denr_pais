<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Facades\Response;


use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Element\Table;

use Carbon\Carbon;

class PDFController extends Controller
{
    /**
     * Convert a number to words (supports 0-999)
     */



    public function generateTable(Request $request, int $id)
    {
        // Get application info
        $application = DB::table('tbl_application_checklist as ac')
            ->leftJoin('tbl_chainsaw_information as ci', 'ci.application_id', '=', 'ac.id')
            ->leftJoin('tbl_application_payment as ap', 'ap.application_id', '=', 'ac.id')
            ->where('ac.id', $id)
            ->select([
                'ac.permit_no as permit_number',
                'ac.authorized_representative',
                DB::raw("CONCAT_WS(' ', ac.applicant_firstname, ac.applicant_middlename, ac.applicant_lastname) AS applicant_name"),
                'ac.applicant_complete_address',
                'ci.engine_serial_no',
                'ac.company_address',
                'ci.permit_chainsaw_no',
                'ci.issued_date',
                'ci.permit_validity',
                'ci.issued_by',
                'ci.quantity',
                'ci.supplier_name',
                'ci.supplier_address',
                'ci.purpose',
                'ci.other_details',
                'ac.approved_date',
                'ci.permit_validity as expiry_date',
                'ap.official_receipt',
                'ap.date_of_payment',
            ])
            ->first();

        if (!$application) {
            abort(404, 'Application not found');
        }

        // Get brands and models
        $rows = DB::table('chainsaw_brands as cb')
            ->leftJoin('chainsaw_models as cm', 'cm.brand_id', '=', 'cb.id')
            ->where('cb.application_id', $id)
            ->select('cb.brand_name', 'cm.model', 'cm.quantity')
            ->get();

        // Group by brand
        $brands = $rows->groupBy('brand_name')->map(function ($items) {
            return [
                'brand_name' => $items->first()->brand_name,
                'models' => $items->map(function ($item) {
                    return [
                        'model' => $item->model,
                        'quantity' => $item->quantity,
                    ];
                })->filter(fn($m) => $m['model'] !== null)->values()
            ];
        })->values();

        // Calculate total quantity from all models
        $totalQuantity = $rows->sum('quantity');
        $quantityInWords = ucfirst($this->numberToWords((int)$totalQuantity));
        $quantityText = "{$quantityInWords} ({$totalQuantity}) unit" . ($totalQuantity > 1 ? 's' : '') . "";
        $model_qty = "{$totalQuantity}";

        // Pass data to PDF view
        $pdf = Pdf::loadView('pdf.table-template', [
            'permit_number' => $application->permit_number,
            'name' => $application->authorized_representative ?? $application->applicant_name,
            'address' => $application->applicant_complete_address,
            'complete_address' => $application->company_address ?? $application->applicant_complete_address,
            'quantity' => $quantityText,
            'model_qty' => $model_qty,
            'brands' => $brands,
            'permit_chainsaw_no' => $application->permit_chainsaw_no,
            'engine_serial_no' => $application->engine_serial_no,
            'supplier_name' => $application->supplier_name,
            'supplier_address' => $application->supplier_address,
            'purpose' => $application->purpose,
            'others' => $application->other_details,
            'issued_by' => $application->issued_by,
            'issued_date' => $application->issued_date ? \Carbon\Carbon::parse($application->issued_date)->format('F d, Y') : null,
            'permit_validity' => $application->permit_validity ? \Carbon\Carbon::parse($application->permit_validity)->format('F d, Y') : null,
            'expiry_date' => $application->expiry_date ? \Carbon\Carbon::parse($application->expiry_date)->format('F d, Y') : null,
            'or_number' => $application->official_receipt,
            'or_date' => $application->date_of_payment ? \Carbon\Carbon::parse($application->date_of_payment)->format('F d, Y') : null,
        ]);

        return $pdf->stream('permit.pdf');
    }


    public function getChainsawBrandsWithModels($applicationId)
    {
        $rows = DB::table('chainsaw_brands as cb')
            ->leftJoin('chainsaw_models as cm', 'cm.brand_id', '=', 'cb.id')
            ->where('cb.application_id', $applicationId)
            ->select(
                'cb.id as brand_id',
                'cb.brand_name',
                'cm.id as model_id',
                'cm.model',
                'cm.quantity'
            )
            ->orderBy('cb.id')
            ->get();

        // Group into Vue-friendly structure
        $brands = [];

        foreach ($rows as $row) {
            if (!isset($brands[$row->brand_id])) {
                $brands[$row->brand_id] = [
                    'brand_name'   => $row->brand_name,
                    'quantity'     => $row->quantity,
                    'models' => []
                ];
            }

            if ($row->model_id) {
                $brands[$row->brand_id]['models'][] = [
                    'model'    => $row->model,
                    'quantity' => $row->quantity
                ];
            }
        }

        // Reset array keys
        return response()->json(array_values($brands));
    }

    /**
     * Preview permit (for testing purposes)
     */


    public function generatePermitDocx($id)
    {
        $application = DB::table('tbl_application_checklist as ac')
            ->leftJoin('tbl_chainsaw_information as ci', 'ci.application_id', '=', 'ac.id')
            ->leftJoin('tbl_application_payment as ap', 'ap.application_id', '=', 'ac.id')
            ->where('ac.id', $id)
            ->select([
                'ac.permit_no as permit_number',
                'ac.authorized_representative',
                DB::raw("CONCAT_WS(' ', ac.applicant_firstname, ac.applicant_middlename, ac.applicant_lastname) AS applicant_name"),
                'ac.applicant_complete_address',
                'ci.engine_serial_no',
                'ac.company_address',
                'ac.expiry_date',
                'ci.permit_chainsaw_no',
                'ci.issued_date',
                'ci.permit_validity',
                'ci.issued_by',
                'ci.quantity',
                'ci.supplier_name',
                'ci.supplier_address',
                'ci.purpose',
                'ci.other_details',
                'ac.approved_date',
                'ap.official_receipt',
                'ap.date_of_payment',
            ])
            ->first();

        if (!$application) {
            abort(404, 'Application not found');
        }

        // Get brands and models
        $rows = DB::table('chainsaw_brands as cb')
            ->leftJoin('chainsaw_models as cm', 'cm.brand_id', '=', 'cb.id')
            ->where('cb.application_id', $id)
            ->select('cb.brand_name', 'cm.model', 'cm.quantity')
            ->get();

        $brands = $rows->groupBy('brand_name')->map(function ($items) {
            return [
                'brand_name' => $items->first()->brand_name,
                'models' => $items->map(function ($item) {
                    return [
                        'model' => $item->model,
                        'quantity' => $item->quantity,
                    ];
                })->filter(fn($m) => $m['model'] !== null)->values()
            ];
        })->values();

        $brandText = $brands->pluck('brand_name')->implode(', ');

        $modelText = $brands->flatMap(function ($brand) {
            return collect($brand['models'])->pluck('model');
        })->implode(', ');

        // Quantity
        $totalQuantity = $rows->sum('quantity');
        $quantityInWords = ucfirst($this->numberToWords((int)$totalQuantity));
        $quantityText = "{$quantityInWords} ({$totalQuantity}) unit" . ($totalQuantity > 1 ? 's' : '');

        // Load template
        $template = new TemplateProcessor(storage_path('app/templates/chainsaw-permit-basic.docx'));

        $template->setValue('permit_number', $application->permit_number);
        $template->setValue('name', $application->authorized_representative ?? $application->applicant_name);
        $template->setValue('address', $application->company_address);
        $template->setValue('quantity', $quantityText);
        $template->setValue('brand', $brandText);
        $template->setValue('model', $modelText);
        $template->setValue('permit_chainsaw_no', $application->permit_chainsaw_no);
        $template->setValue('engine_serial_no', $application->engine_serial_no);
        $template->setValue('supplier_name', $application->supplier_name);
        $template->setValue('supplier_address', $application->supplier_address);
        $template->setValue('purpose', $application->purpose);
        $template->setValue('others', $application->other_details);
        $template->setValue('issued_by', $application->issued_by);
        $template->setValue('issued_date', $application->issued_date ? Carbon::parse($application->issued_date)->format('F d, Y') : null);
        $template->setValue('expiry_date', $application->expiry_date ? Carbon::parse($application->expiry_date)->format('F d, Y') : null);
        $template->setValue('or_number', $application->official_receipt);
        $template->setValue('or_date', $application->date_of_payment ? Carbon::parse($application->date_of_payment)->format('F d, Y') : null);




        $fileName = "chainsaw-permit-" . $application->permit_number . ".docx";

        $tempFile = storage_path('app/temp-' . $fileName);
        $template->saveAs($tempFile);

        /*
            |--------------------------------------------------------------------------
            | Apply Word Protection Without Rewriting the Document
            |--------------------------------------------------------------------------
            */

        $zip = new \ZipArchive();
        $zip->open($tempFile);

        $settingsXml = $zip->getFromName('word/settings.xml');

        $protectionXml = '
        <w:documentProtection 
        w:edit="forms" 
        w:enforcement="1" 
        w:cryptProviderType="rsaAES" 
        w:cryptAlgorithmClass="hash" 
        w:cryptAlgorithmType="typeAny" 
        w:cryptAlgorithmSid="14" 
        w:cryptSpinCount="100000" 
        w:hash="DENR123"
        />';

        $settingsXml = str_replace(
            '</w:settings>',
            $protectionXml . '</w:settings>',
            $settingsXml
        );

        $zip->addFromString('word/settings.xml', $settingsXml);
        $zip->close();

        return response()->download($tempFile)->deleteFileAfterSend(true);
    }
    public function generatePermitDocxMultipleBrandsModels($id)
    {

        $application = DB::table('tbl_application_checklist as ac')
            ->leftJoin('tbl_chainsaw_information as ci', 'ci.application_id', '=', 'ac.id')
            ->leftJoin('tbl_application_payment as ap', 'ap.application_id', '=', 'ac.id')
            ->where('ac.id', $id)
            ->select([
                'ac.permit_no as permit_number',
                'ac.authorized_representative',
                DB::raw("CONCAT_WS(' ', ac.applicant_firstname, ac.applicant_middlename, ac.applicant_lastname) AS applicant_name"),
                'ac.company_address',
                'ac.expiry_date',
                'ci.engine_serial_no',
                'ci.issued_date',
                'ci.permit_validity',
                'ci.issued_by',
                'ci.supplier_name',
                'ci.supplier_address',
                'ci.purpose',
                'ci.other_details',
                'ap.official_receipt',
                'ap.date_of_payment',
            ])
            ->first();

        if (!$application) {
            abort(404, 'Application not found');
        }

        /*
        |--------------------------------------------------------------------------
        | Get Brands + Models
        |--------------------------------------------------------------------------
        */

        $rows = DB::table('chainsaw_brands as cb')
            ->leftJoin('chainsaw_models as cm', 'cm.brand_id', '=', 'cb.id')
            ->where('cb.application_id', $id)
            ->select(
                'cb.brand_name',
                'cm.model',
                'cm.permit_to_sell_no as permit_sell_no',
                'cm.quantity'
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Compute Quantity
        |--------------------------------------------------------------------------
        */

        $totalQuantity = $rows->sum('quantity');
        $quantityInWords = ucfirst($this->numberToWords((int)$totalQuantity));
        $quantityText = "{$quantityInWords} ({$totalQuantity}) unit" . ($totalQuantity > 1 ? 's' : '');

        /*
        |--------------------------------------------------------------------------
        | Brand Summary (Page 1)
        |--------------------------------------------------------------------------
        */

        $brandText = $rows->pluck('brand_name')->unique()->implode(' and ');

        /*
        |--------------------------------------------------------------------------
        | Load Word Template
        |--------------------------------------------------------------------------
        */

        $template = new TemplateProcessor(storage_path('app/templates/chainsaw-permit-MULTIPLE-MODELS-ONE-SUPPLIER.docx'));
        /*
        |--------------------------------------------------------------------------
        | Page 1 Values
        |--------------------------------------------------------------------------
        */

        $template->setValue('permit_number', $application->permit_number);
        $template->setValue('name', $application->authorized_representative ?? $application->applicant_name);
        $template->setValue('address', $application->company_address);

        $template->setValue('quantity', $quantityText);
        $template->setValue('brand', $brandText);
        $template->setValue('model', 'See Annex "A"');

        $template->setValue('engine_serial_no', $application->engine_serial_no);

        $template->setValue('supplier_name', $application->supplier_name);
        $template->setValue('supplier_address', $application->supplier_address);

        $template->setValue('purpose', $application->purpose);
        $template->setValue('others', $application->other_details);
        $template->setValue('or_number', $application->official_receipt);
        $template->setValue('issued_by', $application->issued_by);

        $template->setValue(
            'issued_date',
            $application->issued_date
                ? Carbon::parse($application->issued_date)->format('F d, Y')
                : ''
        );

        $template->setValue(
            'expiry_date',
            $application->permit_validity
                ? Carbon::parse($application->permit_validity)->format('F d, Y')
                : ''
        );

        $template->setValue('or_number', $application->official_receipt);

        $template->setValue(
            'or_date',
            $application->date_of_payment
                ? Carbon::parse($application->date_of_payment)->format('F d, Y')
                : ''
        );

        /*
        |--------------------------------------------------------------------------
        | Build Annex Table
        |--------------------------------------------------------------------------
        */

        $phpWord = new PhpWord();

        $table = new Table([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 50
        ]);

        /*
        |--------------------------------------------------------------------------
        | Table Header
        |--------------------------------------------------------------------------
        */

        $table->addRow();
        $arialFont = [
            'name' => 'Arial',
            'size' => 10
        ];

        $table->addCell(2000)->addText('Brand',$arialFont);
        $table->addCell(2000)->addText('Model',$arialFont);
        $table->addCell(1500)->addText('No of Units',$arialFont);
        $table->addCell(2500)->addText('Permit Sell Chainsaw No',$arialFont);
        $table->addCell(2000)->addText('Date of Issuance',$arialFont);
        $table->addCell(2000)->addText('Date of Expiry',$arialFont);

        /*
        |--------------------------------------------------------------------------
        | Table Data
        |--------------------------------------------------------------------------
        */
  
        foreach ($rows as $row) {

            $table->addRow();

            $table->addCell(2000)->addText($row->brand_name, $arialFont);
            $table->addCell(2000)->addText($row->model, $arialFont);
            $table->addCell(1500)->addText($row->quantity, $arialFont);
            $table->addCell(2500)->addText($row->permit_sell_no, $arialFont);

            $table->addCell(2000)->addText(
                $application->issued_date
                    ? Carbon::parse($application->issued_date)->format('F d, Y')
                    : '',
                $arialFont
            );

            $table->addCell(2000)->addText(
                $application->permit_validity
                    ? Carbon::parse($application->permit_validity)->format('F d, Y')
                    : '',
                $arialFont
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Inject Table Into Template
        |--------------------------------------------------------------------------
        */

        $template->setComplexBlock('annex_table', $table);

        /*
        |--------------------------------------------------------------------------
        | Save Temporary File
        |--------------------------------------------------------------------------
        */
        /*
        |--------------------------------------------------------------------------
        | Save File
        |--------------------------------------------------------------------------
        */

        $fileName = "chainsaw-permit-" . $application->permit_number . ".docx";

        $tempFile = storage_path($fileName);

        $template->saveAs($tempFile);

        /*
            |--------------------------------------------------------------------------
            | Apply Word Protection Without Rewriting the Document
            |--------------------------------------------------------------------------
            */

        $zip = new \ZipArchive();
        $zip->open($tempFile);

        $settingsXml = $zip->getFromName('word/settings.xml');

        $protectionXml = '
        <w:documentProtection 
        w:edit="forms" 
        w:enforcement="1" 
        w:cryptProviderType="rsaAES" 
        w:cryptAlgorithmClass="hash" 
        w:cryptAlgorithmType="typeAny" 
        w:cryptAlgorithmSid="14" 
        w:cryptSpinCount="100000" 
        w:hash="DENR123"
        />';

        $settingsXml = str_replace(
            '</w:settings>',
            $protectionXml . '</w:settings>',
            $settingsXml
        );

        $zip->addFromString('word/settings.xml', $settingsXml);
        $zip->close();

        return response()->download($tempFile)->deleteFileAfterSend(true);
    }

    private function numberToWords(int $number): string
    {
        $dictionary = [
            0 => 'zero',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen',
            20 => 'twenty',
            30 => 'thirty',
            40 => 'forty',
            50 => 'fifty',
            60 => 'sixty',
            70 => 'seventy',
            80 => 'eighty',
            90 => 'ninety'
        ];

        if ($number < 21) {
            return $dictionary[$number];
        }

        if ($number < 100) {
            $tens = ((int) ($number / 10)) * 10;
            $units = $number % 10;
            return $dictionary[$tens] . ($units ? '-' . $dictionary[$units] : '');
        }

        if ($number < 1000) {
            $hundreds = (int) ($number / 100);
            $remainder = $number % 100;
            return $dictionary[$hundreds] . ' hundred' . ($remainder ? ' and ' . $this->numberToWords($remainder) : '');
        }

        // fallback for numbers >= 1000
        return (string) $number;
    }
}
