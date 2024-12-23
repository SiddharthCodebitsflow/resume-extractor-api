<?php

namespace App\Repositories;

interface ResumeRepositoryInterface
{
    public function uploadFile($data);
    public function getResumeData();
}
