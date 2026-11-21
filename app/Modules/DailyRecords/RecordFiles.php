<?php

namespace App\Modules\DailyRecords;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class RecordFiles
{
    public const RULE = 'file|max:10240|mimes:pdf,jpg,jpeg,png,webp,txt,csv,doc,docx,xls,xlsx,ppt,pptx';

    public function transaction(array $uploads, callable $work): mixed
    {
        $files = [];
        try {
            foreach ($uploads as $upload) {
                /** @var UploadedFile $upload */
                $path = $upload->store('daily-records', 'local');
                abort_unless($path, 500, 'The attachment could not be stored.');
                $name = basename(str_replace(chr(92), '/', $upload->getClientOriginalName()));
                $name = preg_replace('/[[:cntrl:]]/u', '', $name) ?: 'Attachment';
                $files[] = [
                    'path' => $path,
                    'name' => mb_substr($name, 0, 240),
                    'bytes' => $upload->getSize(),
                ];
            }

            $result = DB::transaction(fn () => $work($files), 3);
        } catch (\Throwable $error) {
            Storage::disk('local')->delete(array_column($files, 'path'));
            throw $error;
        }
        if ($files) {
            $paths = array_column($files, 'path');
            $persisted = DB::table('daily_record_files')->useWritePdo()->whereIn('path', $paths)->pluck('path')->all();
            $unused = array_diff($paths, $persisted);
            if ($unused) {
                Storage::disk('local')->delete($unused);
            }
        }
        return $result;
    }

    public function attach(array $files, string $column, int $id): void
    {
        if ($files) {
            DB::table('daily_record_files')->insert(array_map(fn ($file) => $file + [$column => $id], $files));
        }
    }

    public function download(object $file): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($file->path), 404);
        return Storage::disk('local')->download($file->path, $file->name, [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
