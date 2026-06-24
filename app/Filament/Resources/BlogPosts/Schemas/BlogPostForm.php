<?php

namespace App\Filament\Resources\BlogPosts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->required(),
                Select::make('author_id')
                    ->label('Penulis')
                    ->relationship('author', 'name')
                    ->required()
                    ->default(fn () => auth()->id()),
                Textarea::make('excerpt')
                    ->label('Ringkasan')
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull(),
                RichEditor::make('content')
                    ->label('Konten')
                    ->required()
                    ->columnSpanFull(),
                FileUpload::make('featured_image')
                    ->label('Gambar Utama')
                    ->image()
                    ->directory('blog')
                    ->columnSpanFull(),
                Toggle::make('is_published')
                    ->label('Publikasikan')
                    ->default(false),
                DateTimePicker::make('published_at')
                    ->label('Tanggal Publikasi')
                    ->default(now()),
                TextInput::make('meta_title')
                    ->label('Meta Title')
                    ->maxLength(255),
                Textarea::make('meta_description')
                    ->label('Meta Description')
                    ->rows(2)
                    ->maxLength(300),
            ]);
    }
}
