<?php

namespace App\Modules\DailyRecords;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class NoteDocument
{
    public static function validate(string $json, ?int $entry, array $uploads): array
    {
        $ops = json_decode($json, true, 8);
        if (!is_array($ops) || !array_is_list($ops) || !$ops || count($ops) > 600) {
            self::invalid();
        }
        $body = '';
        $images = [];
        foreach ($ops as $op) {
            if (!is_array($op) || array_diff(array_keys($op), ['insert', 'attributes'])) {
                self::invalid();
            }
            $insert = $op['insert'] ?? null;
            $attributes = $op['attributes'] ?? [];
            if (is_string($insert) && $insert !== '' && is_array($attributes)) {
                foreach ($attributes as $name => $value) {
                    if (!self::validAttribute($name, $value)) {
                        self::invalid();
                    }
                }
                $body .= $insert;
            } elseif (is_array($insert) && array_keys($insert) === ['image'] && !$attributes &&
                is_string($insert['image']) && preg_match('/\Anote-image-[0-9a-f-]{36}\.(?:png|jpg|jpeg|webp)\z/', $insert['image'])) {
                $images[] = $insert['image'];
                $body .= "\n";
            } else {
                self::invalid();
            }
        }
        $last = $ops[array_key_last($ops)]['insert'];
        if (!is_string($last) || !str_ends_with($last, "\n") || mb_strlen(trim($body)) > 10000 || count($images) > 30) {
            self::invalid();
        }
        $missing = array_diff(array_unique($images), array_column($uploads, 'name'));
        if ($entry && $missing) {
            $owned = DB::table('daily_record_files')->where('entry_id', $entry)->where('restricted', false)
                ->whereIn('name', $missing)->pluck('name')->all();
            $missing = array_diff($missing, $owned);
        }
        if ($missing) {
            self::invalid();
        }

        return [$ops, trim($body)];
    }

    private static function validAttribute(string $name, mixed $value): bool
    {
        return match ($name) {
            'bold', 'italic', 'underline', 'strike', 'inline-code', 'blockquote', 'code-block' => $value === true,
            'header' => is_int($value) && $value >= 1 && $value <= 6,
            'indent' => is_int($value) && $value >= 1 && $value <= 8,
            'list' => in_array($value, ['ordered', 'bullet', 'checked', 'unchecked'], true),
            'align' => in_array($value, ['left', 'right', 'center', 'justify'], true),
            'direction' => $value === 'rtl',
            'script' => in_array($value, ['sub', 'super'], true),
            'font' => in_array($value, ['GoogleSans', 'serif', 'monospace'], true),
            'size' => is_string($value) && (in_array($value, ['small', 'large', 'huge'], true)
                || (is_numeric($value) && (float) $value >= 8 && (float) $value <= 72)),
            'color', 'background' => is_string($value) && preg_match('/\A#[a-f0-9]{6}(?:[a-f0-9]{2})?\z/i', $value),
            'link' => is_string($value) && mb_strlen($value) <= 2048 && !preg_match('/[\x00-\x20]/', $value)
                && in_array(strtolower(parse_url($value, PHP_URL_SCHEME) ?? ''), ['https', 'http'], true)
                && parse_url($value, PHP_URL_HOST),
            default => false,
        };
    }

    private static function invalid(): never
    {
        throw ValidationException::withMessages(['note_document' => 'Check the note formatting, web links, images, or document length.']);
    }
}
