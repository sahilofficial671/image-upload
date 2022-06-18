<?php

namespace App\Http\Controllers\Image;

use App\Http\Controllers\Controller;
use App\Services\UploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class ImageUploadController extends Controller
{
    public function index(): View
    {
        return view('image.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'image' => [
                "required",
                "image",
                "mimes:jpg,png,jpeg,gif,svg"
            ],
        ], [
            'image.*' => 'Please select a valid image. Supported formats are: jpg,png,jpeg,gif,svg'
        ]);

        /** @var UploadedFile */
        $file = $validated['image'];

        $file->store('public');

        $service = (new UploadService([
            'asset' => $file
        ]))->handle();

        if($service->isUploaded()){
            return back()->with('success', 'File Uploaded.');
        }

        return back()->with('error', 'Something went wrong!');
    }
}
