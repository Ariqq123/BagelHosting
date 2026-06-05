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
        return $this->base('Paper', 'Installs Paper from the MCJars API.', $this->mcJarsInstallScript('PAPER'));
    }

    private function purpur(): array
    {
        return $this->base('Purpur', 'Installs Purpur from the MCJars API.', $this->mcJarsInstallScript('PURPUR'));
    }

    private function vanilla(): array
    {
        return $this->base('Vanilla', 'Installs Vanilla from the MCJars API.', $this->mcJarsInstallScript('VANILLA'));
    }

    private function fabric(): array
    {
        return $this->base('Fabric', 'Installs Fabric from the MCJars API.', $this->mcJarsInstallScript('FABRIC'));
    }

    private function forge(): array
    {
        return $this->base('Forge', 'Installs Forge from the MCJars API.', $this->mcJarsInstallScript('FORGE'));
    }

    private function velocity(): array
    {
        $definition = $this->base('Velocity', 'Installs Velocity from the MCJars API.', $this->mcJarsInstallScript('VELOCITY'));
        $definition['startup'] = 'java -Xms128M -XX:MaxRAMPercentage=95.0 -jar {{SERVER_JARFILE}}';

        return $definition;
    }

    private function mcJarsInstallScript(string $type): string
    {
        return str_replace('{{TYPE}}', $type, <<<'SH'
#!/bin/ash
apk add --no-cache curl jq unzip
cd /mnt/server
TYPE="{{TYPE}}"
API="https://mcjars.app/api/v1/builds/${TYPE}"

if [ -n "${MINECRAFT_VERSION}" ] && [ "${MINECRAFT_VERSION}" != "latest" ]; then
  API="${API}/${MINECRAFT_VERSION}"
fi

BUILD=$(curl -fsSL "${API}" | jq -c '
  if has("builds") then
    .builds[0]
  else
    ([.versions | to_entries[] | select(.value.supported != false) | .value.latest] | last) // ([.versions | to_entries[] | .value.latest] | last)
  end
')

if [ -z "${BUILD}" ] || [ "${BUILD}" = "null" ]; then
  echo "No MCJars build found for ${TYPE} ${MINECRAFT_VERSION:-latest}"
  exit 1
fi

JAR_URL=$(echo "${BUILD}" | jq -r '.jarUrl // empty')
ZIP_URL=$(echo "${BUILD}" | jq -r '.zipUrl // empty')

if [ -n "${JAR_URL}" ]; then
  curl -fsSL -o "${SERVER_JARFILE}" "${JAR_URL}"
elif [ -n "${ZIP_URL}" ]; then
  curl -fsSL -o mcjars-server.zip "${ZIP_URL}"
  rm -rf libraries
  unzip -o mcjars-server.zip
  rm -f mcjars-server.zip

  if [ -f server.jar ] && [ "${SERVER_JARFILE}" != "server.jar" ]; then
    mv server.jar "${SERVER_JARFILE}"
  fi
else
  echo "MCJars build did not include a jar or zip download URL."
  exit 1
fi
SH);
    }
}
