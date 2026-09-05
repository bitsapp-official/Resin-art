<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class HomePageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Store Settings';

    protected static ?string $navigationLabel = 'Homepage Content & Story';

    protected static ?string $title = 'Homepage Content & Story Settings';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.home-page-settings';

    protected static ?string $slug = 'home-page-settings';

    // Quote Section
    public ?string $home_quote_text = null;
    public ?string $home_quote_author = null;

    // Story Section ("The House: Made to Order")
    public $home_story_image = null;
    public ?string $home_story_tag = null;
    public ?string $home_story_title = null;
    public ?string $home_story_paragraph1 = null;
    public ?string $home_story_paragraph2 = null;
    public ?string $home_story_badge = null;
    public ?string $home_story_link_text = null;
    public ?string $home_story_link_url = null;

    // Custom Artworks Section
    public ?string $home_custom_tag = null;
    public ?string $home_custom_title = null;
    public ?string $home_custom_desc = null;
    public ?string $home_custom_btn_text = null;
    public ?string $home_custom_btn_url = null;
    public ?string $home_custom_step1_title = null;
    public ?string $home_custom_step1_desc = null;
    public ?string $home_custom_step2_title = null;
    public ?string $home_custom_step2_desc = null;
    public ?string $home_custom_step3_title = null;
    public ?string $home_custom_step3_desc = null;

    public function mount(): void
    {
        $this->form->fill([
            // Quote Banner
            'home_quote_text'       => SiteSetting::get('home_quote_text', 'Resin remembers everything — the hand, the hour, the light in the room.'),
            'home_quote_author'     => SiteSetting::get('home_quote_author', 'Founder & Resin Artist'),

            // The House / Story Section
            'home_story_image'      => SiteSetting::get('home_story_image', 'about/artist_workshop.png'),
            'home_story_tag'        => SiteSetting::get('home_story_tag', 'OUR APPROACH'),
            'home_story_title'      => SiteSetting::get('home_story_title', 'Handcrafted, made to order.'),
            'home_story_paragraph1' => SiteSetting::get('home_story_paragraph1', 'Every piece begins as a slow conversation between wood, resin, and time. We pour by hand, creating unique items that are specifically made to order for your space.'),
            'home_story_paragraph2' => SiteSetting::get('home_story_paragraph2', 'No two rivers are alike. No two skies. That is the point.'),
            'home_story_badge'      => SiteSetting::get('home_story_badge', 'HANDCRAFTED · MADE TO ORDER'),
            'home_story_link_text'  => SiteSetting::get('home_story_link_text', 'Read our story'),
            'home_story_link_url'   => SiteSetting::get('home_story_link_url', '/about'),

            // Custom Made-to-Order Artworks Section
            'home_custom_tag'         => SiteSetting::get('home_custom_tag', 'CUSTOM RESIN ARTWORK'),
            'home_custom_title'       => SiteSetting::get('home_custom_title', 'Have a design in mind? Crafted for your space.'),
            'home_custom_desc'        => SiteSetting::get('home_custom_desc', 'Every custom piece begins with your vision. Simply describe your requirement in your own words, share inspiration or room photos, and our lead artisan connects directly with you on WhatsApp to discuss wood slabs, colors, and pricing.'),
            'home_custom_btn_text'    => SiteSetting::get('home_custom_btn_text', 'Submit Custom Requirement →'),
            'home_custom_btn_url'     => SiteSetting::get('home_custom_btn_url', '/custom'),
            'home_custom_step1_title' => SiteSetting::get('home_custom_step1_title', 'Describe Your Requirement & Photos'),
            'home_custom_step1_desc'  => SiteSetting::get('home_custom_step1_desc', 'Tell us what you have in mind in your own words — whether a dining river table, wall art, clock, or decor — and upload inspiration photos. No complex measurements needed.'),
            'home_custom_step2_title' => SiteSetting::get('home_custom_step2_title', 'Artisan WhatsApp Consultation'),
            'home_custom_step2_desc'  => SiteSetting::get('home_custom_step2_desc', 'Our lead artisan connects directly with you on WhatsApp or Email to discuss wood slab selections, custom color palettes, sizing, and share a transparent all-inclusive quote.'),
            'home_custom_step3_title' => SiteSetting::get('home_custom_step3_title', 'Hand-Poured Craft & Crated Delivery'),
            'home_custom_step3_desc'  => SiteSetting::get('home_custom_step3_desc', 'Once approved, we pour your artwork layer-by-layer with live WhatsApp photo updates, seal with diamond gloss, and deliver safely in insured wooden crates to your doorstep.'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // ── SECTION 1: FOUNDER QUOTE BANNER ──
                Section::make('Founder Brand Quote Banner')
                    ->description('Controls the full-width editorial quote displayed across the middle of the homepage.')
                    ->schema([
                        Textarea::make('home_quote_text')
                            ->label('Quote Text')
                            ->rows(3)
                            ->required()
                            ->placeholder('e.g. Resin remembers everything — the hand, the hour, the light in the room.'),

                        TextInput::make('home_quote_author')
                            ->label('Quote Author / Attribution')
                            ->required()
                            ->placeholder('e.g. Founder & Resin Artist'),
                    ])->collapsible(),

                // ── SECTION 2: THE HOUSE / ATELIER STORY ──
                Section::make('The House: Made to Order (Our Approach & Story Section)')
                    ->description('Customize the editorial narrative section and upload the story photo.')
                    ->schema([
                        FileUpload::make('home_story_image')
                            ->label('Story / Workshop Photo')
                            ->image()
                            ->disk('public')
                            ->directory('homepage')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('4:5')
                            ->helperText('Upload artwork or workshop photo. Recommended aspect ratio 4:5.')
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('home_story_tag')
                                    ->label('Small Top Tag')
                                    ->required()
                                    ->placeholder('e.g. OUR APPROACH'),

                                TextInput::make('home_story_badge')
                                    ->label('Image Glass Badge Text')
                                    ->required()
                                    ->placeholder('e.g. HANDCRAFTED · MADE TO ORDER'),
                            ]),

                        TextInput::make('home_story_title')
                            ->label('Headline Title')
                            ->required()
                            ->placeholder('e.g. Handcrafted, made to order.')
                            ->columnSpanFull(),

                        Textarea::make('home_story_paragraph1')
                            ->label('Primary Description Paragraph')
                            ->rows(3)
                            ->required()
                            ->placeholder('e.g. Every piece begins as a slow conversation between wood, resin, and time...'),

                        Textarea::make('home_story_paragraph2')
                            ->label('Secondary Italic Note (Optional)')
                            ->rows(2)
                            ->placeholder('e.g. No two rivers are alike. No two skies. That is the point.'),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('home_story_link_text')
                                    ->label('CTA Button Text')
                                    ->required()
                                    ->placeholder('e.g. Read our story'),

                                TextInput::make('home_story_link_url')
                                    ->label('CTA Target URL')
                                    ->required()
                                    ->placeholder('e.g. /about'),
                            ]),
                    ])->collapsible(),

                // ── SECTION 3: CUSTOM MADE-TO-ORDER ARTWORKS ──
                Section::make('Custom Made-to-Order Section (Process Card)')
                    ->description('Edit the dark showcase card that explains the 3-step handcrafted custom order process.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('home_custom_tag')
                                    ->label('Top Tag')
                                    ->required()
                                    ->placeholder('e.g. CUSTOM RESIN ARTWORK'),

                                TextInput::make('home_custom_btn_text')
                                    ->label('CTA Button Text')
                                    ->required()
                                    ->placeholder('e.g. Submit Custom Requirement →'),
                            ]),

                        TextInput::make('home_custom_title')
                            ->label('Headline Title')
                            ->required()
                            ->placeholder('e.g. Have a design in mind? Crafted for your space.')
                            ->columnSpanFull(),

                        Textarea::make('home_custom_desc')
                            ->label('Introduction Description Paragraph')
                            ->rows(3)
                            ->required()
                            ->placeholder('e.g. From signature ocean river dining tables to custom fluid wall art panels...'),

                        TextInput::make('home_custom_btn_url')
                            ->label('CTA Target URL')
                            ->required()
                            ->placeholder('e.g. /custom')
                            ->columnSpanFull(),

                        Section::make('3 Process Steps')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Section::make('Step 01')
                                            ->schema([
                                                TextInput::make('home_custom_step1_title')
                                                    ->label('Step 1 Title')
                                                    ->required()
                                                    ->placeholder('Describe Your Requirement & Photos'),
                                                Textarea::make('home_custom_step1_desc')
                                                    ->label('Step 1 Description')
                                                    ->rows(4)
                                                    ->required()
                                                    ->placeholder('Tell us what you have in mind in your own words, and upload inspiration photos...'),
                                            ]),

                                        Section::make('Step 02')
                                            ->schema([
                                                TextInput::make('home_custom_step2_title')
                                                    ->label('Step 2 Title')
                                                    ->required()
                                                    ->placeholder('Artisan WhatsApp Consultation'),
                                                Textarea::make('home_custom_step2_desc')
                                                    ->label('Step 2 Description')
                                                    ->rows(4)
                                                    ->required()
                                                    ->placeholder('Our lead artisan connects directly with you on WhatsApp or Email with wood options and quote...'),
                                            ]),

                                        Section::make('Step 03')
                                            ->schema([
                                                TextInput::make('home_custom_step3_title')
                                                    ->label('Step 3 Title')
                                                    ->required()
                                                    ->placeholder('Hand-Poured Craft & Crated Delivery'),
                                                Textarea::make('home_custom_step3_desc')
                                                    ->label('Step 3 Description')
                                                    ->rows(4)
                                                    ->required()
                                                    ->placeholder('Once approved, we pour layer-by-layer with live updates and deliver safely in crates...'),
                                            ]),
                                    ]),
                            ]),
                    ])->collapsible(),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Quote
        SiteSetting::set('home_quote_text', $data['home_quote_text'], 'homepage', 'Home Quote Text');
        SiteSetting::set('home_quote_author', $data['home_quote_author'], 'homepage', 'Home Quote Author');

        // Story
        $storyImage = $data['home_story_image'] ?? null;
        if (is_array($storyImage)) {
            $storyImage = reset($storyImage) ?: null;
        }
        if ($storyImage) {
            SiteSetting::set('home_story_image', (string) $storyImage, 'homepage', 'Home Story Image');
        }
        SiteSetting::set('home_story_tag', $data['home_story_tag'], 'homepage', 'Home Story Tag');
        SiteSetting::set('home_story_badge', $data['home_story_badge'], 'homepage', 'Home Story Badge');
        SiteSetting::set('home_story_title', $data['home_story_title'], 'homepage', 'Home Story Title');
        SiteSetting::set('home_story_paragraph1', $data['home_story_paragraph1'], 'homepage', 'Home Story Paragraph 1');
        SiteSetting::set('home_story_paragraph2', $data['home_story_paragraph2'] ?? '', 'homepage', 'Home Story Paragraph 2');
        SiteSetting::set('home_story_link_text', $data['home_story_link_text'], 'homepage', 'Home Story Link Text');
        SiteSetting::set('home_story_link_url', $data['home_story_link_url'], 'homepage', 'Home Story Link URL');

        // Custom Made-to-Order Artworks
        SiteSetting::set('home_custom_tag', $data['home_custom_tag'], 'homepage', 'Home Custom Tag');
        SiteSetting::set('home_custom_title', $data['home_custom_title'], 'homepage', 'Home Custom Title');
        SiteSetting::set('home_custom_desc', $data['home_custom_desc'], 'homepage', 'Home Custom Description');
        SiteSetting::set('home_custom_btn_text', $data['home_custom_btn_text'], 'homepage', 'Home Custom Button Text');
        SiteSetting::set('home_custom_btn_url', $data['home_custom_btn_url'], 'homepage', 'Home Custom Button URL');
        SiteSetting::set('home_custom_step1_title', $data['home_custom_step1_title'], 'homepage', 'Home Custom Step 1 Title');
        SiteSetting::set('home_custom_step1_desc', $data['home_custom_step1_desc'], 'homepage', 'Home Custom Step 1 Description');
        SiteSetting::set('home_custom_step2_title', $data['home_custom_step2_title'], 'homepage', 'Home Custom Step 2 Title');
        SiteSetting::set('home_custom_step2_desc', $data['home_custom_step2_desc'], 'homepage', 'Home Custom Step 2 Description');
        SiteSetting::set('home_custom_step3_title', $data['home_custom_step3_title'], 'homepage', 'Home Custom Step 3 Title');
        SiteSetting::set('home_custom_step3_desc', $data['home_custom_step3_desc'], 'homepage', 'Home Custom Step 3 Description');

        $this->form->fill($data);

        Notification::make()
            ->title('Homepage Settings Saved Successfully')
            ->body('Story image, quote, and custom artwork process have been updated.')
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
