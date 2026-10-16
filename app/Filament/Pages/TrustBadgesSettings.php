<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
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
    public ?string $badge_1_icon     = null;
    public ?string $badge_1_title    = null;
    public ?string $badge_1_subtitle = null;

    public ?string $badge_2_icon     = null;
    public ?string $badge_2_title    = null;
    public ?string $badge_2_subtitle = null;

    public ?string $badge_3_icon     = null;
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
            'badge_1_icon'              => SiteSetting::get('global_badge_1_icon', 'diamond'),
            'badge_1_title'             => SiteSetting::get('global_badge_1_title', '100% Certified'),
            'badge_1_subtitle'          => SiteSetting::get('global_badge_1_subtitle', 'NATURAL DIAMONDS'),
            'badge_2_icon'              => SiteSetting::get('global_badge_2_icon', 'hallmark'),
            'badge_2_title'             => SiteSetting::get('global_badge_2_title', 'BIS Hallmarked'),
            'badge_2_subtitle'          => SiteSetting::get('global_badge_2_subtitle', '14K - 22K GOLD'),
            'badge_3_icon'              => SiteSetting::get('global_badge_3_icon', 'globe'),
            'badge_3_title'             => SiteSetting::get('global_badge_3_title', 'Free Insured'),
            'badge_3_subtitle'          => SiteSetting::get('global_badge_3_subtitle', 'WORLDWIDE DISPATCH'),

            // Shipping & Policy Tab
            'product_tab_shipping_note' => SiteSetting::get('product_tab_shipping_note', 'Ships within 24–48 business hours with full transit insurance.'),
            'ship_badge_1_title'        => SiteSetting::get('product_tab_ship_badge_1_title', 'Free Ship'),
            'ship_badge_1_subtitle'     => SiteSetting::get('product_tab_ship_badge_1_subtitle', 'WORLDWIDE'),
            'ship_badge_2_title'        => SiteSetting::get('product_tab_ship_badge_2_title', '24–48 hrs'),
            'ship_badge_2_subtitle'     => SiteSetting::get('product_tab_ship_badge_2_subtitle', 'DISPATCH'),
            'ship_badge_3_title'        => SiteSetting::get('product_tab_ship_badge_3_title', 'Insured'),
            'ship_badge_3_subtitle'     => SiteSetting::get('product_tab_ship_badge_3_subtitle', 'PACKAGING'),

            // Bullets
            'bullet_1'                  => SiteSetting::get('product_tab_policy_bullet_1', 'Orders can be modified or cancelled within 3 hours'),
            'bullet_2'                  => SiteSetting::get('product_tab_policy_bullet_2', '100% insured courier delivery with signature confirmation'),
            'bullet_3'                  => SiteSetting::get('product_tab_policy_bullet_3', 'Complimentary ring sizing & lifetime authenticity guarantee'),
        ]);
    }

    public function form(Form $form): Form
    {
        $iconOptions = [
            'diamond'     => '💎 Diamond / Solitaire Brilliance',
            'hallmark'    => '🎖️ BIS Hallmark / Quality Seal',
            'globe'       => '🌍 Worldwide / Global Express',
            'shield'      => '🛡️ Insured Protection / Guarantee',
            'sparkles'    => '✨ Handcrafted Brilliance',
            'truck'       => '🚚 Fast Delivery / Shipping',
            'certificate' => '📜 Official Certification',
        ];

        return $form
            ->schema([
                // ── SECTION 1: TOP 3 TRUST BADGES (UNDER ADD TO CART) ──
                Section::make('Top Trust Badges (Shown under Add to Cart)')
                    ->description('These 3 pill cards appear below the purchase buttons on every product page with custom icons, titles, and subtitles.')
                    ->schema([
                        Section::make('Badge 1')
                            ->schema([
                                Select::make('badge_1_icon')
                                    ->label('Icon')
                                    ->options($iconOptions)
                                    ->default('diamond')
                                    ->required(),
                                TextInput::make('badge_1_title')->label('Title')->placeholder('100% Certified')->required(),
                                TextInput::make('badge_1_subtitle')->label('Subtitle')->placeholder('NATURAL DIAMONDS')->required(),
                            ])->columns(3),

                        Section::make('Badge 2')
                            ->schema([
                                Select::make('badge_2_icon')
                                    ->label('Icon')
                                    ->options($iconOptions)
                                    ->default('hallmark')
                                    ->required(),
                                TextInput::make('badge_2_title')->label('Title')->placeholder('BIS Hallmarked')->required(),
                                TextInput::make('badge_2_subtitle')->label('Subtitle')->placeholder('14K - 22K GOLD')->required(),
                            ])->columns(3),

                        Section::make('Badge 3')
                            ->schema([
                                Select::make('badge_3_icon')
                                    ->label('Icon')
                                    ->options($iconOptions)
                                    ->default('globe')
                                    ->required(),
                                TextInput::make('badge_3_title')->label('Title')->placeholder('Free Insured')->required(),
                                TextInput::make('badge_3_subtitle')->label('Subtitle')->placeholder('WORLDWIDE DISPATCH')->required(),
                            ])->columns(3),
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
                                TextInput::make('ship_badge_1_subtitle')->label('Card 1 Subtitle')->placeholder('WORLDWIDE')->required(),

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
                                    ->placeholder('Orders can be modified or cancelled within 3 hours')
                                    ->required(),
                                TextInput::make('bullet_2')
                                    ->label('Bullet 2 (Return Rule)')
                                    ->placeholder('100% insured courier delivery with signature confirmation')
                                    ->required(),
                                TextInput::make('bullet_3')
                                    ->label('Bullet 3 (Damage Guarantee)')
                                    ->placeholder('Complimentary ring sizing & lifetime authenticity guarantee')
                                    ->required(),
                            ]),
                    ])->collapsible(),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Top Badges
        SiteSetting::set('global_badge_1_icon',      $data['badge_1_icon'],      'general', 'Global Trust Badge 1 Icon');
        SiteSetting::set('global_badge_1_title',     $data['badge_1_title'],     'general', 'Global Trust Badge 1 Title');
        SiteSetting::set('global_badge_1_subtitle',  $data['badge_1_subtitle'],  'general', 'Global Trust Badge 1 Subtitle');

        SiteSetting::set('global_badge_2_icon',      $data['badge_2_icon'],      'general', 'Global Trust Badge 2 Icon');
        SiteSetting::set('global_badge_2_title',     $data['badge_2_title'],     'general', 'Global Trust Badge 2 Title');
        SiteSetting::set('global_badge_2_subtitle',  $data['badge_2_subtitle'],  'general', 'Global Trust Badge 2 Subtitle');

        SiteSetting::set('global_badge_3_icon',      $data['badge_3_icon'],      'general', 'Global Trust Badge 3 Icon');
        SiteSetting::set('global_badge_3_title',     $data['badge_3_title'],     'general', 'Global Trust Badge 3 Title');
        SiteSetting::set('global_badge_3_subtitle',  $data['badge_3_subtitle'],  'general', 'Global Trust Badge 3 Subtitle');

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
