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
        if (!Storage::disk('local')->exists($path)) {
            return null;
        }

        $contents = Storage::disk('local')->get($path);

        // Para OWL, no haría falta cargarlo como array
        return json_decode($contents, true);
    }
}
