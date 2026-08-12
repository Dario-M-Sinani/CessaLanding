<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

// Agrupa PersonalResource y PersonalCategoriaResource bajo un solo ítem "Personal" en el menú
// (antes eran dos entradas separadas) -- a pedido explícito del usuario, ver
// ESTADO_SEGURIDAD_MIGRACION.md §3.31ter. Al entrar, redirige sola a la primera sub-página
// (comportamiento propio de Cluster::mount(), sin código nuestro).
class Personal extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'Personal';

    protected static ?string $navigationGroup = 'Menú Principal';

    protected static ?int $navigationSort = 85;
}
