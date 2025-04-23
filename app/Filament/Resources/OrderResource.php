<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'order_number';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Details')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->default(fn () => Order::generateOrderNumber())
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name', function (Builder $query) {
                                return $query->where('role', User::ROLE_CUSTOMER);
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options([
                                Order::STATUS_PENDING => 'Pending',
                                Order::STATUS_PROCESSING => 'Processing',
                                Order::STATUS_COMPLETED => 'Completed',
                                Order::STATUS_DECLINED => 'Declined',
                                Order::STATUS_CANCELLED => 'Cancelled',
                            ])
                            ->default(Order::STATUS_PENDING)
                            ->reactive()
                            ->afterStateUpdated(function (string $state, $record) {
                                // If order was cancelled, restore product stock
                                if ($record && $state === Order::STATUS_CANCELLED && $record->status !== Order::STATUS_CANCELLED) {
                                    foreach ($record->items as $item) {
                                        $product = $item->product;
                                        if ($product instanceof Product) {
                                            $product->stock += $item->quantity;
                                            $product->save();
                                        }
                                    }
                                }
                            })
                            ->required(),

                        Forms\Components\TextInput::make('total_amount')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Order Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->relationship('product', 'name', function (Builder $query) {
                                        return $query->where('stock', '>', 0);
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, $state, $livewire) {
                                        if ($state) {
                                            $productResult = Product::find($state);
                                            if ($productResult instanceof Product) {
                                                // Get the current price from the product
                                                $price = (float) $productResult->getCurrentPrice();

                                                // Set the product details in the form
                                                $set('product_name', $productResult->name);
                                                $set('price', $price);
                                                $set('available_stock', $productResult->stock);

                                                // Set the initial quantity
                                                $quantity = 1; // Default quantity
                                                $set('quantity', $quantity);

                                                // Calculate and set the subtotal
                                                $subtotal = $price * $quantity;
                                                $set('subtotal', $subtotal);

                                                // Force a form update to recalculate the total
                                                if (method_exists($livewire, 'dispatch')) {
                                                    $livewire->dispatch('recalculate-total');
                                                }
                                            }
                                        }
                                    }),

                                Forms\Components\TextInput::make('product_name')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),

                                Forms\Components\TextInput::make('available_stock')
                                    ->label('Available Stock')
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, $state, $get, $livewire) {
                                        // Ensure we have a valid price and quantity
                                        $price = $get('price');
                                        $price = ($price !== null && is_numeric($price))
                                            ? (float) $price
                                            : 0;

                                        $quantity = ($state !== null && is_numeric($state))
                                            ? (int) $state
                                            : 0;

                                        // Calculate the subtotal
                                        $subtotal = $price * $quantity;

                                        // Set the subtotal back to the form
                                        $set('subtotal', $subtotal);

                                        // Validate against available stock
                                        $productId = $get('product_id');
                                        if ($productId) {
                                            $productResult = Product::find($productId);
                                            if ($productResult instanceof Product && $quantity > $productResult->stock) {
                                                // Adjust quantity to available stock
                                                $quantity = $productResult->stock;
                                                $set('quantity', $quantity);

                                                // Recalculate subtotal
                                                $subtotal = $price * $quantity;
                                                $set('subtotal', $subtotal);
                                            }
                                        }

                                        // Force a form update to recalculate the total
                                        if (method_exists($livewire, 'dispatch')) {
                                            $livewire->dispatch('recalculate-total');
                                        }
                                    }),

                                Forms\Components\TextInput::make('price')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01)
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),

                                Forms\Components\TextInput::make('subtotal')
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(),
                            ])
                            ->columns(2)
                            ->addActionLabel('Add Product')
                            ->deleteAction(
                                fn (Forms\Components\Actions\Action $action) => $action->requiresConfirmation(),
                            )
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                self::calculateOrderTotal($get, $set);
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Order::STATUS_PENDING => 'gray',
                        Order::STATUS_PROCESSING => 'info',
                        Order::STATUS_COMPLETED => 'success',
                        Order::STATUS_DECLINED => 'warning',
                        Order::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->getStateUsing(fn (Order $record): int => $record->itemsCount())
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
                            ->selectRaw('orders.*, sum(order_items.quantity) as items_count')
                            ->groupBy('orders.id')
                            ->orderBy('items_count', $direction);
                    }),

                Tables\Columns\TextColumn::make('total_amount')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Order::STATUS_PENDING => 'Pending',
                        Order::STATUS_PROCESSING => 'Processing',
                        Order::STATUS_COMPLETED => 'Completed',
                        Order::STATUS_DECLINED => 'Declined',
                        Order::STATUS_CANCELLED => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->label('Customer'),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Order $record) {
                        // If deleting order, restore stock for all its items
                        foreach ($record->items as $item) {
                            $product = $item->product;
                            if ($product instanceof Product) {
                                $product->stock += $item->quantity;
                                $product->save();
                            }
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            // Restore stock for all deleted orders
                            foreach ($records as $record) {
                                foreach ($record->items as $item) {
                                    $product = $item->product;
                                    if ($product instanceof Product) {
                                        $product->stock += $item->quantity;
                                        $product->save();
                                    }
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    /**
     * Calculate the total amount from all order items
     *
     * @param  Forms\Get  $get  The Filament form's Get object
     * @param  Forms\Set  $set  The Filament form's Set object
     * @return float The calculated total amount
     */
    private static function calculateOrderTotal(Forms\Get $get, Forms\Set $set): float
    {
        // Get all items
        $items = $get('items');

        // Start with zero total
        $total = 0;

        // Calculate total from all items
        if (is_array($items)) {
            foreach ($items as $index => $item) {
                // Get quantity and price, with safe defaults
                $quantity = 0;
                if (array_key_exists('quantity', $item) && is_numeric($item['quantity'])) {
                    $quantity = (int) $item['quantity'];
                }

                $price = 0;
                if (array_key_exists('price', $item) && is_numeric($item['price'])) {
                    $price = (float) $item['price'];
                }

                // Calculate this item's subtotal
                $itemTotal = $price * $quantity;

                // Add to the running total
                $total += $itemTotal;
            }
        }

        // Format the total amount to 2 decimal places
        $formattedTotal = number_format($total, 2, '.', '');

        // Update the total_amount field with the formatted total
        $set('total_amount', $formattedTotal);

        return $total;
    }
}
