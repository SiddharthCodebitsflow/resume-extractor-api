<?php

namespace App\Http\Controllers;

use App\Repositories\ResumeRepositoryInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    private $resumeRepository;

    public function __construct(ResumeRepositoryInterface $resumeRepository)
    {
        $this->resumeRepository = $resumeRepository;
    }

    public function upload(Request $request)
    {
        $this->resumeRepository->uploadFile($request);
    }
}
