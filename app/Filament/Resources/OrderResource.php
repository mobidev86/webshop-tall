<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                                        if ($item->product) {
                                            $item->product->stock += $item->quantity;
                                            $item->product->save();
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
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        if ($state) {
                                            $product = Product::find($state);
                                            if ($product) {
                                                $set('product_name', $product->name);
                                                $set('price', $product->getCurrentPrice());
                                                $set('available_stock', $product->stock);
                                                
                                                // Also update subtotal whenever product changes
                                                $quantity = 1; // Default quantity
                                                $set('quantity', $quantity);
                                                $set('subtotal', $product->getCurrentPrice() * $quantity);
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
                                    ->afterStateUpdated(function (callable $set, $state, $get) {
                                        $price = (float) $get('price');
                                        $quantity = (int) $state;
                                        $set('subtotal', $price * $quantity);
                                        
                                        // Validate against available stock
                                        $productId = $get('product_id');
                                        if ($productId) {
                                            $product = Product::find($productId);
                                            if ($product && $quantity > $product->stock) {
                                                $set('quantity', $product->stock);
                                                $set('subtotal', $price * $product->stock);
                                            }
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
                            ->afterStateUpdated(function ($state, callable $get, callable $set, ?Order $record = null) {
                                if ($record) {
                                    // Recalculate total after items are updated
                                    $record->calculateTotalAmount();
                                    $set('total_amount', $record->total_amount);
                                } else {
                                    // For new orders, calculate the total here
                                    $total = 0;
                                    if (is_array($state)) {
                                        foreach ($state as $item) {
                                            if (isset($item['price']) && isset($item['quantity'])) {
                                                $total += (float) $item['price'] * (int) $item['quantity'];
                                            }
                                        }
                                    }
                                    $set('total_amount', $total);
                                }
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
                        Order::STATUS_PROCESSING => 'blue',
                        Order::STATUS_COMPLETED => 'green',
                        Order::STATUS_DECLINED => 'red',
                        Order::STATUS_CANCELLED => 'orange',
                    })
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
                            if ($item->product) {
                                $item->product->stock += $item->quantity;
                                $item->product->save();
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
                                    if ($item->product) {
                                        $item->product->stock += $item->quantity;
                                        $item->product->save();
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
}
