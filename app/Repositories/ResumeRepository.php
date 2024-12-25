<?php

namespace App\Repositories;

use App\Models\User;
use Exception;
use Smalot\PdfParser\Parser;

class ResumeRepository implements ResumeRepositoryInterface
{
    public function getResumeData()
    {
        try {
            $data = User::get();
            return response()->json(['error' => false, 'data' => $data, 'message' => 'fetched successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 400);
        }
    }

    public function uploadFile($request)
    {
        try {
            $request->validate(['resume' => 'required|mimes:pdf|max:2048',]);

            $file = $request->file('resume');
            $extension = $file->getClientOriginalExtension();
            $year = date('Y');
            $month = date('m');
            $directory = "resumes/{$year}/{$month}";
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs($directory, $fileName, 'public');

            $parserText = $this->parseResume($file->getRealPath(), $extension);
            // dd($parserText);
            $arr = [];
            if ($parserText) {
                $data = json_decode($this->convertToJson($parserText));
                $arr = [
                    'name' => $data->personal_info->name ?? null,
                    'email' => $data->personal_info->email ?? null,
                    'phone' => $data->personal_info->phone ?? null,
                    'resume_path' => $filePath,
                    'additional_details' => $data,
                    'parsed_resume' => $parserText,
                    'status' => 1,
                ];
            } else {
                $arr = ['resume_path' => $filePath,];
            }
            User::create($arr);
            return response()->json(['message' => 'File uploaded successfully!', 'file_path' => $filePath,], 200);
        } catch (Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 422);
        }
    }

    private function parseResume($filePath, $extension)
    {
        if (in_array($extension, ['pdf'])) {
            return $this->parsePdf($filePath);
        } else {
            throw new \Exception("Unsupported file format: $extension");
        }
    }

    private function parsePdf($filePath)
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);
        return  $pdf->getText();
    }

    private function convertToJson($text)
    {
        $data = [];

        $data['personal_info'] = [
            'name' => $this->extractName($text),
            'email' => $this->extractEmail($text),
            'phone' => $this->extractPhone($text)
        ];

        if (preg_match('/TECHNICAL SKILLS\s*(.*?)(PROJECTS|ACHIEVEMENTS)/si', $text, $matches)) {
            $data['skills'] = $this->extractSkills($matches[1]);
        }

        if (preg_match('/PROJECTS\s*(.*?)(ACHIEVEMENTS|CERTIFICATIONS)/si', $text, $matches)) {
            $data['projects'] = $this->extractProjects($matches[1]);
        }

        if (preg_match('/ACHIEVEMENTS\s*(.*?)(CERTIFICATIONS|EDUCATION)/si', $text, $matches)) {
            $data['achievements'] = $this->extractAchievements($matches[1]);
        }

        if (preg_match('/CERTIFICATIONS\s*(.*?)(EDUCATION)/si', $text, $matches)) {
            $data['certifications'] = $this->extractCertifications($matches[1]);
        }

        if (preg_match('/EDUCATION\s*(.*)/si', $text, $matches)) {
            $data['education'] = $matches[1];
        }

        return json_encode($data);
    }

    private function extractName($text)
    {
        if (preg_match('/^\s*([A-Za-z\s]+)\s*\n/m', $text, $matches)) {
            return trim($matches[1]);
        }
        return '';
    }

    private function extractEmail($text)
    {
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $matches)) {
            return $matches[0];
        }
        return '';
    }

    private function extractPhone($text)
    {
        $patternPriority = '/\+[\d]{1,3}[-.\s]?\(?\d{1,4}\)?[-.\s]?\d{1,4}[-.\s]?\d{1,4}[-.\s]?\d{1,4}/';
        $patternFallback = '/(\d{10,12})/';

        if (preg_match($patternPriority, $text, $matches)) {
            $phoneNumber = preg_replace('/[^0-9+]/', '', $matches[0]);
            return $phoneNumber;
        }

        if (preg_match($patternFallback, $text, $matches)) {
            return $matches[0];
        }

        return '';
    }


    private function extractSkills($text)
    {
        return array_map('trim', explode(',', $text));
    }

    private function extractProjects($text)
    {
        $projects = [];
        preg_match_all('/([\w\s-]+)\s*\|/', $text, $matches);
        foreach ($matches[1] as $project) {
            $projects[] = trim($project);
        }
        return $projects;
    }

    private function extractAchievements($text)
    {
        return array_map('trim', explode("\n", $text));
    }

    private function extractCertifications($text)
    {
        return array_map('trim', explode("\n", $text));
    }
}
