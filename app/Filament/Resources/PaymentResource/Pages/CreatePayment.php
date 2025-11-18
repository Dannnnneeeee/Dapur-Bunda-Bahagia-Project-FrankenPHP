<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
{
    // Auto-set processed_by
    $data['processed_by'] = auth('admin')->id();

    // Kalau payment method cash, auto-set paid_at
    if ($data['payment_method'] === 'cash' && $data['status'] === 'paid') {
        $data['paid_at'] = now();
    }

    return $data;
}

 protected function afterCreate(): void
    {
        // Update order payment status
        if ($this->record->status === 'paid') {
            $this->record->order->checkPaymentStatus();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
