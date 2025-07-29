<?php

declare(strict_types = 1);

namespace App\Filament\Actions;

use App\Data\Stripe\StripeDataLoader;
use App\Forms\Components\RadioGroup;
use App\Models\Organization;

use function App\Support\tenant;

use Closure;
use Filament\Actions\Action;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\{Arr, HtmlString};
use Illuminate\View\View;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class SubscribePlanAction extends Action
{
    protected string | HtmlString | Closure | null $brandLogo = null;

    protected string | Htmlable | Closure | null $heading = null;

    protected string | Htmlable | Closure | null $subheading = null;

    protected function setUp(): void
    {
        $this->name('subscribe');

        $this->modalWidth(MaxWidth::Large);

        $this->modalContent(view('filament.actions.subscribe.header', $this->extractPublicMethods()));

        $this->form([
            RadioGroup::make('billing_period')
                ->label(__('Select your plan'))
                ->options($this->getBilledPeriods())
                ->default(array_key_first($this->getBilledPeriods()))
                ->columnSpanFull()
                ->badges([
                    'year' => __('Best Value'),
                ])
                ->required(),
        ]);

        $this->registerModalActions([
            Action::make('checkout')
                ->label(__('Subscribe Now!'))
                ->size('xl')
                ->extraAttributes(['class' => 'w-full'])
                ->action(function (Action $action) {
                    $actions       = $action->getLivewire()->mountedActionsData;
                    $billingPeriod = data_get(Arr::first($actions), 'billing_period');

                    // Calls the function that creates the checkout session and redirects the user
                    $this->checkoutUrl($billingPeriod);
                }),
        ]);

        $this->modalContentFooter(function (Action $action): View {
            return view('filament.actions.subscribe.footer', [
                ...$this->extractPublicMethods(),
                'action' => $action,
            ]);
        });

        $this->closeModalByClickingAway(false);
        $this->closeModalByEscaping(false);
        $this->modalCloseButton(false);
        $this->modalCancelAction(false);
        $this->modalSubmitAction(false);

        $this->extraModalWindowAttributes([
            'class' => '[&_.fi-modal-header]:hidden bg-gradient-to-b from-indigo-500/10 from-0% to-indigo-500/0 to-30%',
        ]);
    }

    public function brandLogo(string | Htmlable | Closure | null $logo): static
    {
        $this->brandLogo = $logo;

        return $this;
    }

    public function getBrandLogo(): string | Htmlable | null
    {
        return $this->evaluate($this->brandLogo);
    }

    /**
     * Returns the available billing periods from the loaded data.
     *
     * @return array
     */
    protected function getBilledPeriods(): array
    {
        $products = StripeDataLoader::getProductsData();

        $periods = [];

        foreach ($products as $product) {
            foreach ($product['prices'] as $price) {
                $periods[$price['interval']] = ucfirst($price['interval']);
            }
        }

        return $periods;
    }

    /**
     * Creates a checkout session in Stripe and redirects the user.
     *
     * @param string $billingPeriod
     * @return void
     */
    protected function checkoutUrl(string $billingPeriod): void
    {
        // Gets the current organization (tenant)

        $organization = tenant(Organization::class);

        if (!$organization->stripe_id) {
            throw new \Exception('The organization does not have an associated Stripe ID.');
        }

        // Configure the Stripe API Key
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Get the product and price based on the selected billing period
        $products = StripeDataLoader::getProductsData();
        $priceId  = null;

        foreach ($products as $product) {
            foreach ($product['prices'] as $price) {
                if ($price['interval'] === $billingPeriod) {
                    $priceId = $price['stripe_price_id']; // Ensures it's getting the price ID

                    break 2;
                }
            }
        }

        // Create the checkout session
        $checkoutSession = Session::create([

            'payment_method_types' => ['card'],

            'mode'       => 'subscription',
            'customer'   => $organization->stripe_id, // Make sure the organization has a stripe_id
            'line_items' => [
                [
                    'price'    => $priceId, // Here goes the price object ID
                    'quantity' => 1,
                ],
            ],
            'success_url' => url('/app'), // Redirects to the Filament dashboard
            'cancel_url'  => url('/app'),  // Redirects to the dashboard if payment is canceled

        ]);

        // Redirect to the checkout URL
        redirect()->away($checkoutSession->url);
    }

}
