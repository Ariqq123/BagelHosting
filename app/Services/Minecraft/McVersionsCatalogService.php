<?php

namespace Pterodactyl\Services\Minecraft;

class McVersionsCatalogService
{
    public const AUTHOR = 'mc-versions-generator@example.com';
    public const MARKER = 'Managed by MC Versions generator.';
    public const NEST_NAME = 'Minecraft Versions';

    public function definitions(): array
    {
        return [
            $this->paper(),
            $this->purpur(),
            $this->vanilla(),
            $this->fabric(),
            $this->forge(),
            $this->velocity(),
        ];
    }

    private function base(string $name, string $description, string $installScript, array $extraVariables = []): array
    {
        return [
            'name' => $name,
            'description' => self::MARKER . ' ' . $description,
            'features' => ['eula'],
            'docker_images' => [
                'Java 21' => 'ghcr.io/pterodactyl/yolks:java_21',
                'Java 17' => 'ghcr.io/pterodactyl/yolks:java_17',
            ],
            'startup' => 'java -Xms128M -XX:MaxRAMPercentage=95.0 -jar {{SERVER_JARFILE}}',
            'config_stop' => 'stop',
            'config_startup' => '{}',
            'config_logs' => '{}',
            'config_files' => '{}',
            'script_is_privileged' => false,
            'script_install' => $installScript,
            'script_entry' => 'ash',
            'script_container' => 'ghcr.io/pterodactyl/installers:alpine',
            'force_outgoing_ip' => false,
            'variables' => array_merge($this->commonVariables(), $extraVariables),
        ];
    }

    private function commonVariables(): array
    {
        return [
            [
                'name' => 'Minecraft Version',
                'description' => 'Minecraft version to install. Use latest for the newest supported release.',
                'env_variable' => 'MINECRAFT_VERSION',
                'default_value' => 'latest',
                'user_viewable' => true,
                'user_editable' => true,
                'rules' => 'required|string|max:32',
            ],
            [
                'name' => 'Server Jar File',
                'description' => 'The jar file name used by the startup command.',
                'env_variable' => 'SERVER_JARFILE',
                'default_value' => 'server.jar',
                'user_viewable' => true,
                'user_editable' => true,
                'rules' => 'required|regex:/^([\\w\\d._-]+)(\\.jar)$/',
            ],
        ];
    }

    private function paper(): array
    {
        return $this->base('Paper', 'Installs Paper from the PaperMC API.', <<<'SH'
#!/bin/ash
apk add --no-cache curl jq
cd /mnt/server
PROJECT=paper
USER_AGENT="Pterodactyl MC Versions Generator"
if [ -z "${MINECRAFT_VERSION}" ] || [ "${MINECRAFT_VERSION}" = "latest" ]; then
  MINECRAFT_VERSION=$(curl -sSL -A "${USER_AGENT}" https://api.papermc.io/v2/projects/${PROJECT} | jq -r '.versions[-1]')
fi
BUILD=$(curl -sSL -A "${USER_AGENT}" https://api.papermc.io/v2/projects/${PROJECT}/versions/${MINECRAFT_VERSION} | jq -r '.builds[-1]')
JAR=${PROJECT}-${MINECRAFT_VERSION}-${BUILD}.jar
curl -sSL -A "${USER_AGENT}" -o "${SERVER_JARFILE}" "https://api.papermc.io/v2/projects/${PROJECT}/versions/${MINECRAFT_VERSION}/builds/${BUILD}/downloads/${JAR}"
SH);
    }

    private function purpur(): array
    {
        return $this->base('Purpur', 'Installs Purpur from the Purpur API.', <<<'SH'
#!/bin/ash
apk add --no-cache curl jq
cd /mnt/server
if [ -z "${MINECRAFT_VERSION}" ] || [ "${MINECRAFT_VERSION}" = "latest" ]; then
  MINECRAFT_VERSION=$(curl -sSL https://api.purpurmc.org/v2/purpur | jq -r '.versions[-1]')
fi
BUILD=$(curl -sSL https://api.purpurmc.org/v2/purpur/${MINECRAFT_VERSION} | jq -r '.builds.latest')
curl -sSL -o "${SERVER_JARFILE}" "https://api.purpurmc.org/v2/purpur/${MINECRAFT_VERSION}/${BUILD}/download"
SH);
    }

    private function vanilla(): array
    {
        return $this->base('Vanilla', 'Installs Vanilla from Mojang manifests.', <<<'SH'
#!/bin/ash
apk add --no-cache curl jq
cd /mnt/server
MANIFEST=$(curl -sSL https://launchermeta.mojang.com/mc/game/version_manifest.json)
if [ -z "${MINECRAFT_VERSION}" ] || [ "${MINECRAFT_VERSION}" = "latest" ]; then
  MINECRAFT_VERSION=$(echo "${MANIFEST}" | jq -r '.latest.release')
fi
VERSION_URL=$(echo "${MANIFEST}" | jq -r --arg VERSION "${MINECRAFT_VERSION}" '.versions[] | select(.id == $VERSION) | .url')
DOWNLOAD_URL=$(curl -sSL "${VERSION_URL}" | jq -r '.downloads.server.url')
curl -sSL -o "${SERVER_JARFILE}" "${DOWNLOAD_URL}"
SH);
    }

    private function fabric(): array
    {
        return $this->base('Fabric', 'Installs Fabric loader from Fabric metadata.', <<<'SH'
#!/bin/ash
apk add --no-cache curl jq
cd /mnt/server
if [ -z "${MINECRAFT_VERSION}" ] || [ "${MINECRAFT_VERSION}" = "latest" ]; then
  MINECRAFT_VERSION=$(curl -sSL https://meta.fabricmc.net/v2/versions/game | jq -r '[.[] | select(.stable == true)][0].version')
fi
LOADER_VERSION=$(curl -sSL https://meta.fabricmc.net/v2/versions/loader | jq -r '.[0].version')
INSTALLER_VERSION=$(curl -sSL https://meta.fabricmc.net/v2/versions/installer | jq -r '.[0].version')
curl -sSL -o "${SERVER_JARFILE}" "https://meta.fabricmc.net/v2/versions/loader/${MINECRAFT_VERSION}/${LOADER_VERSION}/${INSTALLER_VERSION}/server/jar"
SH);
    }

    private function forge(): array
    {
        return $this->base('Forge', 'Installs Forge using Forge promotions metadata.', <<<'SH'
#!/bin/ash
apk add --no-cache curl jq bash
cd /mnt/server
if [ -z "${MINECRAFT_VERSION}" ] || [ "${MINECRAFT_VERSION}" = "latest" ]; then
  MINECRAFT_VERSION=$(curl -sSL https://files.minecraftforge.net/net/minecraftforge/forge/promotions_slim.json | jq -r '.promos | keys[] | select(endswith("-latest")) | split("-")[0]' | sort -V | tail -1)
fi
FORGE_VERSION=$(curl -sSL https://files.minecraftforge.net/net/minecraftforge/forge/promotions_slim.json | jq -r --arg MC "${MINECRAFT_VERSION}-latest" '.promos[$MC]')
DOWNLOAD="https://maven.minecraftforge.net/net/minecraftforge/forge/${MINECRAFT_VERSION}-${FORGE_VERSION}/forge-${MINECRAFT_VERSION}-${FORGE_VERSION}-installer.jar"
curl -sSL -o installer.jar "${DOWNLOAD}"
java -jar installer.jar --installServer
if [ -f forge-${MINECRAFT_VERSION}-${FORGE_VERSION}.jar ]; then
  mv forge-${MINECRAFT_VERSION}-${FORGE_VERSION}.jar "${SERVER_JARFILE}"
fi
rm -f installer.jar
SH);
    }

    private function velocity(): array
    {
        $definition = $this->base('Velocity', 'Installs Velocity from the PaperMC API.', <<<'SH'
#!/bin/ash
apk add --no-cache curl jq
cd /mnt/server
PROJECT=velocity
USER_AGENT="Pterodactyl MC Versions Generator"
if [ -z "${MINECRAFT_VERSION}" ] || [ "${MINECRAFT_VERSION}" = "latest" ]; then
  MINECRAFT_VERSION=$(curl -sSL -A "${USER_AGENT}" https://api.papermc.io/v2/projects/${PROJECT} | jq -r '.versions[-1]')
fi
BUILD=$(curl -sSL -A "${USER_AGENT}" https://api.papermc.io/v2/projects/${PROJECT}/versions/${MINECRAFT_VERSION} | jq -r '.builds[-1]')
JAR=${PROJECT}-${MINECRAFT_VERSION}-${BUILD}.jar
curl -sSL -A "${USER_AGENT}" -o "${SERVER_JARFILE}" "https://api.papermc.io/v2/projects/${PROJECT}/versions/${MINECRAFT_VERSION}/builds/${BUILD}/downloads/${JAR}"
SH);
        $definition['startup'] = 'java -Xms128M -XX:MaxRAMPercentage=95.0 -jar {{SERVER_JARFILE}}';

        return $definition;
    }
}
