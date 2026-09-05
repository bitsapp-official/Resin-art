<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class TrustBadgesSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Store Settings';

    protected static ?string $navigationLabel = 'Product Badges & Policy Tab';

    protected static ?string $title = 'Product Page Badges & Shipping Policy Tab';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.trust-badges-settings';

    protected static ?string $slug = 'trust-badges-settings';

    // Top Trust Badges
    public ?string $badge_1_title    = null;
    public ?string $badge_1_subtitle = null;
    public ?string $badge_2_title    = null;
    public ?string $badge_2_subtitle = null;
    public ?string $badge_3_title    = null;
    public ?string $badge_3_subtitle = null;

    // Shipping & Policy Tab Fields
    public ?string $product_tab_shipping_note  = null;
    public ?string $ship_badge_1_title         = null;
    public ?string $ship_badge_1_subtitle      = null;
    public ?string $ship_badge_2_title         = null;
    public ?string $ship_badge_2_subtitle      = null;
    public ?string $ship_badge_3_title         = null;
    public ?string $ship_badge_3_subtitle      = null;

    // Cancellation & Return Bullets
    public ?string $bullet_1                   = null;
    public ?string $bullet_2                   = null;
    public ?string $bullet_3                   = null;

    public function mount(): void
    {
        $this->form->fill([
            // Top Trust Badges
            'badge_1_title'             => SiteSetting::get('global_badge_1_title', 'Hand-Poured'),
            'badge_1_subtitle'          => SiteSetting::get('global_badge_1_subtitle', '100% HANDMADE'),
            'badge_2_title'             => SiteSetting::get('global_badge_2_title', 'Atelier Piece'),
            'badge_2_subtitle'          => SiteSetting::get('global_badge_2_subtitle', 'ORIGINAL ART'),
            'badge_3_title'             => SiteSetting::get('global_badge_3_title', 'Free Express'),
            'badge_3_subtitle'          => SiteSetting::get('global_badge_3_subtitle', 'PAN INDIA SHIP'),

            // Shipping & Policy Tab
            'product_tab_shipping_note' => SiteSetting::get('product_tab_shipping_note', 'Ships within 24–48 business hours.'),
            'ship_badge_1_title'        => SiteSetting::get('product_tab_ship_badge_1_title', 'Free Ship'),
            'ship_badge_1_subtitle'     => SiteSetting::get('product_tab_ship_badge_1_subtitle', 'PAN INDIA'),
            'ship_badge_2_title'        => SiteSetting::get('product_tab_ship_badge_2_title', '24–48 hrs'),
            'ship_badge_2_subtitle'     => SiteSetting::get('product_tab_ship_badge_2_subtitle', 'DISPATCH'),
            'ship_badge_3_title'        => SiteSetting::get('product_tab_ship_badge_3_title', 'Insured'),
            'ship_badge_3_subtitle'     => SiteSetting::get('product_tab_ship_badge_3_subtitle', 'PACKAGING'),

            // Bullets
            'bullet_1'                  => SiteSetting::get('product_tab_policy_bullet_1', '3 hours to cancel after placing order'),
            'bullet_2'                  => SiteSetting::get('product_tab_policy_bullet_2', 'No returns once dispatched (handcrafted / made-to-order)'),
            'bullet_3'                  => SiteSetting::get('product_tab_policy_bullet_3', 'Damage claims accepted within 48 hrs of delivery'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // ── SECTION 1: TOP 3 TRUST BADGES (UNDER ADD TO CART) ──
                Section::make('Top Trust Badges (Shown under Add to Cart)')
                    ->description('These 3 pill cards appear below the purchase buttons on every product page.')
                    ->schema([
                        Section::make('Badge 1')
                            ->schema([
                                TextInput::make('badge_1_title')->label('Title')->placeholder('Hand-Poured')->required(),
                                TextInput::make('badge_1_subtitle')->label('Subtitle')->placeholder('100% HANDMADE')->required(),
                            ])->columns(2),

                        Section::make('Badge 2')
                            ->schema([
                                TextInput::make('badge_2_title')->label('Title')->placeholder('Atelier Piece')->required(),
                                TextInput::make('badge_2_subtitle')->label('Subtitle')->placeholder('ORIGINAL ART')->required(),
                            ])->columns(2),

                        Section::make('Badge 3')
                            ->schema([
                                TextInput::make('badge_3_title')->label('Title')->placeholder('Free Express')->required(),
                                TextInput::make('badge_3_subtitle')->label('Subtitle')->placeholder('PAN INDIA SHIP')->required(),
                            ])->columns(2),
                    ])->collapsible(),

                // ── SECTION 2: SHIPPING & POLICY TAB DETAILS ──
                Section::make('Shipping & Policy Tab Details (Product Page Tab)')
                    ->description('Manage all texts and mini cards shown inside the "SHIPPING & POLICY" tab on product pages.')
                    ->schema([
                        TextInput::make('product_tab_shipping_note')
                            ->label('Shipping Lead Line')
                            ->placeholder('e.g. Ships within 24–48 business hours.')
                            ->required(),

                        Section::make('3 Shipping Highlight Cards (Inside Tab)')
                            ->schema([
                                TextInput::make('ship_badge_1_title')->label('Card 1 Title')->placeholder('Free Ship')->required(),
                                TextInput::make('ship_badge_1_subtitle')->label('Card 1 Subtitle')->placeholder('PAN INDIA')->required(),

                                TextInput::make('ship_badge_2_title')->label('Card 2 Title')->placeholder('24–48 hrs')->required(),
                                TextInput::make('ship_badge_2_subtitle')->label('Card 2 Subtitle')->placeholder('DISPATCH')->required(),

                                TextInput::make('ship_badge_3_title')->label('Card 3 Title')->placeholder('Insured')->required(),
                                TextInput::make('ship_badge_3_subtitle')->label('Card 3 Subtitle')->placeholder('PACKAGING')->required(),
                            ])->columns(2),

                        Section::make('Cancellation & Return Policy Summary Bullets')
                            ->description('These 3 bullet points explain your shop policies inside the product tab.')
                            ->schema([
                                TextInput::make('bullet_1')
                                    ->label('Bullet 1 (Cancellation Window)')
                                    ->placeholder('3 hours to cancel after placing order')
                                    ->required(),
                                TextInput::make('bullet_2')
                                    ->label('Bullet 2 (Return Rule)')
                                    ->placeholder('No returns once dispatched (handcrafted / made-to-order)')
                                    ->required(),
                                TextInput::make('bullet_3')
                                    ->label('Bullet 3 (Damage Guarantee)')
                                    ->placeholder('Damage claims accepted within 48 hrs of delivery')
                                    ->required(),
                            ]),
                    ])->collapsible(),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Top Badges
        SiteSetting::set('global_badge_1_title',    $data['badge_1_title'],    'general', 'Global Trust Badge 1 Title');
        SiteSetting::set('global_badge_1_subtitle',  $data['badge_1_subtitle'], 'general', 'Global Trust Badge 1 Subtitle');
        SiteSetting::set('global_badge_2_title',    $data['badge_2_title'],    'general', 'Global Trust Badge 2 Title');
        SiteSetting::set('global_badge_2_subtitle',  $data['badge_2_subtitle'], 'general', 'Global Trust Badge 2 Subtitle');
        SiteSetting::set('global_badge_3_title',    $data['badge_3_title'],    'general', 'Global Trust Badge 3 Title');
        SiteSetting::set('global_badge_3_subtitle',  $data['badge_3_subtitle'], 'general', 'Global Trust Badge 3 Subtitle');

        // Shipping Tab Line
        SiteSetting::set('product_tab_shipping_note', $data['product_tab_shipping_note'], 'general', 'Product Tab Shipping Note');

        // Shipping Tab Cards
        SiteSetting::set('product_tab_ship_badge_1_title',    $data['ship_badge_1_title'],    'general', 'Product Tab Ship Badge 1 Title');
        SiteSetting::set('product_tab_ship_badge_1_subtitle', $data['ship_badge_1_subtitle'], 'general', 'Product Tab Ship Badge 1 Subtitle');
        SiteSetting::set('product_tab_ship_badge_2_title',    $data['ship_badge_2_title'],    'general', 'Product Tab Ship Badge 2 Title');
        SiteSetting::set('product_tab_ship_badge_2_subtitle', $data['ship_badge_2_subtitle'], 'general', 'Product Tab Ship Badge 2 Subtitle');
        SiteSetting::set('product_tab_ship_badge_3_title',    $data['ship_badge_3_title'],    'general', 'Product Tab Ship Badge 3 Title');
        SiteSetting::set('product_tab_ship_badge_3_subtitle', $data['ship_badge_3_subtitle'], 'general', 'Product Tab Ship Badge 3 Subtitle');

        // Bullets
        SiteSetting::set('product_tab_policy_bullet_1', $data['bullet_1'], 'general', 'Product Tab Policy Bullet 1');
        SiteSetting::set('product_tab_policy_bullet_2', $data['bullet_2'], 'general', 'Product Tab Policy Bullet 2');
        SiteSetting::set('product_tab_policy_bullet_3', $data['bullet_3'], 'general', 'Product Tab Policy Bullet 3');

        $this->form->fill($data);

        Notification::make()
            ->title('All Settings Saved!')
            ->body('Product page badges, shipping lead line, mini cards, and policy bullets updated successfully.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save All Settings')
                ->icon('heroicon-o-check-circle')
                ->action('save'),
        ];
    }
}
