<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $recordTitleAttribute = 'order_number';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_number')
                    ->default(fn () => Order::generateOrderNumber())
                    ->disabled()
                    ->dehydrated()
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
                    ->required(),
                
                Forms\Components\TextInput::make('total_amount')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_number')
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
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
                        default => 'gray',
                    })
                    ->sortable(),
                
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
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Automatically set the user_id to the current record's ID
                        $data['user_id'] = $this->ownerRecord->id;
                        return $data;
                    }),
            ])
            ->actions([
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
} 