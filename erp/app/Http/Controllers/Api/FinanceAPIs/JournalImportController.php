<?php

namespace App\Http\Controllers\Api\FinanceAPIs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounts\ImportExportRequest;
use App\Models\Branch;
use App\Models\CompanyProfileSetting;
use App\Models\Facility;
// use App\Services\Journals\JournalServiceInterface;
use Illuminate\Http\JsonResponse;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;


class JournalImportController extends Controller
{
    protected $accountService;

    // public function __construct(JournalServiceInterface $accountService)
    // {
    //     $this->accountService = $accountService;
    // }

    public function template(): StreamedResponse
    {

        $spreadsheet = new Spreadsheet();

        // Main Sheet
        $mainSheet = $spreadsheet->getActiveSheet();
        $this->accountsDataSheet($mainSheet);

        $branches = Branch::active()->get(['id', 'name_en', 'name_ar']);
        $facilities = Facility::active()->get(['id', 'name_en', 'name_ar']);
        $companies = CompanyProfileSetting::get(['id', 'name_en', 'name_ar']);

        // Data
        $branchOptions = $branches
            ->map(fn($b) => [$b->id . ' - ' . $b->name])
            ->values()
            ->toArray();

        $facilitiesOptions = $facilities
            ->map(fn($b) => [$b->id . ' - ' . $b->name])
            ->values()
            ->toArray();

        $companiesOptions = $companies
            ->map(fn($b) => [$b->id . ' - ' . $b->name])
            ->values()
            ->toArray();


        $this->referenceDataSheet($spreadsheet, $mainSheet, $facilitiesOptions, $companiesOptions, $branchOptions);
        $this->instructionsSheet($spreadsheet, $facilitiesOptions, $companiesOptions, $branchOptions);

        $fileName = 'journals_import_template_' . now()->format('YmdHis') . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName);
    }

    private function accountsDataSheet($mainSheet)
    {
        $mainSheet->setTitle('Journals Data');

        // Merge headers with line break
        $mainSheet->setCellValue('A1', "البيانات الأساسية\nيمكنك تكرار البيانات الأساسية للحسابات حسب المنشآت");
        $mainSheet->mergeCells('A1:I1');
        $mainSheet->setCellValue('J1', 'المنشآت');
        $mainSheet->mergeCells('J1:N1');

        // Style header row
        $headerStyle = $mainSheet->getStyle('A1:N1');
        $headerStyle->getFont()
            ->setBold(true)
            ->setSize(12)
            ->setName('Arial');
        $headerStyle->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true); // Enable text wrapping for the multi-line text
        $mainSheet->getRowDimension(1)->setRowHeight(40); // Increased height to accommodate two lines

        // Set background colors
        $mainSheet->getStyle('A1:I1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('0070C0');

        $mainSheet->getStyle('J1:N1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('4CA6FF');

        // Set white font color
        $mainSheet->getStyle('A1:N1')->getFont()->getColor()->setRGB('FFFFFF');

        // Column headers (row 2) - Translated to Arabic
        $headers = [
            'A2' => 'الاسم (عربي)',
            'B2' => 'الاسم (إنجليزي)',
            'C2' => 'الحساب الرئيسي',
            'D2' => 'الوصف (إنجليزي)',
            'E2' => 'الوصف (عربي)',
            'F2' => 'حساب دفعات؟',
            'G2' => 'مفعل؟',
            'H2' => 'الرصيد الافتتاحي',
            'I2' => 'الرصيد',
            'J2' => 'المنشأة',
            'K2' => 'اسم الشركة',
            'L2' => 'اسم الفرع',
            'M2' => 'مفعل؟',
            'N2' => 'افتراضي؟',
        ];

        foreach ($headers as $cell => $label) {
            $mainSheet->setCellValue($cell, $label);
        }

        // Style column headers
        $columnHeaderStyle = $mainSheet->getStyle('A2:N2');
        $columnHeaderStyle->getFont()
            ->setBold(true)
            ->setSize(11);
        $columnHeaderStyle->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $mainSheet->getRowDimension(2)->setRowHeight(25);

        // Auto-size columns A-N
        foreach (range('A', 'N') as $col) {
            $mainSheet->getColumnDimension($col)->setAutoSize(true);
        }

        $mainSheet->freezePane('A3');
    }

    private function referenceDataSheet($spreadsheet, $mainSheet, $facilitiesOptions, $companiesOptions, $branchOptions)
    {
        // ReferenceData Sheet
        $validationSheet = new Worksheet($spreadsheet, 'ReferenceData');
        $spreadsheet->addSheet($validationSheet, 1);

        // Headers
        $validationSheet->setCellValue('A1', 'المنشآت');
        $validationSheet->setCellValue('B1', 'الشركات');
        $validationSheet->setCellValue('C1', 'الفروع');

        $validationSheet->fromArray($facilitiesOptions, null, 'A2');
        $validationSheet->fromArray($companiesOptions, null, 'B2');
        $validationSheet->fromArray($branchOptions, null, 'C2');

        // Set active sheet back to main
        $spreadsheet->setActiveSheetIndex(0);

        // Apply dropdowns
        $rowLimit = 100;
        $facilitiesRange = "'ReferenceData'!\$A\$2:\$A\$" . (count($facilitiesOptions) + 1);
        $companiesRange = "'ReferenceData'!\$B\$2:\$B\$" . (count($companiesOptions) + 1);
        $branchesRange = "'ReferenceData'!\$C\$2:\$C\$" . (count($branchOptions) + 1);

        for ($row = 3; $row <= $rowLimit; $row++) {
            $mainSheet->getCell("J$row")->setDataValidation($this->dropdownValidation($facilitiesRange));
            $mainSheet->getCell("K$row")->setDataValidation($this->dropdownValidation($companiesRange));
            $mainSheet->getCell("L$row")->setDataValidation($this->dropdownValidation($branchesRange));
            $mainSheet->getCell("F$row")->setDataValidation($this->dropdownValidation('"Yes,No"'));
            $mainSheet->getCell("G$row")->setDataValidation($this->dropdownValidation('"Yes,No"'));
            $mainSheet->getCell("H$row")->setDataValidation($this->dropdownValidation('"Yes,No"'));
            $mainSheet->getCell("M$row")->setDataValidation($this->dropdownValidation('"Yes,No"'));
            $mainSheet->getCell("N$row")->setDataValidation($this->dropdownValidation('"Yes,No"'));
        }

        $validationSheet->getStyle('A1:M1')->getFont()->setBold(true);
        $validationSheet->getColumnDimension('A')->setAutoSize(true);
        $validationSheet->getColumnDimension('B')->setAutoSize(true);
        $validationSheet->getColumnDimension('C')->setAutoSize(true);
        $validationSheet->getColumnDimension('D')->setAutoSize(true);
        $validationSheet->freezePane('A2');
    }

    private function instructionsSheet($spreadsheet, $facilitiesOptions, $companiesOptions, $branchOptions)
    {
        // Instructions Sheet
        $instructionSheet = new Worksheet($spreadsheet, 'تعليمات');
        $spreadsheet->addSheet($instructionSheet);

        $instructions = [
            ['تعليمات استيراد شجرة الحسابات'],
            [''],
            ['1. الحقول المطلوبة:'],
            ['   - الاسم (عربي)، الاسم (إنجليزي)، الحساب الرئيسي، الوصف (إنجليزي)، الوصف (عربي)، حساب دفعات؟، مفعل؟، الرصيد الافتتاحي، الرصيد، المنشأة، اسم الشركة، اسم الفرع، مفعل؟، افتراضي؟'],
            [''],
            ['2. المنشآت:']
        ];

        foreach ($facilitiesOptions as $facilitiesOption) {
            array_push($instructions, $facilitiesOption);
        }

        array_push($instructions,
            [''],
            ['3. الشركات:']
        );
        foreach ($companiesOptions as $companiesOption) {
            array_push($instructions, $companiesOption);
        }
        array_push($instructions,
            [''],
            ['4. الفروع:']
        );
        foreach ($branchOptions as $branchOption) {
            array_push($instructions, $branchOption);
        }

        array_push($instructions,
            [''],
            ['6. ملاحظات:'],
            ['   - يجب أن يشير الحساب الرئيسي إلى حسابات موجودة'],
            ['   - اترك الحقول الاختيارية فارغة للقيم الافتراضية'],
        );

        $instructionSheet->fromArray($instructions, null, 'A1');
        $instructionSheet->getStyle('A1')->getFont()->setBold(true);
        $instructionSheet->getColumnDimension('A')->setAutoSize(true);
        $instructionSheet->freezePane('A2');
    }

    private function dropdownValidation(string $formula): DataValidation
    {
        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setFormula1($formula);
        return $validation;
    }

    public function import(ImportExportRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = $this->accountService->importAccounts($validated);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'imported_count' => $result['imported_count'],
            'error_count' => $result['error_count'],
            'errors' => $result['errors'],
        ], $result['success'] ? 200 : ($result['error_count'] > 0 ? 422 : 500));
    }

    public function export()
    {
        $filters = request('filters', []);
        $export_type = request('export_type');
        return match ($export_type) {
            'excel' => $this->accountService->exportAccounts($filters, $export_type)->download(),
            'pdf' => $this->accountService->exportAccounts($filters, $export_type)->Output($this->generateFilename(), 'D'),
            default => null,
        };
    }

    private function generateFilename(): string
    {
        return "accounts_export_" . date('Ymd_His') . ".pdf";
    }
}
