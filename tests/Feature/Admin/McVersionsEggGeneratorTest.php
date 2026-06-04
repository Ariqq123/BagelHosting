<?php

namespace Pterodactyl\Tests\Feature\Admin;

use Pterodactyl\Models\Egg;
use Pterodactyl\Models\Nest;
use Pterodactyl\Models\EggVariable;
use Pterodactyl\Tests\TestCase;
use Pterodactyl\Services\Minecraft\McVersionsEggGeneratorService;

class McVersionsEggGeneratorTest extends TestCase
{
    public function test_preview_reports_actions_without_writing_records(): void
    {
        $result = $this->app->make(McVersionsEggGeneratorService::class)->preview();

        $this->assertSame(0, Nest::query()->where('name', 'Minecraft Versions')->count());
        $this->assertNotEmpty($result['eggs']);
        $this->assertSame('create', $result['nest']['action']);
        $this->assertContains('Paper', array_column($result['eggs'], 'name'));
    }

    public function test_sync_creates_managed_nest_eggs_and_variables(): void
    {
        $result = $this->app->make(McVersionsEggGeneratorService::class)->sync();

        $nest = Nest::query()->where('name', 'Minecraft Versions')->firstOrFail();
        $paper = Egg::query()->where('nest_id', $nest->id)->where('name', 'Paper')->firstOrFail();

        $this->assertSame('mc-versions-generator@example.com', $nest->author);
        $this->assertStringContainsString('Managed by MC Versions generator.', $nest->description);
        $this->assertStringContainsString('SERVER_JARFILE', $paper->startup);
        $this->assertStringContainsString('api.papermc.io', $paper->script_install);
        $this->assertGreaterThanOrEqual(1, $result['created']);
        $this->assertDatabaseHas('egg_variables', [
            'egg_id' => $paper->id,
            'env_variable' => 'MINECRAFT_VERSION',
        ]);
        $this->assertDatabaseHas('egg_variables', [
            'egg_id' => $paper->id,
            'env_variable' => 'SERVER_JARFILE',
        ]);
    }

    public function test_sync_is_idempotent_and_keeps_egg_ids_stable(): void
    {
        $this->app->make(McVersionsEggGeneratorService::class)->sync();
        $firstNest = Nest::query()->where('name', 'Minecraft Versions')->firstOrFail();
        $firstPaper = Egg::query()->where('nest_id', $firstNest->id)->where('name', 'Paper')->firstOrFail();
        $firstVariableCount = EggVariable::query()->where('egg_id', $firstPaper->id)->count();

        $this->app->make(McVersionsEggGeneratorService::class)->sync();

        $this->assertSame(1, Nest::query()->where('name', 'Minecraft Versions')->count());
        $this->assertSame(1, Egg::query()->where('nest_id', $firstNest->id)->where('name', 'Paper')->count());
        $this->assertSame($firstPaper->id, Egg::query()->where('nest_id', $firstNest->id)->where('name', 'Paper')->value('id'));
        $this->assertSame($firstVariableCount, EggVariable::query()->where('egg_id', $firstPaper->id)->count());
    }

    public function test_sync_does_not_modify_unrelated_nests_or_eggs(): void
    {
        $nest = new Nest();
        $nest->forceFill([
            'uuid' => '00000000-0000-0000-0000-000000000001',
            'author' => 'admin@example.com',
            'name' => 'Minecraft',
            'description' => 'Existing nest',
        ])->save();

        $egg = new Egg();
        $egg->forceFill([
            'uuid' => '00000000-0000-0000-0000-000000000002',
            'nest_id' => $nest->id,
            'author' => 'admin@example.com',
            'name' => 'Paper',
            'description' => 'Existing egg',
            'docker_images' => ['Java 21' => 'ghcr.io/pterodactyl/yolks:java_21'],
            'startup' => 'java -jar server.jar',
            'config_stop' => 'stop',
            'config_startup' => '{}',
            'config_logs' => '{}',
            'config_files' => '{}',
            'script_is_privileged' => false,
            'script_install' => 'echo existing',
            'script_entry' => 'ash',
            'script_container' => 'ghcr.io/pterodactyl/installers:alpine',
            'force_outgoing_ip' => false,
        ])->save();

        $this->app->make(McVersionsEggGeneratorService::class)->sync();

        $this->assertSame('Existing nest', $nest->fresh()->description);
        $this->assertSame('echo existing', Egg::query()->where('nest_id', $nest->id)->where('name', 'Paper')->value('script_install'));
    }
}
