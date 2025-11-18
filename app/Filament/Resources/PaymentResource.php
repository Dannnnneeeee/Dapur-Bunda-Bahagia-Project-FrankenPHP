<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

     protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Payments';

    protected static ?string $navigationGroup = 'Order Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                 Forms\Components\Section::make('Payment Details')
                ->schema([
                    Forms\Components\Select::make('order_id')
                        ->label('Order')
                        ->options(function () {
                            return \App\Models\Order::query()
                                ->get()
                                ->filter(fn ($order) => !$order->isFullyPaid())
                                ->mapWithKeys(fn ($order) => [
                                    $order->id => "{$order->order_number} - {$order->customer_display_name} (Total: Rp " . number_format($order->total_price, 0, ',', '.') . ")"
                                ]);
                        })
                        ->required()
                        ->searchable()
                        ->live() // ✅
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if ($state) {
                                $order = \App\Models\Order::find($state);
                                $set('amount', $order?->remaining_amount);
                            }
                        })
                        ->helperText('Pilih order yang akan dibayar'),

                    Forms\Components\TextInput::make('amount')
                        ->label('Total to Pay')
                        ->required()
                        ->numeric()
                        ->prefix('Rp')
                        ->disabled() // ✅ Gak bisa diedit
                        ->dehydrated(),
                    Forms\Components\Select::make('payment_method')
                        ->label('Payment Method')
                        ->options([
                            'cash' => 'Cash',
                            'midtrans' => 'Midtrans',
                            'qris' => 'QRIS',
                            'transfer' => 'Bank Transfer',
                        ])
                        ->required()
                        ->default('cash')
                        ->live(),
                        Forms\Components\TextInput::make('cash_received')
                        ->label('Cash Received')
                        ->numeric()
                        ->prefix('Rp')
                        ->visible(fn (Forms\Get $get) => $get('payment_method') === 'cash')
                        ->required(fn (Forms\Get $get) => $get('payment_method') === 'cash')
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                            $amount = $get('amount');
                            if ($state && $amount) {
                                $change = $state - $amount;
                                $set('change', $change >= 0 ? $change : 0);
                            }
                        })
                        ->helperText('Jumlah uang yang diterima dari customer'),

                    // ✅ TAMBAH: Change (auto-calculated)
                    Forms\Components\TextInput::make('change')
                        ->label('Change (Kembalian)')
                        ->numeric()
                        ->prefix('Rp')
                        ->disabled()
                        ->dehydrated()
                        ->visible(fn (Forms\Get $get) => $get('payment_method') === 'cash')
                        ->extraAttributes(['class' => 'font-bold text-xl text-success-600']),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'pending' => 'Pending',
                            'paid' => 'Paid',
                            'failed' => 'Failed',
                            'expired' => 'Expired',
                            'refunded' => 'Refunded',
                        ])
                        ->default('pending')
                        ->required(),

                    Forms\Components\TextInput::make('reference_number')
                        ->label('Reference Number')
                        ->maxLength(255)
                        ->helperText('Bank reference, receipt number, etc'),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_number')
                    ->label('Payment #')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable()
                    ->url(fn (?Payment $record): ?string =>
                    $record ? route('filament.admin.resources.orders.view', ['record' => $record->order_id]) : null
                    )
                    ->color('info'),

                Tables\Columns\TextColumn::make('order.customer_display_name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'midtrans' => 'primary',
                        'qris' => 'warning',
                        'transfer' => 'info',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                    // Di table() method, tambahkan column:

                Tables\Columns\TextColumn::make('cash_received')
                    ->label('Cash Received')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->visible(fn (?Payment $record): bool =>
        $record && $record->payment_method === 'cash'),

                Tables\Columns\TextColumn::make('change')
                    ->label('Change')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                     ->visible(fn (?Payment $record): bool =>
        $record && $record->payment_method === 'cash'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'pending',
                        'danger' => 'failed',
                        'secondary' => 'expired',
                        'info' => 'refunded',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Reference')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('processor.name')
                    ->label('Processed By')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'expired' => 'Expired',
                        'refunded' => 'Refunded',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'midtrans' => 'Midtrans',
                        'qris' => 'QRIS',
                        'transfer' => 'Bank Transfer',
                    ])
                    ->multiple(),

                Tables\Filters\Filter::make('order_id')
                    ->form([
                        Forms\Components\Select::make('value')
                            ->label('Order')
                            ->relationship('order', 'order_number')
                            ->searchable(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['value'], fn ($q) => $q->where('order_id', $data['value']));
                    }),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_paid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Payment $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Payment $record) {
                        $record->markAsPaid();
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Payment marked as paid')
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'view' => Pages\ViewPayment::route('/{record}'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
