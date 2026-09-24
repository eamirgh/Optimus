<?php

namespace Eamirgh\Optimus\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishSkillCommand extends Command
{
    protected $signature = 'optimus:skill
                            {--path= : Custom destination path for the skill file}';

    protected $aliases = ['optimus:publish:skill'];

    protected $description = 'Publish the Laravel Optimus agent skill (SKILL.md) to your application';

    public function handle(): int
    {
        $sourcePath = __DIR__ . '/../../SKILL.md';

        if (! File::exists($sourcePath)) {
            $this->error('Skill source file (SKILL.md) could not be located.');
            return self::FAILURE;
        }

        $destinationPath = $this->option('path')
            ? (string) $this->option('path')
            : base_path('.agents/skills/optimus/SKILL.md');

        $destinationDir = dirname($destinationPath);

        if (! File::isDirectory($destinationDir)) {
            File::makeDirectory($destinationDir, 0755, true);
        }

        File::copy($sourcePath, $destinationPath);

        $this->info("Laravel Optimus agent skill published successfully to [{$destinationPath}].");

        return self::SUCCESS;
    }
}
