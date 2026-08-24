<?php

use App\Models\GalleryPhoto;
use App\Models\PhotographerImportBatch;
use App\Models\PhotographerImportItem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

it('admin can import photographer zip from server path and sort photos', function () {
    if (! class_exists(ZipArchive::class)) {
        $this->markTestSkipped('ZipArchive extension is required.');
    }

    $admin = makeAdmin();
    $importDir = storage_path('app/imports');
    File::ensureDirectoryExists($importDir);
    $zipPath = $importDir . '/sample-photographer.zip';

    $image = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=');

    $zip = new ZipArchive();
    expect($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE))->toBeTrue();
    $zip->addFromString('reception/photo-001.png', $image);
    $zip->addFromString('__MACOSX/ignored.txt', 'ignored');
    $zip->close();

    $this->actingAs($admin)
        ->post(route('admin.gallery.imports.store'), [
            'name' => 'カメラマンテスト',
            'gallery_category' => 'reception',
            'server_zip_path' => $zipPath,
        ])
        ->assertRedirect();

    $batch = PhotographerImportBatch::firstOrFail();
    expect($batch->imported_count)->toBe(1);
    expect($batch->skipped_count)->toBe(1);
    expect($batch->items()->count())->toBe(1);

    $item = $batch->items()->firstOrFail();
    expect($item->status)->toBe(PhotographerImportItem::STATUS_PENDING);
    expect(Storage::disk('public')->exists($item->file_path))->toBeTrue();

    $this->actingAs($admin)
        ->patch(route('admin.gallery.imports.items.decide', [$batch, $item]), [
            'decision' => 'accept',
        ])
        ->assertRedirect();

    $item->refresh();
    expect($item->status)->toBe(PhotographerImportItem::STATUS_ACCEPTED);
    expect($item->gallery_photo_id)->not->toBeNull();

    $photo = GalleryPhoto::findOrFail($item->gallery_photo_id);
    expect($photo->photo_source)->toBe('photographer');
    expect($photo->gallery_category)->toBe('reception');
    expect($photo->status)->toBe('approved');

    $this->actingAs($admin)
        ->patch(route('admin.gallery.imports.items.decide', [$batch, $item]), [
            'decision' => 'reject',
        ])
        ->assertRedirect();

    $item->refresh();
    expect($item->status)->toBe(PhotographerImportItem::STATUS_REJECTED);
    expect($photo->fresh()->status)->toBe('rejected');
});
