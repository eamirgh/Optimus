<?php

namespace Eamirgh\Optimus\Tests\Feature;

use Eamirgh\Optimus\Tests\TestCase;
use Illuminate\Support\Facades\File;

class PublishSkillCommandTest extends TestCase
{
    private string $targetSkillPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->targetSkillPath = sys_get_temp_dir() . '/optimus_skill_test/SKILL.md';
    }

    protected function tearDown(): void
    {
        if (File::exists($this->targetSkillPath)) {
            File::delete($this->targetSkillPath);
        }
        $dir = dirname($this->targetSkillPath);
        if (File::isDirectory($dir)) {
            File::deleteDirectory($dir);
        }
        parent::tearDown();
    }

    public function test_it_publishes_skill_to_specified_path(): void
    {
        $this->artisan('optimus:skill', ['--path' => $this->targetSkillPath])
            ->assertSuccessful()
            ->expectsOutputToContain('Laravel Optimus agent skill published successfully');

        $this->assertFileExists($this->targetSkillPath);
        $content = File::get($this->targetSkillPath);
        $this->assertStringContainsString('# Skill: optimus', $content);
        $this->assertStringContainsString('<x-image>', $content);
    }
}
