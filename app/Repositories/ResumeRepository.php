<?php

namespace App\Repositories;

class ResumeRepository implements ResumeRepositoryInterface
{
    public function uploadFile($request)
    {
        // Validate the uploaded file
        $request->validate([
            'resume' => 'required|mimes:pdf|max:2048',
        ]);

        // Get the uploaded file
        $file = $request->file('resume');

        // Create the directory structure based on the current date
        $year = date('Y');  // Current year
        $month = date('m'); // Current month
        $directory = "resumes/{$year}/{$month}";

        // Generate a unique filename for the file
        $fileName = time() . '_' . $file->getClientOriginalName();

        // Store the file in the desired directory
        $filePath = $file->storeAs($directory, $fileName, 'public');

        return response()->json([
            'message' => 'File uploaded successfully!',
            'file_path' => $filePath,
        ]);
    }
}
