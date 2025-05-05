<?php

namespace App\Support\Storage;

use Illuminate\Support\Facades\Storage;

class LocalGraphStorage  implements GraphStorageInterface
{
    public function save(string $path, array $graph): void
    {
        if (isset($graph['raw'])) {
            // Si es "raw", guardar el contenido tal cual
            Storage::disk('local')->put($path, $graph['raw']);
        } else {
            Storage::disk('local')->put($path, json_encode($graph, JSON_PRETTY_PRINT));
        }
    }

    public function load(string $path): ?array
    {
        $contents = $this->loadRaw($path);
        return json_decode($contents, true);
    }
    public function loadRaw(string $path): ?string
    {
        if (!Storage::disk('local')->exists($path)) {
            return null;
        }

        return Storage::disk('local')->get($path);
    }

    public function saveRaw(string $path, string $graph): void
    {
        Storage::disk('local')->put($path, json_encode($graph, JSON_PRETTY_PRINT));
    }
}
