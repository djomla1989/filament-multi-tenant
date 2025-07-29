<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Backup\Backup;
use App\Http\Middleware\VerifyIsAdmin;
use Croustibat\FilamentJobsMonitor\FilamentJobsMonitorPlugin;
use Filament\Http\Middleware\{Authenticate, AuthenticateSession, DisableBladeIconComponents, DispatchServingFilamentEvent};
use Filament\Navigation\MenuItem;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\{Pages, Panel, PanelProvider};
use Illuminate\Cookie\Middleware\{AddQueuedCookiesToResponse, EncryptCookies};
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin; // Ensure this class exists in the specified namespace

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->sidebarCollapsibleOnDesktop()
            ->databaseNotifications()
            ->globalSearch(true)
            ->maxContentWidth(MaxWidth::ScreenTwoExtraLarge)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Application')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->url('/app'),
            ])
            ->font('Inter')
            ->colors([
                'danger'  => Color::Rose,
                'gray'    => Color::Gray,
                'info'    => Color::Blue,
                'primary' => Color::Indigo,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->navigationGroups([
                'Plans',
                'Administration',
                'System',

            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([

            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                VerifyIsAdmin::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])

            ->plugins([
                // ...
                FilamentJobsMonitorPlugin::make(),
                /*
                FilamentSpatieLaravelBackupPlugin::make()
                    ->usingPage(Backup::class)
                    ->usingPolingInterval('10s') // default value is 4s
                    ->usingQueue('default') // default value is null
                    ->timeout(120) // default value is 120s
                    */
            ]);
    }
}
