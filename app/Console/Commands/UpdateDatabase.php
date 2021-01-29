<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Services\Integrations\Github;
use Illuminate\Console\Command;

class UpdateDatabase extends Command
{
    protected $signature = 'db:update';

    protected $description = 'Update database with the StudentDB store.';

    protected Github $github;

    public function __construct()
    {
        parent::__construct();
        $this->github = resolve(Github::class);
    }

    public function handle() : int
    {
        $this->info('Database update in progress...');

        Student::truncate();

        $all_students = [];

        foreach ($this->github->getGrades() as $grade => $path) {
            $this->info("Downloading {$grade} student data...");

            $students     = $this->github->getGradeStudents($grade, $path);
            $all_students = array_merge($all_students, $students);
        }

        $this->info('Uploading all students to database...');
        $bar = $this->output->createProgressBar();
        $bar->start(count($all_students));

        foreach ($all_students as $student) {
            Student::forceCreate($student);
            $bar->advance();
        }

        $bar->finish();

        return 0;
    }
}
