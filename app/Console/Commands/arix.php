<?php

namespace Pterodactyl\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class Arix extends Command
{
    protected $signature = "arix {action?}";
    protected $description = "All commands for Arix Theme for Pterodactyl.";

    public function handle()
    {
        $action = $this->argument("action");

        $title = new OutputFormatterStyle("#fff", null, ["bold"]);
        $this->output->getFormatter()->setStyle("title", $title);

        $b = new OutputFormatterStyle(null, null, ["bold"]);
        $this->output->getFormatter()->setStyle("b", $b);

        if ($action === null) {
            $this->line("
            <title>
            ░█████╗░██████╗░██╗██╗░░██╗
            ██╔══██╗██╔══██╗██║╚██╗██╔╝
            ███████║██████╔╝██║░╚███╔╝░
            ██╔══██║██╔══██╗██║░██╔██╗░
            ██║░░██║██║░░██║██║██╔╝╚██╗
            ╚═╝░░╚═╝╚═╝░░╚═╝╚═╝╚═╝░░╚═╝

           Thank you for purchasing Arix</title>

           > php artisan arix (this window)
           > php artisan arix install
           > php artisan arix update
           > php artisan arix uninstall
            ");
        } elseif ($action === "install") {
            $this->install();
        } elseif ($action === "update") {
            $this->update();
        } elseif ($action === "uninstall") {
            $this->uninstall();
        } else {
            $this->error(
                "Invalid action. Supported actions: install, update, uninstall"
            );
        }
    }

    public function installOrUpdate($isUpdate = false)
    {
        if ($isUpdate) {
            $this->info("
    This command is not recommended to use.
    This command skips frequently used files by addons during theme updating to avoid losing your addon customizations.
    If you still experience an error after updating please contact us.");
        }
        $confirmation = $this->confirm(
            "Are all the required dependencies installed from the readme file?",
            "yes"
        );
        if (!$confirmation) {
            return;
        }

        $versions = File::directories("./arix");
        if (empty($versions)) {
            $this->info("No versions found in /arix directory.");
            return;
        }

        $version = basename($this->choice("Select a version:", $versions));
        $this->info("Installing Arix Theme {$version}...");

        $excludeOption = "";
        if ($isUpdate) {
            $excludeOption =
                "--exclude='routes.ts' --exclude='getServer.ts' --exclude='admin.blade.php' --exclude='admin.php' --exclude='ServerTransformer.php'";
        }

        exec("rsync -a {$excludeOption} arix/{$version}/ ./");

        $this->info("Proceeding with the installation...");
        $this->info("Migrating database...");
        $this->command("php artisan migrate --force");

        $this->info("Installing required packages...");
        $this->info("This can take a minute...");
        $this->command(
            "yarn add @types/md5 md5 react-icons@5.4.0 @types/bbcode-to-react bbcode-to-react i18next-browser-languagedetector@7.2.1"
        );

        $nodeVersion = shell_exec("node -v");
        $nodeVersion = (int) ltrim($nodeVersion, "v");

        if ($nodeVersion < 17) {
            $this->info("Node.js version is v{$nodeVersion} (< 17)");
        } else {
            $this->info("Node.js version is v{$nodeVersion} (>= 17)");
            putenv("export NODE_OPTIONS=--openssl-legacy-provider");
        }

        $this->info("Building panel assets...");
        $this->info("This can take a minute...");
        $this->command("yarn build:production");

        $this->info("Set permissions...");
        $this->command("chown -R www-data:www-data " . base_path() . "/*");
        $this->command("chown -R nginx:nginx " . base_path() . "/*");
        $this->command("chown -R apache:apache " . base_path() . "/*");

        $this->info("Optimize application...");
        $this->command("php artisan optimize:clear");
        $this->command("php artisan optimize");

        $this->info("Compile translations...");
        $this->command("php artisan language:compile");

        $message = $isUpdate
            ? "✨    🌟  Theme updated  🌟  ✨"
            : "✨    🌟 Theme installed  🌟  ✨";
        $this->line("
        
        ─────────────────────────────────────
        │                                   │
        │            {$message}           
        │      succefullly        │
        │                                   │
        ─────────────────────────────────────
        ");
    }

    public function install()
    {
        $this->installOrUpdate();
    }

    public function update()
    {
        $this->installOrUpdate(true);
    }

    private function uninstall()
    {
        $this->line("Uninstalling...");
        $this->command("php artisan down");
        $this->command(
            "curl -L https://github.com/pterodactyl/panel/releases/latest/download/panel.tar.gz | tar -xzv"
        );
        $this->command("chmod -R 755 storage/* bootstrap/cache");
        $this->command("composer install --no-dev --optimize-autoloader");
        $this->command("php artisan view:clear");
        $this->command("php artisan config:clear");
        $this->command("php artisan config:clear");
        $this->command("php artisan migrate --seed --force");
        $this->command("chown -R www-data:www-data " . base_path() . "/*");
        $this->command("chown -R nginx:nginx " . base_path() . "/*");
        $this->command("chown -R apache:apache " . base_path() . "/*");
        $this->command("php artisan queue:restart");
        $this->command("php artisan up");
    }

    private function command($cmd)
    {
        return exec($cmd);
    }
}