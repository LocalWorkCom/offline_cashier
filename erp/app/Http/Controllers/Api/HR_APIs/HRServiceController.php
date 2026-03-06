<?php


namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Services\HR_Services\HRServicesService;
use Google\Rpc\Context\AttributeContext\Response;
use Illuminate\Http\Request;

class HRServiceController extends Controller
{
    protected $hrServiceService;
    protected $lang;

    public function __construct(HRServicesService $hrServiceService)
    {
        $this->hrServiceService = $hrServiceService;
        $this->lang =  app()->getLocale();
    }

    // Get all HR services
    public function index(Request $request)
    {
        $hrServices = $this->hrServiceService->getAll();
        $data = paginateOrGetAll($hrServices, $request, []);
        return ResponseWithSuccessData($this->lang, $data, 1);
    }

    // Get a single HR service
    public function show($id)
    {
        try {
            $hrService = $this->hrServiceService->getById($id);
            return ResponseWithSuccessData($this->lang, $hrService, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequest($this->lang, 2);
        }
    }


    // Create a new HR service
    public function store(Request $request)
    {
        $employee = auth('employee')->user();
        if (!$employee->hasRole('Branch_Manager')) {
            return RespondWithBadRequest($this->lang, 2);
        }
        $validated = $request->validate([
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
        ]);
        $validated['created_by'] =  authActionSave()['by'];
        $validated['created_by_type'] = authActionSave()['type'];
        $validated['key'] = str_replace(' ', '', ucwords(trim($request->name_en)));
        $hrService = $this->hrServiceService->create($validated);

        return ResponseWithSuccessData($this->lang, $hrService, 1);
    }

    // Update an existing HR service
    public function update(Request $request, $id)
    {
        $employee = auth('employee')->user();
        if (!$employee->hasRole('Branch_Manager')) {
            return RespondWithBadRequest($this->lang, 2);
        }
        $validated = $request->validate([
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
        ]);
        $validated['updated_by'] =  authActionSave()['by'];
        $validated['updated_by_type'] = authActionSave()['type'];
        $validated['key'] = ucfirst(trim($request->name_en));

        try {
            $hrService = $this->hrServiceService->update($id, $validated);
            return ResponseWithSuccessData($this->lang, $hrService, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequest($this->lang, 2);
        }
    }

    // Delete an HR service
    public function destroy($id)
    {
        try {
            $this->hrServiceService->delete($id);
            return RespondWithSuccessRequest($this->lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequest($this->lang, 2);
        }
    }
}
