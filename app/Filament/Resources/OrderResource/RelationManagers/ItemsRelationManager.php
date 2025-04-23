<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\OrderItem;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $recordTitleAttribute = 'product_name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name', function (Builder $query) {
                        return $query->where('stock', '>', 0);
                    })
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function (callable $set, $state, $livewire) {
                        $product = Product::find($state);
                        if ($product) {
                            $price = (float) $product->getCurrentPrice();
                            $set('product_name', $product->name);
                            $set('price', $price);
                            $set('quantity', 1);
                            $set('subtotal', $price);
                            $set('available_stock', $product->stock);
                        }
                    })
                    ->required(),

                Forms\Components\TextInput::make('product_name')
                    ->required()
                    ->maxLength(255)
                    ->disabled()
                    ->dehydrated(),

                Forms\Components\TextInput::make('available_stock')
                    ->label('Available Stock')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),

                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->reactive()
                    ->afterStateUpdated(function (callable $set, $state, $get) {
                        $price = (float) $get('price');
                        $quantity = (int) $state;
                        $set('subtotal', $price * $quantity);
                    })
                    ->suffixAction(
                        Forms\Components\Actions\Action::make('validateStock')
                            ->icon('heroicon-m-check')
                            ->action(function (callable $get, callable $set) {
                                $productId = $get('product_id');
                                $quantity = (int) $get('quantity');

                                if (! $productId) {
                                    return;
                                }

                                $product = Product::find($productId);
                                if (! $product) {
                                    return;
                                }

                                // Check if quantity is valid
                                if ($quantity <= 0) {
                                    $set('quantity', 1);
                                    $set('subtotal', $get('price') * 1);

                                    return;
                                }

                                // Validate against available stock
                                if ($quantity > $product->stock) {
                                    $set('quantity', $product->stock);
                                    $set('subtotal', $get('price') * $product->stock);
                                }
                            })
                    ),

                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->reactive()
                    ->minValue(0.01)
                    ->step(0.01)
                    ->afterStateUpdated(function (callable $set, $state, $get) {
                        $price = (float) $state;
                        $quantity = (int) $get('quantity');
                        $set('subtotal', $price * $quantity);
                    }),

                Forms\Components\TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->disabled()
                    ->dehydrated(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('quantity')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subtotal')
                    ->money('USD')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data): Model {
                        try {
                            DB::beginTransaction();

                            // Validate required fields
                            if (empty($data['product_id'])) {
                                throw new \Exception('Product is required');
                            }

                            // Get fresh product data to ensure we have the latest stock info
                            $product = Product::lockForUpdate()->find($data['product_id']);
                            if (! $product) {
                                throw new \Exception('Selected product does not exist');
                            }

                            // Check if product has enough stock
                            $quantity = (int) ($data['quantity'] ?? 1);
                            if ($quantity > $product->stock) {
                                throw new \Exception("Not enough stock available. Only {$product->stock} units available.");
                            }

                            // Ensure product data is complete
                            $data['product_name'] = $product->name;

                            // Calculate subtotal
                            $price = (float) ($data['price'] ?? $product->getCurrentPrice());
                            $subtotal = $price * $quantity;

                            // Update product stock
                            $product->stock -= $quantity;
                            $product->save();

                            // Create item with calculated values
                            $orderItem = OrderItem::create([
                                'order_id' => $this->ownerRecord->id,
                                'product_id' => $data['product_id'],
                                'product_name' => $data['product_name'],
                                'quantity' => $quantity,
                                'price' => $price,
                                'subtotal' => $subtotal,
                            ]);

                            // Update the parent order's total amount
                            $this->ownerRecord->calculateTotalAmount();

                            DB::commit();

                            return $orderItem;
                        } catch (\Exception $e) {
                            DB::rollBack();
                            throw $e;
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function (Model $record, array $data): Model {
                        try {
                            DB::beginTransaction();

                            // Validate required fields
                            if (empty($data['product_id'])) {
                                throw new \Exception('Product is required');
                            }

                            // Original values for reference
                            $originalProductId = $record->product_id;
                            $originalQuantity = $record->quantity;

                            // Different product selected
                            if ($data['product_id'] != $originalProductId) {
                                // Restore stock to original product
                                $originalProduct = Product::lockForUpdate()->find($originalProductId);
                                if ($originalProduct) {
                                    $originalProduct->stock += $originalQuantity;
                                    $originalProduct->save();
                                }

                                // Get fresh data for new product
                                $newProduct = Product::lockForUpdate()->find($data['product_id']);
                                if (! $newProduct) {
                                    throw new \Exception('Selected product does not exist');
                                }

                                // Check if new product has enough stock
                                $newQuantity = (int) ($data['quantity'] ?? 1);
                                if ($newQuantity > $newProduct->stock) {
                                    throw new \Exception("Not enough stock available. Only {$newProduct->stock} units available.");
                                }

                                // Update new product stock
                                $newProduct->stock -= $newQuantity;
                                $newProduct->save();

                                // Update product data
                                $data['product_name'] = $newProduct->name;
                                $price = (float) ($data['price'] ?? $newProduct->getCurrentPrice());
                            } else {
                                // Same product, just quantity changed
                                $product = Product::lockForUpdate()->find($originalProductId);
                                if (! $product) {
                                    throw new \Exception('Product does not exist');
                                }

                                // Calculate stock change
                                $newQuantity = (int) ($data['quantity'] ?? 1);
                                $stockChange = $newQuantity - $originalQuantity;

                                // Check if we need more stock than available
                                if ($stockChange > 0 && $stockChange > $product->stock) {
                                    throw new \Exception("Not enough stock available. Only {$product->stock} additional units available.");
                                }

                                // Update product stock
                                $product->stock -= $stockChange;
                                $product->save();

                                $price = (float) ($data['price'] ?? $product->getCurrentPrice());
                            }

                            // Calculate subtotal
                            $quantity = (int) ($data['quantity'] ?? 1);
                            $subtotal = $price * $quantity;

                            // Update the order item
                            $record->update([
                                'product_id' => $data['product_id'],
                                'product_name' => $data['product_name'] ?? $record->product_name,
                                'quantity' => $quantity,
                                'price' => $price,
                                'subtotal' => $subtotal,
                            ]);

                            // Update the parent order's total amount
                            $this->ownerRecord->calculateTotalAmount();

                            DB::commit();

                            return $record;
                        } catch (\Exception $e) {
                            DB::rollBack();
                            throw $e;
                        }
                    }),
                Tables\Actions\DeleteAction::make()
                    ->using(function (Model $record): void {
                        try {
                            DB::beginTransaction();

                            // Restore product stock
                            $product = Product::lockForUpdate()->find($record->product_id);
                            if ($product) {
                                $product->stock += $record->quantity;
                                $product->save();
                            }

                            // Delete the record
                            $record->delete();

                            // Update the parent order's total amount
                            $this->ownerRecord->calculateTotalAmount();

                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            throw $e;
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->using(function (array $records): void {
                            try {
                                DB::beginTransaction();

                                foreach ($records as $record) {
                                    // Restore product stock
                                    $product = Product::lockForUpdate()->find($record->product_id);
                                    if ($product) {
                                        $product->stock += $record->quantity;
                                        $product->save();
                                    }

                                    // Delete the record
                                    $record->delete();
                                }

                                // Update the parent order's total amount
                                $this->ownerRecord->calculateTotalAmount();

                                DB::commit();
                            } catch (\Exception $e) {
                                DB::rollBack();
                                throw $e;
                            }
                        }),
                ]),
            ]);
    }
}
