<?php

namespace Pterodactyl\Tests\Feature\Admin;

use Psr\Log\LoggerInterface;
use Illuminate\Support\Collection;
use Pterodactyl\Models\User;
use Pterodactyl\Tests\TestCase;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Encryption\Encrypter;
use Pterodactyl\Providers\SettingsServiceProvider;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class MailSettingsTest extends TestCase
{
    public function test_admin_can_update_registration_allowed_domains_from_mail_settings(): void
    {
        config()->set('mail.default', 'smtp');

        $this->mock(Kernel::class, function ($mock) {
            $mock->shouldReceive('call')->once()->with('queue:restart');
        });

        $this->mock(SettingsRepositoryInterface::class, function ($mock) {
            $mock->shouldReceive('set')->with('settings::mail:mailers:smtp:host', 'smtp.example.com')->once();
            $mock->shouldReceive('set')->with('settings::mail:mailers:smtp:port', 587)->once();
            $mock->shouldReceive('set')->with('settings::mail:mailers:smtp:encryption', 'tls')->once();
            $mock->shouldReceive('set')->with('settings::mail:mailers:smtp:username', 'mailer')->once();
            $mock->shouldReceive('set')->with('settings::mail:from:address', 'panel@example.com')->once();
            $mock->shouldReceive('set')->with('settings::mail:from:name', 'Panel')->once();
            $mock->shouldReceive('set')
                ->with('settings::arix:registration:allowed_domains', json_encode(['gmail.com', 'bagelsmp.tech', 'team.example.com']))
                ->once();
        });

        $admin = User::factory()->admin()->make(['id' => 1]);

        $response = $this->actingAs($admin)->patchJson('/admin/settings/mail', [
            'mail:mailers:smtp:host' => 'smtp.example.com',
            'mail:mailers:smtp:port' => 587,
            'mail:mailers:smtp:encryption' => 'tls',
            'mail:mailers:smtp:username' => 'mailer',
            'mail:mailers:smtp:password' => '',
            'mail:from:address' => 'panel@example.com',
            'mail:from:name' => 'Panel',
            'arix:registration:allowed_domains' => " Gmail.COM\nbagelsmp.tech, team.example.com\n",
        ]);

        $response->assertNoContent();
    }

    public function test_settings_provider_decodes_registration_allowed_domains(): void
    {
        config()->set('mail.default', 'smtp');
        config()->set('arix.registration.allowed_domains', ['original.test']);

        $settings = $this->mock(SettingsRepositoryInterface::class, function ($mock) {
            $mock->shouldReceive('all')->once()->andReturn(new Collection([
                (object) [
                    'key' => 'settings::arix:registration:allowed_domains',
                    'value' => json_encode(['school.edu', 'gmail.com']),
                ],
            ]));
        });

        (new SettingsServiceProvider($this->app))->boot(
            $this->app->make(ConfigRepository::class),
            $this->app->make(Encrypter::class),
            $this->app->make(LoggerInterface::class),
            $settings,
        );

        $this->assertSame(['school.edu', 'gmail.com'], config('arix.registration.allowed_domains'));
    }
}
