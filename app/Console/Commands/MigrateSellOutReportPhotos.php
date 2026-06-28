<?php

namespace App\Console\Commands;

use App\Models\SellOutReportPhoto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateSellOutReportPhotos extends Command
{
    protected $signature = 'sellout:migrate-photos';

    protected $description = 'Move sell out report photos from storage/app/public into public/uploads so they no longer depend on the public/storage symlink';

    public function handle(): int
    {
        $destination = public_path(SellOutReportPhoto::UPLOAD_PATH);

        if (! is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        $moved = 0;
        $missing = 0;

        SellOutReportPhoto::query()->each(function (SellOutReportPhoto $photo) use ($destination, &$moved, &$missing) {
            $oldPath = $photo->photo_path;

            if (! str_contains($oldPath, '/')) {
                return;
            }

            $sourcePath = Storage::disk('public')->path($oldPath);
            $filename = basename($oldPath);
            $targetPath = $destination . '/' . $filename;

            if (! is_file($sourcePath)) {
                $missing++;
                $this->warn("Missing source file for photo #{$photo->id}: {$oldPath}");

                return;
            }

            rename($sourcePath, $targetPath);

            $photo->update([
                'photo_path' => $filename,
                'photo_url' => null,
            ]);

            $moved++;
        });

        $this->info("Migrated {$moved} photo(s). {$missing} missing source file(s).");

        return self::SUCCESS;
    }
}
