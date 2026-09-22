<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('panduan')->where('status', 'publik')->update(['status' => 'Publik']);
        DB::table('panduan')->where('status', 'draft')->update(['status' => 'Draft']);
        DB::table('panduan')->where('kategori', 'Budidaya')->update(['kategori' => 'Budidaya Tanaman']);
        DB::table('panduan')->where('kategori', 'Pemupukan')->update(['kategori' => 'Nutrisi & Pupuk']);
    }

    public function down(): void
    {
        DB::table('panduan')->where('status', 'Publik')->update(['status' => 'publik']);
        DB::table('panduan')->where('status', 'Draft')->update(['status' => 'draft']);
        DB::table('panduan')->where('kategori', 'Budidaya Tanaman')->update(['kategori' => 'Budidaya']);
        DB::table('panduan')->where('kategori', 'Nutrisi & Pupuk')->update(['kategori' => 'Pemupukan']);
    }
};
