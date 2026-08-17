<?php

namespace App\Livewire\Admin;

use App\Services\QrCodeGenerator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Administration — QR Code')]
class QrCode extends Component
{
    /**
     * Render a QR code pointing to the site's current base URL. Generated fresh on every
     * request (never cached to disk) so it always matches the domain the app is served
     * from, whatever that ends up being.
     */
    public function render(QrCodeGenerator $generator): View
    {
        $url = url('/');

        return view('livewire.admin.qr-code', [
            'url' => $url,
            'svg' => $generator->svg($url),
        ]);
    }
}
