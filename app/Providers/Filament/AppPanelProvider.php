<?php

namespace App\Providers\Filament;

use App\Filament\Billing\BillingProvider;
use App\Filament\Pages\Auth\Register;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Tenancy\RegisterOrganization;
use App\Http\Middleware\FilamentSettings;
use App\Models\{Organization, User};
use Filament\Http\Middleware\{Authenticate, AuthenticateSession, DisableBladeIconComponents, DispatchServingFilamentEvent};
use Filament\Navigation\MenuItem;
use Filament\Pages\Auth\EmailVerification\EmailVerificationPrompt;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\{Panel, PanelProvider, Widgets};
use Illuminate\Cookie\Middleware\{AddQueuedCookiesToResponse, EncryptCookies};
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('app')
            ->login()
            ->passwordReset()
            ->registration(Register::class)
            ->emailVerification()
            ->sidebarCollapsibleOnDesktop()
            ->breadcrumbs()
            ->databaseNotifications()
            ->maxContentWidth(MaxWidth::ScreenTwoExtraLarge)
            ->emailVerification(EmailVerificationPrompt::class)
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label('My Profile')
                    // ->label( fn() =>Auth::user()->name)
                    ->url(
                        fn () => User::find(Auth::user()->id)->hasVerifiedEmail()

                        ? rescue(fn () => EditProfilePage::getUrl(), null)
                        : null
                    )
                    ->icon('heroicon-m-user-circle')
                    // If you are using tenancy need to check with the visible method where ->company() is the relation between the user and tenancy model as you called
                    ->visible(function (): bool {
                        $user = Auth::user();

                        return $user instanceof User && method_exists($user, 'organizations') && $user->organizations()->exists();

                    }),
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Admin')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->url('/admin')
                    ->visible(fn (): bool => Auth::user()->is_admin),
            ])
            ->colors([
                'danger'  => Color::Red,
                'gray'    => Color::Slate,
                'info'    => Color::Blue,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
                'primary' => Color::Amber,
            ])
            ->navigationGroups([
                'Administration',
                'Support',

            ])
            ->discoverResources(in: app_path('Filament/App/Resources'), for: 'App\\Filament\\App\\Resources')
            ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\\Filament\\App\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'App\\Filament\\App\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,

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
                FilamentSettings::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                FilamentEditProfilePlugin::make()
                    ->slug('my-profile')
                    ->setTitle('My Profile')
                    ->setNavigationLabel('My Profile')
                    ->setNavigationGroup('Group Profile')
                    ->setIcon('heroicon-o-user')
                    ->setSort(10)
                    ->shouldRegisterNavigation(false)
                    ->shouldShowDeleteAccountForm(false)
                    ->shouldShowBrowserSessionsForm()
                    ->shouldShowAvatarForm(
                        value: true,
                        directory: 'avatars', // image will be stored in 'storage/app/public/avatars
                    )
                    ->customProfileComponents([
                        \App\Livewire\ColorProfileComponent::class,
                    ]),
            ])
            ->tenant(Organization::class, ownershipRelationship: 'organization', slugAttribute: 'slug')
            ->tenantRegistration(RegisterOrganization::class)
            ->tenantBillingProvider(new BillingProvider())
            ->requiresTenantSubscription();
    }
}
