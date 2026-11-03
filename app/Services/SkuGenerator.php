<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetType;
use Illuminate\Support\Str;

class SkuGenerator
{
    /**
     * Generate an appropriate prefix based on type, category, or asset name.
     */
    public static function derivePrefix(?string $typeName = null, ?string $categoryName = null, ?string $assetName = null): string
    {
        // 1. Try Asset Type Name
        if (!empty($typeName)) {
            $prefix = self::cleanPrefixCandidate($typeName);
            if (!empty($prefix)) {
                return $prefix;
            }
        }

        // 2. Try Category Name
        if (!empty($categoryName)) {
            $prefix = self::cleanPrefixCandidate($categoryName);
            if (!empty($prefix)) {
                return $prefix;
            }
        }

        // 3. Try Asset Name
        if (!empty($assetName)) {
            $prefix = self::cleanPrefixCandidate($assetName);
            if (!empty($prefix)) {
                return $prefix;
            }
        }

        return 'AST';
    }

    /**
     * Helper to derive a 2-4 letter uppercase prefix from a name string.
     */
    private static function cleanPrefixCandidate(string $text): string
    {
        $text = trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $text));
        if (empty($text)) {
            return '';
        }

        // Common known mappings for disaster management equipment
        $lower = strtolower($text);
        if (str_contains($lower, 'extinguisher')) return 'FE';
        if (str_contains($lower, 'generator')) return 'GEN';
        if (str_contains($lower, 'first aid') || str_contains($lower, 'trauma')) return 'FAK';
        if (str_contains($lower, 'defibrillator') || str_contains($lower, 'aed')) return 'AED';
        if (str_contains($lower, 'stretcher')) return 'STR';
        if (str_contains($lower, 'radio') || str_contains($lower, 'transceiver')) return 'RAD';
        if (str_contains($lower, 'vehicle') || str_contains($lower, 'patrol') || str_contains($lower, 'multicab')) return 'VEH';
        if (str_contains($lower, 'boat') || str_contains($lower, 'watercraft')) return 'BOT';
        if (str_contains($lower, 'megaphone') || str_contains($lower, 'siren')) return 'MEG';
        if (str_contains($lower, 'rope') || str_contains($lower, 'harness')) return 'ROP';

        $words = array_values(array_filter(explode(' ', $text)));
        if (count($words) >= 2) {
            $initials = '';
            foreach ($words as $w) {
                if (in_array(strtolower($w), ['and', '&', 'of', 'for', 'the', 'in', 'on', 'at', 'to', 'a', 'an'])) {
                    continue;
                }
                $initials .= strtoupper($w[0]);
                if (strlen($initials) >= 4) {
                    break;
                }
            }
            if (strlen($initials) >= 2) {
                return $initials;
            }
        }

        // Single word: take first 3 alphanumeric characters
        return strtoupper(substr($words[0] ?? 'AST', 0, 3));
    }

    /**
     * Generate N unique sequential SKUs/Codes.
     *
     * @param int $quantity
     * @param int|null $categoryId
     * @param int|null $typeId
     * @param string|null $assetName
     * @return array<string>
     */
    public static function generateBatch(int $quantity = 1, ?int $categoryId = null, ?int $typeId = null, ?string $assetName = null): array
    {
        $quantity = max(1, min($quantity, 100));

        $category = $categoryId ? AssetCategory::find($categoryId) : null;
        $type = $typeId ? AssetType::find($typeId) : null;

        $prefix = self::derivePrefix($type?->name, $category?->name, $assetName);

        // Find existing codes matching {PREFIX}-###
        $existingCodes = Asset::where('code', 'LIKE', "{$prefix}-%")
            ->pluck('code')
            ->toArray();

        $highest = 0;
        foreach ($existingCodes as $c) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '-(\d+)$/', $c, $matches)) {
                $num = (int)$matches[1];
                if ($num > $highest) {
                    $highest = $num;
                }
            }
        }

        $generated = [];
        $candidateNum = $highest + 1;

        while (count($generated) < $quantity) {
            $code = sprintf('%s-%03d', $prefix, $candidateNum);

            // Double check uniqueness in DB and in current batch
            if (!in_array($code, $generated, true) && !Asset::where('code', $code)->exists()) {
                $generated[] = $code;
            }
            $candidateNum++;
        }

        return $generated;
    }

    /**
     * Generate a single unique SKU/Code.
     */
    public static function generate(?int $categoryId = null, ?int $typeId = null, ?string $assetName = null): string
    {
        $batch = self::generateBatch(1, $categoryId, $typeId, $assetName);
        return $batch[0] ?? ('AST-' . strtoupper(Str::random(6)));
    }
}

