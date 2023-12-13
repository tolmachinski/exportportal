<?php

namespace App\Filesystem;

/**
 * The path generator for filesystem.
 */
final class ItemPathGenerator
{
    /**
     * Create path to the file uploaded to the directory.
     * The path is created deterministically - for the same
     * filename the path always be the same.
     */
    public static function draftList(int $userId, string $fileName): string
    {
        return "bulk_upload/{$userId}/{$fileName}";
    }

    public static function draftDirectory(int $userId): string
    {
        return "bulk_upload/{$userId}";
    }

    /**
     * Create path to the file uploaded to the directory.
     * The path is created deterministically - for the same filename the path always be the same.
     */
    public static function prototypeDraftUpload(int $idPrototype, string $fileName): string
    {
        return "prototype/{$idPrototype}/{$fileName}";
    }

    /**
     * Create path to the file prototype directory.
     * The path is created deterministically - for the same filename the path always be the same.
     */
    public static function prototypeDirectory(int $idPrototype): string
    {
        return "prototype/{$idPrototype}";
    }

    /**
     * Create path to the file uploaded to the directory.
     * The path is created deterministically - for the same filename the path always be the same.
     */
    public static function snapshotDraftUpload(int $snapshotId, string $fileName): string
    {
        return "snapshots/{$snapshotId}/{$fileName}";
    }

    /**
     * Create path to the file prototype directory.
     * The path is created deterministically - for the same filename the path always be the same.
     */
    public static function snapshotDirectory(int $snapshotId): string
    {
        return "snapshots/{$snapshotId}";
    }

    /**
     * Create path to the user acreditation file directory.
     * The path is created deterministically - for the same filename the path always be the same.
     */
    public static function usersAccreditationDirectory(int $userId): string
    {
        return "users_accreditation/{$userId}";
    }

    /**
     * Path To main image.
     */
    public static function itemMainPhotoPath(int $itemId, string $itemImage): string
    {
        return "items/{$itemId}/{$itemImage}";
    }

    /**
     * Path to pick of month image.
     */
    public static function pickOfMonth(int $itemId, string $fileName): string
    {
        return "items/{$itemId}/thumb_3_{$fileName}";
    }
}
