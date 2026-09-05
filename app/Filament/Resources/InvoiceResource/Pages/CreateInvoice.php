<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\CustomRequest;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $sum = 0;
        if (!empty($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $q = (float)($item['qty'] ?? 1);
                $p = (float)($item['unit_price'] ?? 0);
                $sum += ($q * $p);
            }

            $firstItem = $data['items'][0] ?? [];
            $data['item_title'] = $firstItem['name'] ?? 'Bespoke Resin Artwork';
            $data['item_description'] = $firstItem['description'] ?? null;
        }

        $data['total_amount'] = (float) $sum;

        if (($data['payment_status'] ?? 'fully_paid') === 'fully_paid') {
            $data['paid_amount'] = (float) $sum;
        } else {
            $data['paid_amount'] = 0;
        }

        return $data;
    }

    public function mount(): void
    {
        parent::mount();

        // If request ID passed via query param, pre-fill client data
        $requestId = request()->query('request');
        if ($requestId && $cr = CustomRequest::find($requestId)) {
            $this->form->fill([
                'custom_request_id' => $cr->id,
                'client_name'       => $cr->name,
                'client_email'      => $cr->email,
                'client_phone'      => $cr->whatsapp ?: $cr->phone,
                'client_address'    => $cr->phone ?: '',
                'invoice_number'    => \App\Models\Invoice::generateNumber(),
                'currency_symbol'   => '₹',
                'payment_status'    => 'fully_paid',
                'invoice_date'      => now(),
                'items'             => [
                    [
                        'name'        => 'Bespoke Resin Artwork (' . $cr->public_reference . ')',
                        'description' => $cr->idea_description,
                        'qty'         => 1,
                        'unit_price'  => 0,
                    ]
                ],
            ]);
        }
    }
}
