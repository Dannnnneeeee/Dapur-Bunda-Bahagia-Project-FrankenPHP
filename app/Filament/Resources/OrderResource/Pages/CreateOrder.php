<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
     protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set created_by ke admin yang sedang login
        $data['created_by'] = auth('admin')->id();

        // Calculate pricing dari items
        $subtotal = 0;
        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
        }

        $data['subtotal'] = $subtotal;
        $data['tax'] = $subtotal * 0.11; // PB1 10%
        $data['discount'] = $data['discount'] ?? 0;
        $data['total_price'] = $data['subtotal'] + $data['tax'] - $data['discount'];

        return $data;
    }

    protected function afterCreate(): void
    {
        // Recalculate untuk pastikan akurat
        $this->record->recalculate();

        // Update stock produk
        foreach ($this->record->items as $item) {
            $product = $item->product;
            $product->decrement('stock', $item->quantity);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
