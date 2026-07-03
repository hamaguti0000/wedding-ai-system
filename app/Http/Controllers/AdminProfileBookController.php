<?php

namespace App\Http\Controllers;

use App\Models\ProfileBookPage;
use App\Services\PdfToImagesConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class AdminProfileBookController extends Controller
{
    public function edit()
    {
        $pages = ProfileBookPage::orderBy('page_number')->get();

        return view('admin.profile-book', compact('pages'));
    }

    public function upload(Request $request, PdfToImagesConverter $converter)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:20480',
        ], [
            'pdf.required' => 'PDFファイルを選択してください',
            'pdf.mimes'    => 'PDFファイルを選択してください',
            'pdf.max'      => 'ファイルサイズは20MB以内にしてください',
        ]);

        $tmpDir = storage_path('app/tmp/profile-book-'.Str::random(12));

        try {
            $images = $converter->convert($request->file('pdf')->getRealPath(), $tmpDir);

            $newRecords = [];
            foreach ($images as $index => $localPath) {
                $storedPath = 'profile-book/'.Str::random(20).'.jpg';
                Storage::disk('public')->put($storedPath, file_get_contents($localPath));
                $newRecords[] = ['page_number' => $index + 1, 'image_path' => $storedPath];
            }

            $oldPages = ProfileBookPage::all();

            // DB側は「古いページを全消し→新ページ作成」をトランザクションで一貫させる。
            // 画像ファイル自体はこの前段で既に生成済みなので、ここが失敗しても
            // 既存のプロフィールブックの表示は壊れない（新しい画像が孤立するだけ）。
            DB::transaction(function () use ($newRecords): void {
                ProfileBookPage::query()->delete();
                foreach ($newRecords as $record) {
                    ProfileBookPage::create($record);
                }
            });

            foreach ($oldPages as $old) {
                Storage::disk('public')->delete($old->image_path);
            }

            return back()->with('success', count($newRecords).'ページのプロフィールブックを更新しました');
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'PDFの変換に失敗しました。ファイルを確認してもう一度お試しください。');
        } finally {
            File::deleteDirectory($tmpDir);
        }
    }

    public function destroy()
    {
        $pages = ProfileBookPage::all();

        foreach ($pages as $page) {
            Storage::disk('public')->delete($page->image_path);
        }

        ProfileBookPage::query()->delete();

        return back()->with('success', 'プロフィールブックを削除しました');
    }
}
