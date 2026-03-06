<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\KitchenServices\AddonCategoryService;
use Illuminate\Http\Request;

class testController extends Controller
{
    protected $addonCategoryService;

    public function __construct(AddonCategoryService $addonCategoryService)
    {
        $this->addonCategoryService = $addonCategoryService;
    }

    public function testController()
    {
        $output = null;
        $return_var = null;
        exec('whoami', $output, $return_var);
        echo "exec output: ";
        print_r($output);
        echo "Return code: $return_var\n";

        // Test shell_exec
        $output = shell_exec('whoami');
        echo "shell_exec output: $output\n";

        // Test proc_open
        $descriptorspec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];
        $process = proc_open('whoami', $descriptorspec, $pipes);
        if (is_resource($process)) {
            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $return_value = proc_close($process);
            echo "proc_open output: $output\n";
            echo "Return code: $return_value\n";
        }
    }

    public function show($id)
    {
        $addonCategory = $this->addonCategoryService->show($id);
        return view('dashboard.addon_categories.show', compact('addonCategory'));
    }

    public function create()
    {
        return view('dashboard.addon_categories.create');
    }

    public function store(Request $request)
    {
        $this->addonCategoryService->store($request->all());
        return redirect()->route('dashboard.addon_categories.index')->with('message', __('addon_categories.addon_category_created'));
    }

    public function edit($id)
    {
        $addonCategory = $this->addonCategoryService->show($id);
        return view('dashboard.addon_categories.edit', compact('addonCategory'));
    }

    // public function update(Request $request, $id)
    // {
    //     $this->addonCategoryService->update($id, $request->all());
    //     return redirect()->route('dashboard.addon_categories.index')->with('success', 'Addon category updated successfully.');
    // }



    public function update(Request $request, $id)
    {
        $response = $this->addonCategoryService->update($id, $request->all());
        $responseData = $response->getOriginal();

        return redirect('dashboard/addon-categories')->with('message', __('addon_categories.addon_category_updated'));
    }

    public function destroy(Request $request, $id)
    {
        $lang = app()->getLocale();
        $response = $this->addonCategoryService->delete($id, $lang);
        $responseData = $response->original ?? [];
        $message = $responseData['message'] ?? __('recipes.An error occurred');

        if (isset($responseData['status']) && !$responseData['status']) {
            $validationErrors = $responseData['data'] ?? $message;
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }
        return redirect('dashboard/addon-categories')->with('message', $message);
    }



    public function restore($id)
    {
        $this->addonCategoryService->restore($id);
        return redirect()->route('dashboard.addon_categories.index')->with('success', 'Addon category restored successfully.');
    }
}
