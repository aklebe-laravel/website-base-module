<?php

namespace Modules\WebsiteBase\app\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use Modules\SystemBase\app\Services\Base\BaseService;
use Modules\SystemBase\app\Services\ModuleService;
use Modules\SystemBase\app\Services\ThemeService;
use Modules\WebsiteBase\app\Models\Changelog;
use Nwidart\Modules\Module;

class CreateChangeLogService extends BaseService
{
    /**
     * @return array
     */
    private function getNewCommitData(): array
    {
        return [
            'path'          => '',
            'hash'          => '',
            'author'        => '',
            'date'          => '',
            'date_raw'      => '',
            'messages'      => [],
            'messages_html' => '',
            'index'         => 0,
        ];
    }

    /**
     * @param  array  $data
     *
     * @return Changelog
     */
    public function foundCommit(array $data): Changelog
    {
        $this->parseCommitData($commit);
        if ($changeLogModel = Changelog::with([])
            ->where('path', $data['path'])
            ->where('hash', $data['hash'])
            ->first()) {
        } else {
            $changeLogModel = Changelog::create([
                'hash'              => $data['hash'],
                'path'              => $data['path'],
                'author'            => $data['author'],
                'commit_created_at' => $data['date_raw'],
                'messages'          => implode("\n", $data['messages']),
            ]);
        }

        return $changeLogModel;
    }

    /**
     * $limit beeinflusst den Cache-Key
     *
     * @param  string  $path
     *
     * @return void
     */
    public function updateGitHistory(string $path = ''): void
    {
        // just to remember: array<array{index: int, hash: string, path: string, author: string, date: string, date_raw: string, messages: array}>

        $cacheKey = 'git_history_'.$path;
        Cache::rememberForever($cacheKey, function () use ($path) {

            $fullPath = base_path($path);
            if (is_dir($fullPath)) {

                if (!is_file($fullPath.'/.git/config')) {
                    $this->debug('No git found for path:', [$fullPath, __METHOD__]);
                    return $fullPath;
                }

                $result = Process::forever()->path($fullPath)->run('git log');
                $output = preg_split("/\r\n|\n|\r/", $result->output());;

                $index = 0;
                $commit = $this->getNewCommitData();
                foreach ($output as $line) {
                    if (str_starts_with($line, 'commit')) {
                        $index++;
                        if ($commit['messages']) {
                            $this->foundCommit($commit);
                            $commit = $this->getNewCommitData();
                        }
                        $commit['path'] = $path;
                        $commit['hash'] = substr($line, strlen('commit'));
                    } elseif (str_starts_with($line, 'Author')) {
                        $commit['author'] = substr($line, strlen('Author:'));
                    } elseif (str_starts_with($line, 'Date')) {
                        $gitDate = substr($line, strlen('Date:'));
                        $date = trim($gitDate);
                        $date = Carbon::createFromFormat('D M d H:i:s Y O', $date);
                        $commit['date_raw'] = $date->format('Y-m-d H:i:s');
                        $commit['date'] = $date->format('d.m.Y H:i:s');
                    } else {
                        if (empty($commit['messages'])) {
                            $commit['messages'] = [];
                        }
                        $commit['messages'][] = trim($line);
                        $commit['index'] = $index;
                    }
                }

                if ($commit['messages']) {
                    $this->foundCommit($commit);
                }
            }

            return $fullPath;
        });
    }

    /**
     * @param  array  $paths  empty means: app, all modules and all themes
     * @return void
     */
    public function updateGitHistories(array $paths = []): void
    {

        $cacheKey = 'git_all_histories_'.implode('_', $paths);
        Cache::rememberForever($cacheKey, function () use ($paths) {

            // Automatically fill paths?
            if (!$paths) {

                $paths = [
                    '', // App itself
                ];

                /** @var ModuleService $moduleService */
                $moduleService = app(ModuleService::class);
                $moduleService->runOrderedEnabledModules(function (?Module $module) use (&$paths) {
                    $paths[] = 'Modules/'.$module->getStudlyName();
                    return true;
                });

                /** @var ThemeService $themeService */
                $themeService = app(ThemeService::class);
                foreach ($themeService->getAllThemes() as $theme) {
                    $paths[] = 'Themes/'.data_get($theme, 'name');
                }

            }

            foreach ($paths as $path) {
                $this->updateGitHistory($path);
            }

        });
    }

    /**
     * @param $commit
     * @return void
     */
    private function parseCommitData(&$commit): void
    {
        if (!$commit) {
            return;
        }

        foreach ($commit['messages'] as $msg) {

            if (preg_match('#^([-*]+)(.*?)$#', $msg, $out)) {
                $newIndent = strlen($out[1]);
                $msg = $out[2];
            }


        }

    }

    /**
     * @return string|false
     */
    public function getActualBranchName(): string|false
    {
        if ($stringFromFile = file(base_path().'/.git/HEAD', FILE_USE_INCLUDE_PATH)) {
            $branchName = $stringFromFile[0]; //get the string from the array
            if (preg_match('#^.*\/(.*?)$#', $branchName, $out)) {
                $branchName = $out[1];
            }

            return $branchName;
        }

        return false;
    }
}