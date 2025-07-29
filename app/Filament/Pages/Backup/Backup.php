<?php

namespace App\Filament\Pages\Backup;

use ShuvroRoy\FilamentSpatieLaravelBackup\Pages\Backups as BaseBackups;

class Backup extends BaseBackups
{
    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Sistema';

    protected static ?string $modelLabel = 'Backup';

    protected static ?string $modelLabelPlural = "Backup";

    protected static ?int $navigationSort = 2;

    public function getHeading(): string
    {
        return 'Application Backups';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function getNavigationLabel(): string
    {
        return 'Backup';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Sistema';
    }
}
