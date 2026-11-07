<?php

namespace App\Services\AssetManagement;

use Illuminate\Support\Facades\DB;

class AssetCodeGenerator
{
    public function preview(): string
    {
        $lastNumber = (int) DB::table('asset_code_sequences')->where('id', 1)->value('last_number');

        return $this->format(max($lastNumber, $this->highestExistingNumber()) + 1);
    }

    public function next(): string
    {
        return DB::transaction(function () {
            // One shared counter prevents two submissions from receiving the same code.
            $sequence = DB::table('asset_code_sequences')->where('id', 1)->lockForUpdate()->first();
            $number = max((int) $sequence->last_number, $this->highestExistingNumber()) + 1;
            DB::table('asset_code_sequences')->where('id', 1)->update(['last_number' => $number]);

            return $this->format($number);
        });
    }

    private function highestExistingNumber(): int
    {
        $highest = 0;
        // Include every branch and codes entered before automatic numbering was added.
        foreach (DB::table('assets')->where('asset_code', 'like', 'ID:%')->select('asset_code')->cursor() as $asset) {
            if (preg_match('/^ID:(\d+)$/D', $asset->asset_code, $matches)) {
                $highest = max($highest, (int) $matches[1]);
            }
        }

        return $highest;
    }

    private function format(int $number): string
    {
        return 'ID:'.str_pad((string) $number, 6, '0', STR_PAD_LEFT);
    }
}
