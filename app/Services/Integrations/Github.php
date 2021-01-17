<?php

namespace App\Services\Integrations;

use App\Services\Parsing\StudentInfoParser;
use DateInterval;
use Github\Client;
use GrahamCampbell\GitHub\GitHubManager;
use Illuminate\Support\Facades\Cache;
use UnexpectedValueException;

class Github
{
    static private Client $client;
    protected $github;

    public function __construct(GitHubManager $github)
    {
        $this->github = $github;
    }

    public function getScoreDBClient() : Client
    {
        if (empty($this::$client)) {
            $client = $this->github->connection('app');
            $token  = $this->getInstallationToken();
            $client->authenticate($token, authMethod:
                $client::AUTH_ACCESS_TOKEN);
            $this->github->disconnect('app');
            $this::$client = $client;
        }

        return $this::$client;
    }

    private function getInstallationToken() : string
    {
        $ttl = new DateInterval('PT10M');  // Remember for 1 hour.

        return Cache::remember('github_token', $ttl, function () {
            $installationId = $this->getInstallationId();
            $result         = $this->github->apps()
                ->createInstallationToken($installationId);

            return $result['token'];
        });
    }

    private function getInstallationId() : int
    {
        $installations = $this->github->apps()->findInstallations();

        foreach ($installations as $installation) {
            if ($installation['account']['login'] === 'ScoreDB') {
                return $installation['id'];
            }
        }

        throw new UnexpectedValueException('No installation found.');
    }

    private function getFile(string $path) : string
    {
        $client = $this->getScoreDBClient();

        return $client->repo()->contents()->download(
            'ScoreDB',
            'studentdb-private-store',
            $path,
            'latest'
        );
    }

    public function getManifest() : array
    {
        $ttl = new DateInterval('PT10M');

        return Cache::remember('store_manifest', $ttl, function () {
            return json_decode($this->getFile('meta.json'), true);
        });
    }

    public function getGrades() : array
    {
        $manifest = $this->getManifest();

        return $manifest['grades'];
    }

    public function getGradeStudents(string $grade, string $path) : array
    {
        $file = $this->getFile($path);

        return StudentInfoParser::parse($file, $grade);
    }

    public function checkStudentDBAccess(string $login) : bool
    {
        $ttl = new DateInterval('PT1H');

        return Cache::remember("store_permission_$login", $ttl,
            function () use ($login) {
                $client     = $this->getScoreDBClient();
                $permission = $client->repo()->collaborators()->permission(
                    'ScoreDB',
                    'studentdb-private-store',
                    $login
                )['permission'];

                return $permission !== 'none';
            });
    }
}
