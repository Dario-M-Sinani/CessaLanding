<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Inicio';

    protected static ?string $title = 'Inicio';

    protected static ?string $navigationGroup = 'Menú Principal';

    protected static ?int $navigationSort = 0;
}
