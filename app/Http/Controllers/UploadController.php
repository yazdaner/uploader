<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        $sliceFile = $request->file('sliceFile');
        $path = Storage::disk('public')->path('chunks/' . $sliceFile->getClientOriginalName());
        $name = basename($path, '.part');
        $types = ["mp4", "mov", "wvm", "flv", "avi", "mkv"];
        $ex = explode('.', $name);
        if (sizeof($ex) > 0 && array_search($ex[sizeof($ex) - 1], $types) == 0) {
            $part = $request->get('part');
            $append = true;
            if ($part > 1) {
                $oldFileSize = filesize($path);
                if ($oldFileSize > $part * 500000) {
                    $append = false;
                }
            }
            if ($append) {
                File::append($path, $sliceFile->get());
            }
            if ($request->get('latest') == 'true') {
                File::move($path, "./videos/{$name}");
                return ['status' => 'ok'];
            }
        } else {
            return ['status' => 'error'];
        }
    }
}
