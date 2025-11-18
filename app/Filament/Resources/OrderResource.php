<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use App\Models\Product;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

     protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Orders';

    protected static ?string $navigationGroup = 'Order Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Customer Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Registered Customer')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Pilih customer yang sudah terdaftar, atau isi manual untuk walk-in'),

                        Forms\Components\TextInput::make('customer_name')
                            ->label('Walk-in Customer Name')
                            ->maxLength(255)
                            ->helperText('Untuk customer tanpa akun'),

                        Forms\Components\TextInput::make('customer_phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(20),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Order Details')
                    ->schema([
                        Forms\Components\Select::make('order_type')
                            ->label('Order Type')
                            ->options([
                                'dine_in' => 'Dine In',
                                'takeaway' => 'Takeaway',
                            ])
                            ->default('dine_in')
                            ->required()
                            ->live(),

                        Forms\Components\TextInput::make('table_number')
                            ->label('Table Number')
                            ->maxLength(10)
                            ->visible(fn (Forms\Get $get) => $get('order_type') === 'dine_in'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Order Notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Order Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->options(Product::available()->pluck('name', 'id'))
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('product_name', $product->name);
                                            $set('price', $product->final_price);
                                        }
                                    })
                                    ->columnSpan(3),

                                Forms\Components\TextInput::make('product_name')
                                    ->label('Product Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(3),

                                Forms\Components\TextInput::make('price')
                                    ->label('Price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Qty')
                                    ->required()
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('notes')
                                    ->label('Item Notes')
                                    ->placeholder('Less sugar, extra ice, etc')
                                    ->columnSpan(3),
                            ])
                            ->columns(12)
                            ->defaultItems(1)
                            ->addActionLabel('Add Item')
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated()
                            ->default(0),

                        Forms\Components\TextInput::make('tax')
                            ->label('Tax (10%)')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated()
                            ->default(0),

                        Forms\Components\TextInput::make('discount')
                            ->label('Discount')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),

                        Forms\Components\TextInput::make('total_price')
                            ->label('Total Price')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                    ])
                    ->columns(4),

                Forms\Components\Section::make('Payment & Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Order Status')
                            ->options([
                                'pending' => 'Pending',
                                'preparing' => 'Preparing',
                                'ready' => 'Ready',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('customer_display_name')
                    ->label('Customer')
                    ->searchable(['customer_name', 'user.name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'dine_in' => 'info',
                        'takeaway' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'dine_in' => 'Dine In',
                        'takeaway' => 'Takeaway',
                    }),

                Tables\Columns\TextColumn::make('table_number')
                    ->label('Table')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_status_text')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (Order $record) => $record->payment_status_color),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'preparing' => 'info',
                        'ready' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('payments_count')
                    ->label('Payments')
                    ->counts('payments')
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Time')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'preparing' => 'Preparing',
                        'ready' => 'Ready',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('is_paid')
                    ->label('Payment Status')
                    ->placeholder('All Orders')
                    ->trueLabel('Paid')
                    ->falseLabel('Unpaid')
                    ->queries(
                        true: fn ($query) => $query->paid(),
                        false: fn ($query) => $query->unpaid(),
                    ),

                Tables\Filters\SelectFilter::make('order_type')
                    ->options([
                        'dine_in' => 'Dine In',
                        'takeaway' => 'Takeaway',
                    ]),

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
                Tables\Actions\Action::make('add_payment')
                    ->label('Add Payment')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Order $record) => !$record->isFullyPaid())
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->default(fn (Order $record) => $record->remaining_amount),
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

                            // ✅ TAMBAH: Cash Received
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
                            }),

                        // ✅ TAMBAH: Change
                        Forms\Components\TextInput::make('change')
                            ->label('Change (Kembalian)')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated()
                            ->visible(fn (Forms\Get $get) => $get('payment_method') === 'cash')
                            ->extraAttributes(['class' => 'font-bold text-xl']),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes'),
                    ])
                    ->action(function (Order $record, array $data) {
                        $payment = $record->payments()->create([
                            'amount' => $data['amount'],
                            'cash_received' => $data['cash_received'] ?? null,  // ✅ Tambah
                            'change' => $data['change'] ?? null,                // ✅ Tambah
                            'payment_method' => $data['payment_method'],
                            'status' => $data['payment_method'] === 'cash' ? 'paid' : 'pending',
                            'notes' => $data['notes'] ?? null,
                            'processed_by' => auth('admin')->id()??null,
                            'paid_at' => $data['payment_method'] === 'cash' ? now() : null,
                        ]);

                        if ($payment->status === 'paid') {
                            $record->checkPaymentStatus();
                        }

                        Notification::make()
                            ->success()
                            ->title('Payment added')
                            ->body($data['payment_method'] === 'cash' && isset($data['change'])
                                ? "Change: Rp " . number_format($data['change'], 0, ',', '.')
                                : null)
                            ->send();
                    }),

                Tables\Actions\Action::make('view_payments')
                    ->label('View Payments')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('info')
                    ->url(fn (Order $record) => route('filament.admin.resources.payments.index', [
                        'tableFilters' => ['order_id' => ['value' => $record->id]]
                    ]))
                    ->visible(fn (Order $record) => $record->hasPayment()),

                Tables\Actions\Action::make('mark_paid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Order $record) => !$record->isFullyPaid())
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        // Create full payment
                        $record->payments()->create([
                            'amount' => $record->remaining_amount,
                            'payment_method' => 'cash',
                            'status' => 'paid',
                            'processed_by' => auth('admin')->id()??null,
                            'paid_at' => now(),
                        ]);

                        $record->checkPaymentStatus();

                        Notification::make()
                            ->success()
                            ->title('Order marked as paid')
                            ->send();
                    }),

                Tables\Actions\Action::make('update_status')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('New Status')
                            ->options([
                                'pending' => 'Pending',
                                'preparing' => 'Preparing',
                                'ready' => 'Ready',
                                'completed' => 'Completed',
                            ])
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data) {
                        $record->update(['status' => $data['status']]);
                        if ($data['status'] === 'completed') {
                            $record->markAsCompleted();
                        }
                        Notification::make()
                            ->success()
                            ->title('Status updated')
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

             ->headerActions([
            ExportAction::make()->exports([
                ExcelExport::make()->fromTable()->except([
                    'created_at', 'updated_at', 'deleted_at',
                ]),
            ]),
        ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
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
