<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Recalculate pricing
        $subtotal = 0;
        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
        }

        $data['subtotal'] = $subtotal;
        $data['tax'] = $subtotal * 0.11;
        $data['discount'] = $data['discount'] ?? 0;
        $data['total_price'] = $data['subtotal'] + $data['tax'] - $data['discount'];

        return $data;
    }

    protected function afterSave(): void
    {
        // Recalculate untuk pastikan akurat
        $this->record->recalculate();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
