<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * আপলোড করা ফাইলের সর্বজনীন URL — এক জায়গা থেকে।
 *
 * Every uploaded file (logo, banner, slide, teacher photo, notice
 * attachment) goes through here, so moving from the local disk to R2 or
 * Cloudinary is a change to MEDIA_DISK in .env plus this one file — no
 * Blade template is touched.
 *
 * Why not asset('storage/'.$path): FilesystemTenancyBootstrapper suffixes
 * storage_path() per tenant, so files actually land in
 * storage/tenant3/app/public/ while public/storage symlinks to
 * storage/app/public — the two never meet and every image 404s.
 *
 * Why not tenant_asset(): it serves each file through a PHP route, booting
 * Laravel per image, and the helper is useless once files live on R2.
 */
class Media
{
    /**
     * ফাইলের পূর্ণ URL। path খালি হলে null — ভিউতে `@if` করার জন্য।
     */
    public static function url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        // Already absolute (a seeded placeholder, or a path that came back
        // from a remote driver) — hand it back untouched.
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $disk = self::disk();

        // A remote disk (R2/S3/Cloudinary) knows its own public URL, and the
        // tenant prefix is already baked into the stored path.
        if ($disk !== 'public') {
            return Storage::disk($disk)->url($path);
        }

        // Local: public/storage points at storage/app/public, but tenancy
        // wrote the file under storage/tenant{id}/app/public. Serve it from
        // the per-tenant symlink that storage:link-tenants creates.
        $tenantId = tenant('id');

        return $tenantId === null
            ? asset('storage/'.$path)
            : asset('storage/tenants/'.$tenantId.'/'.$path);
    }

    /**
     * ফাইল সংরক্ষণ করে সংরক্ষিত path ফেরত দেয়।
     *
     * On the local disk tenancy already suffixes storage_path(), so the
     * tenant prefix would be doubled — the path stays bare. On a remote
     * disk nothing separates tenants, so the id goes into the key itself.
     */
    public static function store(UploadedFile $file, string $folder): string
    {
        $disk = self::disk();

        if ($disk === 'public') {
            return $file->store($folder, $disk);
        }

        return $file->store('tenants/'.tenant('id').'/'.$folder, $disk);
    }

    /**
     * ফাইল মুছে ফেলে। path খালি হলে কিছুই করে না।
     */
    public static function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        Storage::disk(self::disk())->delete($path);
    }

    /**
     * ফাইল যে ডিস্কে যাবে ও যেখান থেকে পড়া হবে।
     */
    public static function disk(): string
    {
        return (string) config('filesystems.media_disk', 'public');
    }

    /**
     * এক মাদরাসার storage ফোল্ডারগুলো তৈরি করে; app/public-এর পথ ফেরত দেয়।
     *
     * FilesystemTenancyBootstrapper suffixes storage_path(), so Laravel looks
     * for framework/cache, sessions and views under the tenant's own root.
     * Creating only app/public leaves those missing, and the first real-time
     * facade write does tempnam() on a directory that isn't there — PHP falls
     * back to the system temp dir and raises a warning that Laravel turns into
     * a 500 on livewire/update.
     */
    public static function prepareTenantStorage(int|string $tenantKey): string
    {
        $root = storage_path('tenant'.$tenantKey);

        // livewire-tmp holds a file mid-upload, before save() moves it.
        foreach (['app/public', 'app/livewire-tmp', 'framework/cache', 'framework/sessions', 'framework/views'] as $path) {
            File::ensureDirectoryExists($root.'/'.$path);
        }

        // Uploaded files are per-install data, never committed — the same
        // pattern Laravel ships for storage/app.
        $ignore = $root.'/.gitignore';

        if (! file_exists($ignore)) {
            File::put($ignore, "*\n!.gitignore\n");
        }

        return $root.'/app/public';
    }
}
