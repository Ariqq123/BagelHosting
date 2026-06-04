<?php

namespace Pterodactyl\Services\Minecraft;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Pterodactyl\Models\Egg;
use Pterodactyl\Models\Nest;
use Pterodactyl\Models\EggVariable;

class McVersionsEggGeneratorService
{
    public function __construct(private readonly McVersionsCatalogService $catalog)
    {
    }

    public function preview(): array
    {
        $nest = $this->managedNest();

        return [
            'nest' => [
                'name' => McVersionsCatalogService::NEST_NAME,
                'action' => $nest ? 'update' : 'create',
            ],
            'eggs' => array_map(function (array $definition) use ($nest) {
                $egg = $nest ? $this->managedEgg($nest, $definition['name']) : null;

                return [
                    'name' => $definition['name'],
                    'action' => $egg ? 'update' : 'create',
                    'available' => true,
                ];
            }, $this->catalog->definitions()),
        ];
    }

    public function sync(): array
    {
        return DB::transaction(function () {
            $created = 0;
            $updated = 0;
            $skipped = 0;
            $failed = [];

            $nest = $this->managedNest();
            if (!$nest) {
                $nest = new Nest();
                $nest->forceFill([
                    'uuid' => (string) Str::uuid(),
                    'author' => McVersionsCatalogService::AUTHOR,
                    'name' => McVersionsCatalogService::NEST_NAME,
                    'description' => McVersionsCatalogService::MARKER,
                ])->save();
                ++$created;
            } else {
                $nest->forceFill([
                    'description' => McVersionsCatalogService::MARKER,
                ])->save();
                ++$updated;
            }

            foreach ($this->catalog->definitions() as $definition) {
                try {
                    $egg = $this->upsertEgg($nest, $definition);
                    $egg['created'] ? ++$created : ++$updated;
                } catch (\Throwable $exception) {
                    ++$skipped;
                    $failed[] = [
                        'name' => $definition['name'] ?? 'Unknown',
                        'reason' => $exception->getMessage(),
                    ];
                }
            }

            return compact('created', 'updated', 'skipped', 'failed');
        });
    }

    private function managedNest(): ?Nest
    {
        return Nest::query()
            ->where('name', McVersionsCatalogService::NEST_NAME)
            ->where('author', McVersionsCatalogService::AUTHOR)
            ->first();
    }

    private function managedEgg(Nest $nest, string $name): ?Egg
    {
        return Egg::query()
            ->where('nest_id', $nest->id)
            ->where('name', $name)
            ->where('author', McVersionsCatalogService::AUTHOR)
            ->where('description', 'like', McVersionsCatalogService::MARKER . '%')
            ->first();
    }

    private function upsertEgg(Nest $nest, array $definition): array
    {
        $egg = $this->managedEgg($nest, $definition['name']);
        $created = false;
        $payload = Arr::except($definition, ['variables']);
        $payload['nest_id'] = $nest->id;
        $payload['author'] = McVersionsCatalogService::AUTHOR;

        if (!$egg) {
            $created = true;
            $egg = new Egg();
            $egg->forceFill(array_merge($payload, [
                'uuid' => (string) Str::uuid(),
            ]))->save();
        } else {
            $egg->forceFill($payload)->save();
        }

        foreach ($definition['variables'] as $variable) {
            $this->upsertVariable($egg, $variable);
        }

        return ['egg' => $egg, 'created' => $created];
    }

    private function upsertVariable(Egg $egg, array $definition): EggVariable
    {
        $variable = EggVariable::query()
            ->where('egg_id', $egg->id)
            ->where('env_variable', $definition['env_variable'])
            ->first();

        $payload = array_merge($definition, ['egg_id' => $egg->id]);

        if (!$variable) {
            return EggVariable::query()->create($payload);
        }

        $variable->forceFill($payload)->save();

        return $variable;
    }
}
