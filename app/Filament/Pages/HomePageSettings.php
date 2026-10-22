<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
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

    // Blog / Editorial Journal Section
    public bool $home_blog_enabled = true;
    public ?string $home_blog_tag = null;
    public ?string $home_blog_title = null;
    public ?string $home_blog_subtitle = null;
    public ?int $home_blog_count = 3;

    public function mount(): void
    {
        $this->form->fill([
            // Quote Banner
            'home_quote_text'       => SiteSetting::get('home_quote_text', 'A diamond does not merely capture light — it immortalises the moments that define your life.'),
            'home_quote_author'     => SiteSetting::get('home_quote_author', 'Master Jeweller · Solitaire Atelier'),

            // The Atelier / Story Section
            'home_story_image'      => SiteSetting::get('home_story_image', 'images/hero/jewellery_necklace_banner.jpg'),
            'home_story_tag'        => SiteSetting::get('home_story_tag', 'THE ATELIER HERITAGE'),
            'home_story_title'      => SiteSetting::get('home_story_title', 'Heirlooms born of gold, fire, and light.'),
            'home_story_paragraph1' => SiteSetting::get('home_story_paragraph1', 'Every piece in our atelier is an intimate dialogue between master craftsmanship and certified natural diamonds. Forged in solid 18KT gold, calibrated to microscopic tolerances, and designed to be worn and passed down for a lifetime.'),
            'home_story_paragraph2' => SiteSetting::get('home_story_paragraph2', 'Certified by premier international gemmological laboratories and hallmarked for purity.'),
            'home_story_badge'      => SiteSetting::get('home_story_badge', '100% BIS HALLMARKED · IGI CERTIFIED'),
            'home_story_link_text'  => SiteSetting::get('home_story_link_text', 'Discover our craftsmanship'),
            'home_story_link_url'   => SiteSetting::get('home_story_link_url', '/about'),

            // Bespoke Fine Jewellery Consultation Section
            'home_custom_tag'         => SiteSetting::get('home_custom_tag', 'BESPOKE JEWELLERY ATELIER'),
            'home_custom_title'       => SiteSetting::get('home_custom_title', 'Have a dream design? Handcrafted exclusively for you.'),
            'home_custom_desc'        => SiteSetting::get('home_custom_desc', 'From custom solitaire engagement rings to bridal heirlooms, bring your vision to life. Share your reference sketch or photo, choose your preferred gold karat and certified diamonds, and collaborate one-on-one with our master gemologists.'),
            'home_custom_btn_text'    => SiteSetting::get('home_custom_btn_text', 'Commission Custom Jewellery →'),
            'home_custom_btn_url'     => SiteSetting::get('home_custom_btn_url', '/custom'),
            'home_custom_step1_title' => SiteSetting::get('home_custom_step1_title', 'Design Consultation & Sketch'),
            'home_custom_step1_desc'  => SiteSetting::get('home_custom_step1_desc', 'Share your design inspiration, ring size, or metal tone preference (Yellow, White, or Rose Gold). No prior jewellery expertise needed — our specialists guide you through every cut and setting.'),
            'home_custom_step2_title' => SiteSetting::get('home_custom_step2_title', '3D CAD & Diamond Selection'),
            'home_custom_step2_desc'  => SiteSetting::get('home_custom_step2_desc', 'Our master designers render a photorealistic 3D CAD preview. You inspect stone proportions, diamond clarity, and receive an all-inclusive transparent quote before casting.'),
            'home_custom_step3_title' => SiteSetting::get('home_custom_step3_title', 'Hand Setting & Insured Delivery'),
            'home_custom_step3_desc'  => SiteSetting::get('home_custom_step3_desc', 'Cast in solid gold and hand-set under 10x magnification by master setters. Shipped in tamper-evident insured packaging with independent laboratory certification to your door.'),

            // Blog / Editorial Journal Section
            'home_blog_enabled'       => (bool) SiteSetting::get('home_blog_enabled', true),
            'home_blog_tag'           => SiteSetting::get('home_blog_tag', 'THE ATELIER JOURNAL'),
            'home_blog_title'         => SiteSetting::get('home_blog_title', 'Stories of Brilliance & Craft'),
            'home_blog_subtitle'      => SiteSetting::get('home_blog_subtitle', 'Master guides on solitaires, 4Cs of diamonds, hallmarked gold, and fine jewellery styling.'),
            'home_blog_count'         => (int) SiteSetting::get('home_blog_count', 3),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // ── SECTION 1: FOUNDER QUOTE BANNER ──
                Section::make('Atelier Philosophy Quote Banner')
                    ->description('Controls the full-width editorial quote displayed across the homepage.')
                    ->schema([
                        Textarea::make('home_quote_text')
                            ->label('Quote Text')
                            ->rows(3)
                            ->required()
                            ->placeholder('e.g. A diamond does not merely capture light — it immortalises the moments that define your life.'),

                        TextInput::make('home_quote_author')
                            ->label('Quote Author / Attribution')
                            ->required()
                            ->placeholder('e.g. Master Jeweller · Solitaire Atelier'),
                    ])->collapsible(),

                // ── SECTION 2: THE HOUSE / ATELIER STORY ──
                Section::make('The Atelier Heritage (Our Craftsmanship & Story Section)')
                    ->description('Customize the editorial narrative section and upload the story photo.')
                    ->schema([
                        FileUpload::make('home_story_image')
                            ->label('Story / Atelier Photo')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                            ->maxSize(5120)
                            ->disk('public')
                            ->directory('homepage')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('4:5')
                            ->helperText('Upload jewelry craftsmanship photo. Max 5 MB. Recommended aspect ratio 4:5.')
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('home_story_tag')
                                    ->label('Small Top Tag')
                                    ->required()
                                    ->placeholder('e.g. THE ATELIER HERITAGE'),

                                TextInput::make('home_story_badge')
                                    ->label('Image Glass Badge Text')
                                    ->required()
                                    ->placeholder('e.g. 100% BIS HALLMARKED · IGI CERTIFIED'),
                            ]),

                        TextInput::make('home_story_title')
                            ->label('Headline Title')
                            ->required()
                            ->placeholder('e.g. Heirlooms born of gold, fire, and light.')
                            ->columnSpanFull(),

                        Textarea::make('home_story_paragraph1')
                            ->label('Primary Description Paragraph')
                            ->rows(3)
                            ->required()
                            ->placeholder('e.g. Every piece in our atelier is an intimate dialogue between master craftsmanship and certified natural diamonds...'),

                        Textarea::make('home_story_paragraph2')
                            ->label('Secondary Italic Note (Optional)')
                            ->rows(2)
                            ->placeholder('e.g. Certified by premier international gemmological laboratories and hallmarked for purity.'),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('home_story_link_text')
                                    ->label('CTA Button Text')
                                    ->required()
                                    ->placeholder('e.g. Discover our craftsmanship'),

                                TextInput::make('home_story_link_url')
                                    ->label('CTA Target URL')
                                    ->required()
                                    ->placeholder('e.g. /about'),
                            ]),
                    ])->collapsible(),

                // ── SECTION 3: BESPOKE FINE JEWELLERY CONCIERGE ──
                Section::make('Bespoke Jewellery Concierge (Process Card)')
                    ->description('Edit the dark showcase card that explains the 3-step handcrafted bespoke jewelry order process.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('home_custom_tag')
                                    ->label('Top Tag')
                                    ->required()
                                    ->placeholder('e.g. BESPOKE JEWELLERY ATELIER'),

                                TextInput::make('home_custom_btn_text')
                                    ->label('CTA Button Text')
                                    ->required()
                                    ->placeholder('e.g. Commission Custom Jewellery →'),
                            ]),

                        TextInput::make('home_custom_title')
                            ->label('Headline Title')
                            ->required()
                            ->placeholder('e.g. Have a dream design? Handcrafted exclusively for you.')
                            ->columnSpanFull(),

                        Textarea::make('home_custom_desc')
                            ->label('Introduction Description Paragraph')
                            ->rows(3)
                            ->required()
                            ->placeholder('e.g. From custom solitaire engagement rings to bridal heirlooms...'),

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
                                                    ->placeholder('Design Consultation & Sketch'),
                                                Textarea::make('home_custom_step1_desc')
                                                    ->label('Step 1 Description')
                                                    ->rows(4)
                                                    ->required()
                                                    ->placeholder('Share your design inspiration, ring size, or metal tone preference...'),
                                            ]),

                                        Section::make('Step 02')
                                            ->schema([
                                                TextInput::make('home_custom_step2_title')
                                                    ->label('Step 2 Title')
                                                    ->required()
                                                    ->placeholder('3D CAD & Diamond Selection'),
                                                Textarea::make('home_custom_step2_desc')
                                                    ->label('Step 2 Description')
                                                    ->rows(4)
                                                    ->required()
                                                    ->placeholder('Our master designers render a photorealistic 3D CAD preview...'),
                                            ]),

                                        Section::make('Step 03')
                                            ->schema([
                                                TextInput::make('home_custom_step3_title')
                                                    ->label('Step 3 Title')
                                                    ->required()
                                                    ->placeholder('Hand Setting & Insured Delivery'),
                                                Textarea::make('home_custom_step3_desc')
                                                    ->label('Step 3 Description')
                                                    ->rows(4)
                                                    ->required()
                                                    ->placeholder('Cast in solid gold and hand-set under 10x magnification by master setters...'),
                                            ]),
                                    ]),
                            ]),
                    ])->collapsible(),

                // ── SECTION 4: EDITORIAL JOURNAL & BLOG SHOWCASE ──
                Section::make('Editorial Journal & Blog Showcase')
                    ->description('Control the articles & journal card showcase section displayed on the homepage.')
                    ->schema([
                        Toggle::make('home_blog_enabled')
                            ->label('Show Blog / Journal Section on Homepage')
                            ->helperText('Turn this toggle ON to display the luxury editorial articles cards on the homepage')
                            ->default(true),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('home_blog_tag')
                                    ->label('Small Top Tag')
                                    ->required()
                                    ->placeholder('e.g. THE ATELIER JOURNAL'),

                                Select::make('home_blog_count')
                                    ->label('Showcase Articles Count')
                                    ->helperText('Number of articles to showcase on homepage (Max 3)')
                                    ->options([
                                        1 => '1 Article Showcase',
                                        2 => '2 Articles Showcase',
                                        3 => '3 Articles Showcase (Recommended)',
                                    ])
                                    ->default(3)
                                    ->required(),
                            ]),

                        TextInput::make('home_blog_title')
                            ->label('Headline Title')
                            ->required()
                            ->placeholder('e.g. Stories of Brilliance & Craft'),

                        Textarea::make('home_blog_subtitle')
                            ->label('Subtitle / Description')
                            ->rows(2)
                            ->required()
                            ->placeholder('e.g. Master guides on solitaires, 4Cs of diamonds, hallmarked gold, and fine jewellery styling.'),
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

        // Blog / Editorial Journal Section
        SiteSetting::set('home_blog_enabled', !empty($data['home_blog_enabled']) ? '1' : '0', 'homepage', 'Home Blog Showcase Enabled');
        SiteSetting::set('home_blog_tag', $data['home_blog_tag'], 'homepage', 'Home Blog Tag');
        SiteSetting::set('home_blog_title', $data['home_blog_title'], 'homepage', 'Home Blog Title');
        SiteSetting::set('home_blog_subtitle', $data['home_blog_subtitle'], 'homepage', 'Home Blog Subtitle');
        SiteSetting::set('home_blog_count', (string) ($data['home_blog_count'] ?? 3), 'homepage', 'Home Blog Count');

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
