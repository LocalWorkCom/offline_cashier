<?php

namespace App\Http\Controllers\Dashboard;

use App\Exports\ApplicationExport;
use App\Exports\ApplicationQuestionsExport;
use App\Exports\QuestionsExport;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Position;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Excel as ExcelReader;
use Maatwebsite\Excel\Facades\Excel;

class ApplicationController  extends Controller
{
    public function create($id)
    {
        $position = Position::with('applications')->findOrFail($id);
        return view('dashboard.position.applications.createApplication', compact('position'));
    }

    public function store(Request $request)
    {
        // Validate
        $validated = $request->validate([
            'job_title' => 'required|exists:positions,id', // from hidden input
            'questions' => 'required|array',
            'questions.*.content' => 'required|string|max:255',
            'questions.*.type' => 'required|string|in:text,textarea,radio,checkbox,select,file',
            'questions.*.options' => 'nullable|array',
            'questions.*.required' => 'nullable|boolean',
        ]);

        // Get questions
        $questions = $request->input('questions');

        // Prepare data for Excel export
        $exportData = [];
        foreach ($questions as $q) {
            $exportData[] = [
                'question' => $q['content'],
                'type' => $q['type'],
                'options' => isset($q['options']) ? implode(',', $q['options']) : '',
                'IsRequired' => isset($q['required']) ? 1 : 0,
            ];
        }

        // Ensure position exists
        $position = Position::findOrFail($request->job_title);

        // File settings
        $fileName = 'application_' . $request->job_title . '_' . time() . '.xlsx';
        $publicPath = public_path('applications'); // Path in the public folder

        // Ensure the directory exists in the public folder
        if (!file_exists($publicPath)) {
            mkdir($publicPath, 0777, true); // Create the directory if it doesn't exist
        }

        // Generate the Excel file and save directly to public path
        $export = new QuestionsExport($exportData);
        $filePath = $publicPath . '/' . $fileName;

        // Use Excel::download and manually save the file
        Excel::store($export, $fileName, 'local'); // Temporarily store

        // Move from storage to public folder
        $tempPath = storage_path('app/' . $fileName);
        rename($tempPath, $filePath);

        // OR Alternative approach using Excel facade's save method:
        // Excel::store($export, $filePath);

        // Generate full public URL for the uploaded file
        $uploadedFilePath = asset('applications/' . $fileName);

        // Encrypt position ID for secure link
        $hashedId = Crypt::encryptString($position->id);

        // Save path and link in the database
        $position->application_path = $uploadedFilePath;
        $position->application_link = route('position.applications.fill', ['id' => $hashedId]);
        $position->save();

        return redirect()->route('positions.index')->with('success', 'Application saved successfully.');
    }

    public function UploadFile($path, $fileName)
    {
        $destinationPath = public_path($path);

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $sourceFile = storage_path('app/applications/' . $fileName); // source path
        $targetFile = $destinationPath . '/' . $fileName; // destination path

        if (file_exists($sourceFile)) {
            File::move($sourceFile, $targetFile);
        } else {
            throw new \Exception('File not found: ' . $sourceFile);
        }

        return asset($path . '/' . $fileName);
    }
    public function fetchQuestions($positionApp)
    {
        $position = Position::findOrFail($positionApp);
        $fileUrlPath = parse_url($position->application_path, PHP_URL_PATH);
        // Make sure file URL path is not empty
        if (!$fileUrlPath) {
            throw new \Exception('Application file path is empty.');
        }

        // Then build file path
        $filePath = public_path(ltrim($fileUrlPath, '/'));


        $questionsCollection = Excel::toCollection(new class implements \Maatwebsite\Excel\Concerns\ToCollection {
            public $collection;

            public function collection(\Illuminate\Support\Collection $collection)
            {
                $this->collection = $collection;
            }
        }, $filePath, null, ExcelReader::XLSX); // <- force XLSX type

        $sheet = $questionsCollection->first();

        // Skip header row
        $sheet = $sheet->skip(1);

        return $sheet->map(function ($row) {
            $row = $row->values();
            return [
                'question' => $row[0] ?? null,
                'type' => $row[1] ?? 'text',
                'options' => $row[2] ?? '',
                'required' => isset($row[3]) && trim($row[3]) == '1' ? true : false,
            ];
        })->toArray();
    }

    public function edit($position)
    {
        $questions = $this->fetchQuestions($position);
        $application = Position::findOrFail($position);

        return view('dashboard.position.applications.edit', compact('application', 'questions'));
    }
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'job_title' => 'required|exists:positions,id',
            'questions' => 'required|array',
            'questions.*.content' => 'required|string|max:255',
            'questions.*.type' => 'required|string|in:text,textarea,radio,checkbox,select,file',
            'questions.*.options' => 'nullable|array',
            'questions.*.required' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Find the position
        $position = Position::findOrFail($request->job_title);

        // Prepare data for Excel export
        $exportData = [];
        foreach ($request->questions as $index => $q) {
            $exportData[] = [
                'question' => $q['content'],
                'type' => $q['type'],
                'options' => isset($q['options']) ? implode(',', $q['options']) : '',
                'IsRequired' => isset($q['required']) ? 1 : 0,
            ];
        }

        // Generate filename with timestamp (same pattern as store function)
        $fileName = 'application_' . $request->job_title . '_' . time() . '.xlsx';
        $publicPath = public_path('applications'); // Same path as store function

        // Ensure the directory exists
        if (!file_exists($publicPath)) {
            mkdir($publicPath, 0777, true);
        }

        // Delete old file if it exists
        if ($position->application_path) {
            $oldFile = basename($position->application_path);
            $oldFilePath = $publicPath . '/' . $oldFile;
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
        }

        // Generate the Excel file and save directly to public path (same as store function)
        $export = new QuestionsExport($exportData);
        $filePath = $publicPath . '/' . $fileName;

        // Temporarily store in local disk
        Excel::store($export, $fileName, 'local');

        // Move from storage to public folder
        $tempPath = storage_path('app/' . $fileName);
        rename($tempPath, $filePath);

        // Generate full public URL for the uploaded file
        $uploadedFilePath = asset('applications/' . $fileName);

        // Encrypt position ID for secure link (same as store function)
        $hashedId = Crypt::encryptString($position->id);

        // Update position record
        $position->application_path = $uploadedFilePath;
        $position->application_link = route('position.applications.fill', ['id' => $hashedId]);
        $position->save();

        return redirect()->route('positions.index')->with('success', 'Application questions updated successfully.');
    }
    public function show($id)
    {
        // try {
        $positionId = Crypt::decryptString($id);
        $positions = Position::findOrFail($positionId);
        $questions = $this->fetchQuestions($positionId);
        // dd($questions);
        return view('dashboard.position.applications.viewEmployee', compact('questions', 'positions'));
        // } catch (DecryptException $e) {
        //     abort(404, 'Invalid application link');
        // }
    }

    public function storeApplication(Request $request)
    {
        $request->validate([
            'position_id' => 'required|exists:positions,id',
            'answers' => 'required|array',
        ]);

        $processedAnswers = [];

        foreach ($request->input('answers') as $index => $answer) {
            $type = $answer['type'];
            $question = $answer['question'];

            if ($type === 'file' && $request->hasFile("answers.$index.value")) {
                // Store files in public/applications/files
                $publicPath = public_path('applications/files');
                if (!file_exists($publicPath)) {
                    mkdir($publicPath, 0777, true);
                }

                $file = $request->file("answers.$index.value");
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move($publicPath, $fileName);

                $processedAnswers[] = [
                    'question' => $question,
                    'value' => asset('applications/files/' . $fileName),
                ];
            } else {
                $value = $answer['value'] ?? null;

                if (is_array($value)) {
                    $value = array_filter($value); // remove empty checkboxes
                }

                $processedAnswers[] = [
                    'question' => $question,
                    'value' => $value ?? 'No Answer',
                ];
            }
        }

        // Generate Excel file in public/applications/answers
        $publicPath = public_path('applications/answers');
        if (!file_exists($publicPath)) {
            mkdir($publicPath, 0777, true);
        }

        $fileName = 'application_' . time() . '.xlsx';
        $filePath = 'applications/answers/' . $fileName;
        $fullPath = $publicPath . '/' . $fileName;

        // Generate and save Excel file
        $export = new ApplicationExport($processedAnswers);

        // Temporary storage
        Excel::store($export, $fileName, 'local');

        // Move to public directory
        $tempPath = storage_path('app/' . $fileName);
        rename($tempPath, $fullPath);

        // Save application
        Application::create([
            'position_id' => (int) $request->position_id,
            'file_path' => asset('applications/answers/' . $fileName), // Full public URL
            'storage_path' => $filePath, // Relative path
        ]);

        return back()->with('success', 'Application submitted successfully.');
    }

    public function viewAnswers($id, Request $request)
{
    $applications = Application::with('position')
        ->where('position_id', $id)
        ->when($request->status && $request->status !== 'all', function ($query) use ($request) {
            return $query->where('status', $request->status);
        })
        ->latest()
        ->get();

    return view('dashboard.position.applications.answers.list', [
        'applications' => $applications,
        'id' => $id,
    ]);
}

        public function viewAnswer($id)
    {
        $application = Application::with('position')->findOrFail($id);

        // Handle both full URL and relative path cases
        $filePath = str_contains($application->file_path, 'http')
            ? public_path(parse_url($application->file_path, PHP_URL_PATH))
            : public_path($application->file_path);

        if (!file_exists($filePath)) {
            throw new \Exception("Application answer file not found at: {$filePath}");
        }

        // Fetch the answers from the Excel file
        $answersCollection = Excel::toCollection(new class implements \Maatwebsite\Excel\Concerns\ToCollection {
            public $collection;

            public function collection(\Illuminate\Support\Collection $collection)
            {
                $this->collection = $collection;
            }
        }, $filePath, null, ExcelReader::XLSX);

        // Process the answers
        $answers = [];
        $sheet = $answersCollection->first();

        // Skip header row if exists
        $sheet = $sheet->skip(1);

        foreach ($sheet as $row) {
            $row = $row->values();
            $answers[] = [
                'question' => $row[0] ?? 'No question',
                'answer' => $this->formatAnswer($row[1] ?? 'No answer'),
            ];
        }

        return view('dashboard.position.applications.answers.viewAnswer', [
            'answers' => $answers,
            'application' => $application
        ]);
    }

    protected function formatAnswer($answer)
    {
        if (filter_var($answer, FILTER_VALIDATE_URL)) {
            // If answer is a URL (likely a file), return a link
            return '<a href="' . e($answer) . '" target="_blank">View uploaded file</a>';
        }

        if (is_array($answer)) {
            return implode(', ', array_filter($answer));
        }

        return e($answer);
    }
    public function updateStatus(Request $request, $id)
    {
        $validStatuses = [
            'new_request',
            'accepted',
            'pending_interview',
            'rejected',
            'incomplete_information',
            'on_hold'
        ];

        $request->validate([
            'status' => 'required|in:' . implode(',', $validStatuses)
        ]);

        $application = Application::findOrFail($id);
        $application->status = $request->status;
        $application->save();

        if ($request->status === 'accepted') {
            return redirect()->route('employee.create')
                ->with([
                    'success' => 'Status updated successfully. Please add this accepted employee to our system.',
                    'application_id' => $application->id // Optional: pass the application ID
                ]);
        }

        return back()->with('success', 'Status updated successfully');
    }
}
