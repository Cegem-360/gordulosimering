<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Az egyetlen dokumentum, amely még a nevek tárolása előtt került fel
 * (6202-2RSH_S, az SKF termékoldala PDF-ben), megkapja a megjelenített
 * nevét. Ha a fájlt azóta lecserélték, nem csinál semmit.
 */
return new class() extends Migration
{
    private const string PATH = 'products/documents/C5VKDYjCWNIZKRn0AcSxJEGuIm8iK8g4TnrgLdyd.pdf';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('products')
            ->where('slug', '6202-2rsh-s')
            ->where('documents', 'like', '%C5VKDYjCWNIZKRn0AcSxJEGuIm8iK8g4TnrgLdyd.pdf%')
            ->whereNull('document_names')
            ->update(['document_names' => json_encode([self::PATH => 'SKF 6202-2RSH műszaki adatlap'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('products')
            ->where('slug', '6202-2rsh-s')
            ->where('document_names', 'like', '%SKF 6202-2RSH%')
            ->update(['document_names' => null]);
    }
};
