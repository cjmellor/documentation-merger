<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

use function Laravel\Prompts\info;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

class MergeDocs extends Command
{
    protected const TIMEOUT = 120;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:merge-docs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected array $repoDocs = [
        'laravel' => 'https://github.com/laravel/docs',
        'livewire' => 'https://github.com/livewire/docs',
        'alpinejs' => 'https://github.com/alpinejs/alpine',
        'tailwindcss' => 'https://github.com/tailwindlabs/tailwindcss.com',
    ];

    protected array $pathToDocs = [
        'laravel' => '',
        'livewire' => '',
        'alpinejs' => 'packages/docs/src/en',
        'tailwindcss' => 'src/pages/docs',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Select which documentation files you want to merge
        $docs = multiselect(label: 'Select which documentation files you want to merge', options: [
            'laravel' => 'Laravel',
            'livewire' => 'Livewire',
            'alpinejs' => 'AlpineJS',
            'tailwindcss' => 'TailwindCSS',
        ], required: true);

        // We'll store the downloaded repo's in the temp folder
        // If it doesn't exist, we'll create it
        $this->ensureFolderExists(folders: 'app/temp');

        // Let's loop through the selected docs and download them
        foreach ($docs as $doc) {
            $this->retrieveAndMoveDocumentation($doc);

            $this->mergeDocumentation($doc);
        }

        info(message: "Documentation merged!");
    }

    protected function ensureFolderExists($folders): void
    {
        $folders = is_array($folders) ? $folders : [$folders];

        foreach ($folders as $folder) {
            if (! File::exists(storage_path($folder))) {
                File::makeDirectory(storage_path($folder), recursive: true);
            }
        }
    }

    protected function retrieveAndMoveDocumentation(int|string $doc): void
    {
        info(message: "Retrieving '$doc' documentation...");

        $this->ensureFolderExists([
            "app/docs/$doc",
            "app/temp/$doc",
        ]);

        // We'll clone the repo into the temp folder
        spin(callback: fn () => Process::path(storage_path(path: "app/temp/$doc"))
            ->timeout(self::TIMEOUT)
            ->run(command: "git clone {$this->repoDocs[$doc]}.git .")
        );

        // There might be cases where the docs in the repo are in folders
        // If that's the case, we'll move to that folder to retrieve them
        if (array_key_exists(key: $doc, array: $this->pathToDocs) && $this->pathToDocs[$doc] !== '') {
            Process::run(command: "mv storage/app/temp/$doc/{$this->pathToDocs[$doc]}/* storage/app/docs/$doc");
            // Otherwise, we'll just move the files as normal
        } else {
            Process::run(command: "mv storage/app/temp/$doc/* storage/app/docs/$doc");
        }

        File::deleteDirectory(storage_path(path: "app/temp/$doc"));
    }

    protected function mergeDocumentation(mixed $doc): void
    {
        $files = File::allFiles(storage_path(path: "app/docs/$doc"));

        $mergedFile = '';

        foreach ($files as $file) {
            $mergedFile .= File::get($file->getPathname());
        }

        warning(message: "Merging...");

        File::put(storage_path(path: "app/docs/$doc.{$files[0]->getExtension()}"), $mergedFile);

        File::deleteDirectory(storage_path(path: "app/docs/$doc"));
    }
}
