<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class InvoiceGstSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Store Settings';

    protected static ?string $navigationLabel = 'Invoice & GST Settings';

    protected static ?string $title = 'Tax Invoice & GST Configuration';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.invoice-gst-settings';

    protected static ?string $slug = 'invoice-gst-settings';

    // Form attributes
    public ?string $invoice_brand_name = null;
    public ?string $invoice_brand_tagline = null;
    public ?string $invoice_address = null;
    public ?string $invoice_email = null;
    public ?string $invoice_phone = null;
    public ?string $invoice_website = null;
    public ?string $invoice_gstin = null;
    public ?string $invoice_tax_rate = null;
    public ?string $invoice_tax_label = null;
    public ?bool $invoice_show_tax = true;
    public ?string $invoice_footer_note = null;
    public ?string $invoice_authenticity_note = null;

    public function mount(): void
    {
        $this->form->fill([
            'invoice_brand_name'        => SiteSetting::get('invoice_brand_name', 'Maison Résine'),
            'invoice_brand_tagline'     => SiteSetting::get('invoice_brand_tagline', 'Haute Résine Atelier · Art Contemporain'),
            'invoice_address'           => SiteSetting::get('invoice_address', '14 rue des Étoiles, 33000 Bordeaux, France'),
            'invoice_email'             => SiteSetting::get('invoice_email', 'atelier@maisonresine.com'),
            'invoice_phone'             => SiteSetting::get('invoice_phone', '+91 98201 45678'),
            'invoice_website'           => SiteSetting::get('invoice_website', 'www.maisonresine.com'),
            'invoice_gstin'             => SiteSetting::get('invoice_gstin', ''),
            'invoice_tax_rate'          => SiteSetting::get('invoice_tax_rate', '5'),
            'invoice_tax_label'         => SiteSetting::get('invoice_tax_label', 'Estimated Taxes (GST 5%)'),
            'invoice_show_tax'          => (bool) SiteSetting::get('invoice_show_tax', '1'),
            'invoice_footer_note'       => SiteSetting::get('invoice_footer_note', 'Thank you for choosing Maison Résine. Handcrafted resin art and bespoke custom pieces.'),
            'invoice_authenticity_note' => SiteSetting::get('invoice_authenticity_note', 'Every artwork is accompanied by an embossed physical Certificate of Authenticity.'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Atelier Business Profile (Appears on Invoice Header)')
                    ->description('Configure the company name, address, and contact details shown on all customer tax invoices.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('invoice_brand_name')
                                    ->label('Brand / Company Name')
                                    ->required()
                                    ->placeholder('e.g. Maison Résine'),

                                TextInput::make('invoice_brand_tagline')
                                    ->label('Brand Subtitle / Tagline')
                                    ->placeholder('e.g. Haute Résine Atelier · Art Contemporain'),

                                TextInput::make('invoice_email')
                                    ->label('Official Billing Email')
                                    ->email()
                                    ->required()
                                    ->placeholder('e.g. atelier@maisonresine.com'),

                                TextInput::make('invoice_phone')
                                    ->label('Official Contact / Support Phone')
                                    ->placeholder('e.g. +91 98201 45678'),

                                TextInput::make('invoice_website')
                                    ->label('Website URL')
                                    ->placeholder('e.g. www.maisonresine.com')
                                    ->columnSpan(2),

                                Textarea::make('invoice_address')
                                    ->label('Atelier Registered Address')
                                    ->rows(2)
                                    ->required()
                                    ->placeholder('e.g. 14 rue des Étoiles, 33000 Bordeaux, France')
                                    ->columnSpan(2),
                            ]),
                    ]),

                Section::make('GST & Taxation Settings')
                    ->description('Set your GSTIN / Tax ID and the default GST tax rate applied during checkout and printed on invoices.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('invoice_gstin')
                                    ->label('GSTIN / Tax Registration Number (Optional)')
                                    ->helperText('Leave empty if not registered for GST yet. If filled, will be displayed on all Tax Invoices.')
                                    ->placeholder('e.g. 27AABCM8291F1ZQ'),

                                TextInput::make('invoice_tax_rate')
                                    ->label('Default GST Tax Rate (%)')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default('5')
                                    ->required()
                                    ->helperText('Tax percentage calculated on cart subtotal at checkout (e.g. 0, 5, 12, 18).'),

                                TextInput::make('invoice_tax_label')
                                    ->label('Tax Display Label')
                                    ->default('Estimated Taxes (GST 5%)')
                                    ->placeholder('e.g. Estimated Taxes (GST 5%)')
                                    ->columnSpan(2),

                                Toggle::make('invoice_show_tax')
                                    ->label('Enable Tax Calculation & Display on Invoices')
                                    ->default(true)
                                    ->helperText('Toggle whether tax row is calculated and displayed on checkout and invoices.')
                                    ->columnSpan(2),
                            ]),
                    ]),

                Section::make('Invoice Footer & Authenticity Disclaimer')
                    ->schema([
                        Textarea::make('invoice_authenticity_note')
                            ->label('Authenticity Box Note')
                            ->rows(2)
                            ->default('Every artwork is accompanied by an embossed physical Certificate of Authenticity.'),

                        Textarea::make('invoice_footer_note')
                            ->label('Footer Disclaimer Note')
                            ->rows(2)
                            ->default('Thank you for choosing Maison Résine. This is a computer-generated tax invoice.'),
                    ]),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Invoice Settings')
                ->submit('save')
                ->icon('heroicon-m-check'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        SiteSetting::set('invoice_brand_name', $data['invoice_brand_name'], 'invoice', 'Brand Name');
        SiteSetting::set('invoice_brand_tagline', $data['invoice_brand_tagline'], 'invoice', 'Brand Tagline');
        SiteSetting::set('invoice_address', $data['invoice_address'], 'invoice', 'Atelier Address');
        SiteSetting::set('invoice_email', $data['invoice_email'], 'invoice', 'Billing Email');
        SiteSetting::set('invoice_phone', $data['invoice_phone'], 'invoice', 'Contact Phone');
        SiteSetting::set('invoice_website', $data['invoice_website'], 'invoice', 'Website URL');
        SiteSetting::set('invoice_gstin', trim($data['invoice_gstin'] ?? ''), 'invoice', 'GSTIN Number');
        SiteSetting::set('invoice_tax_rate', $data['invoice_tax_rate'] ?? '5', 'invoice', 'GST Tax Rate %');
        SiteSetting::set('invoice_tax_label', $data['invoice_tax_label'] ?? 'Estimated Taxes (GST 5%)', 'invoice', 'Tax Display Label');
        SiteSetting::set('invoice_show_tax', $data['invoice_show_tax'] ? '1' : '0', 'invoice', 'Enable Tax');
        SiteSetting::set('invoice_footer_note', $data['invoice_footer_note'], 'invoice', 'Footer Note');
        SiteSetting::set('invoice_authenticity_note', $data['invoice_authenticity_note'], 'invoice', 'Authenticity Note');

        Notification::make()
            ->title('Invoice & GST Settings Saved Successfully')
            ->success()
            ->send();
    }
}
