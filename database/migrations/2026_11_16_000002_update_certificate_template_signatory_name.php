<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function replaceSignatoryName(string $from, string $to): void
    {
        foreach (DB::table('certificate_templates')->select('id', 'signatories_json')->cursor() as $template) {
            $signatories = json_decode($template->signatories_json ?? '', true);
            if (!is_array($signatories)) {
                continue;
            }

            $changed = false;
            foreach ($signatories as &$signatory) {
                if (is_array($signatory) && ($signatory['name'] ?? null) === $from) {
                    $signatory['name'] = $to;
                    $changed = true;
                }
            }
            unset($signatory);

            if ($changed) {
                DB::table('certificate_templates')->where('id', $template->id)->update([
                    'signatories_json' => json_encode($signatories, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
            }
        }
    }

    public function up(): void
    {
        $this->replaceSignatoryName('SOPHEA MENG', 'PET POEUN');
        $this->replaceSignatoryName('Sophea Meng', 'PET POEUN');
    }

    public function down(): void
    {
        $this->replaceSignatoryName('PET POEUN', 'SOPHEA MENG');
    }
};
