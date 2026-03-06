<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\FAQFormRequest;
use App\Models\FAQ;
use Illuminate\Http\Request;

class FAQController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $faqs = FAQ::get();
        return view('dashboard.faq.list', compact('faqs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.faq.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FAQFormRequest $request)
    {
//        dd($request->all());
        $lang = app()->getLocale();
        $data = $request->validated();
        $faq = new FAQ();
        $faq->name_ar = $data['name_ar'];
        $faq->name_en = $data['name_en'];
        $faq->question_ar = $data['question_ar'];
        $faq->question_en = $data['question_en'];
        $faq->answer_ar = $data['answer_ar'];
        $faq->answer_en = $data['answer_en'];
        $faq->active = $data['active'];
        $faq->created_by = auth('admin')->id() ?? 1 ;
        $faq->save();
        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message= $responseData['message'];
        return redirect('dashboard/faqs')->with('message',$message);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $faq = FAQ::findOrFail($id);
        return view('dashboard.faq.show', compact('faq'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $faq = FAQ::findOrFail($id);
        return view('dashboard.faq.edit', compact('faq'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FAQFormRequest $request, string $id)
    {
        $lang = app()->getLocale();
        $data = $request->validated();
        $faq = FAQ::findOrFail($id);
        $faq->name_ar = $data['name_ar'];
        $faq->name_en = $data['name_en'];
        $faq->question_ar = $data['question_ar'];
        $faq->question_en = $data['question_en'];
        $faq->answer_ar = $data['answer_ar'];
        $faq->answer_en = $data['answer_en'];
        $faq->active = $data['active'];
        $faq->modified_by = auth('admin')->id() ?? 1;
        $faq->save();
        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/faqs')->with('message', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $lang = app()->getLocale();
        $faq = FAQ::findOrFail($id);
        $faq->deleted_by = auth('admin')->id() ?? 1;
        $faq->delete();
        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/faqs')->with('message', $message);
    }
}
