<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class FileUploaderController extends Controller
{
    public $default_folder = 'uploads/';

   public function storeFiles($id, $base64, $folder)
    {
        $name = Carbon::now()->timestamp . '.png';

        // Decode base64
        list($type, $image) = explode(';', $base64);
        list(, $image) = explode(',', $image);
        $data = base64_decode($image);

        // Folder path
        $folderPath = public_path("uploads/{$folder}/{$id}");
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0755, true); // create folders recursively
        }

        $filePath = $folderPath . '/' . $name;

        $upload = file_put_contents($filePath, $data);

        if ($upload) {
            // Return relative URL path
            return "uploads/{$folder}/{$id}/{$name}";
        }

        return false;
    }

}
