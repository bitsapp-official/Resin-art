<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AccountLayout extends Component
{
    public function __construct(
        public ?string $title = null,
        public ?string $headerTitle = 'Your',
        public ?string $headerItalic = 'atelier.',
        public ?string $headerSubtitle = null
    ) {}

    public function render(): View
    {
        return view('account.layout');
    }
}
