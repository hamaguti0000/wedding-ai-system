@extends('layouts.app')
@section('title', 'カメラマン写真取り込み | Admin')

@push('styles')
<style>
.pi-wrap { max-width: 1120px; margin: 0 auto; padding: 28px 18px 56px; }
.pi-head { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; margin-bottom:20px; }
.pi-kicker { color:#b38b59; letter-spacing:.18em; font-size:.72rem; font-weight:800; }
.pi-title { margin:6px 0 8px; color:#2f261f; font-size:1.7rem; }
.pi-lead { margin:0; color:#75685d; line-height:1.8; font-size:.92rem; }
.pi-card { border:1px solid #eadbc8; border-radius:18px; background:#fffdf9; box-shadow:0 14px 34px rgba(61,47,37,.07); padding:20px; margin-bottom:18px; }
.pi-alert { border-radius:14px; padding:12px 14px; margin-bottom:14px; font-weight:800; }
.pi-alert.success { background:#edf8f0; color:#247346; border:1px solid #ccebd5; }
.pi-alert.error { background:#fff1f1; color:#b42318; border:1px solid #f2cccc; }
.pi-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.pi-field label { display:block; color:#4c3b2d; font-size:.82rem; font-weight:800; margin-bottom:7px; }
.pi-field input, .pi-field select { width:100%; border:1px solid #e3d3bf; border-radius:12px; padding:12px 13px; background:#fff; font-size:.92rem; }
.pi-field small { display:block; margin-top:6px; color:#9a8c7e; line-height:1.55; }
.pi-actions { display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin-top:16px; }
.pi-btn { appearance:none; border:0; border-radius:999px; padding:12px 18px; font-weight:900; cursor:pointer; display:inline-flex; align-items:center; gap:8px; text-decoration:none; }
.pi-btn.primary { background:#b38b59; color:#fff; }
.pi-btn.ghost { border:1px solid #dec9ae; color:#775b3f; background:#fff; }
.pi-note { display:flex; gap:12px; align-items:flex-start; border:1px dashed #e1c49d; background:#fffaf1; border-radius:15px; padding:13px 14px; color:#766454; line-height:1.7; font-size:.84rem; margin-top:14px; }
.pi-batches { display:grid; gap:12px; }
.pi-batch { border:1px solid #eadbc8; border-radius:16px; background:#fff; padding:15px; display:grid; grid-template-columns:1fr auto; gap:12px; align-items:center; }
.pi-batch h3 { margin:0 0 4px; font-size:1rem; color:#332820; }
.pi-batch p { margin:0; color:#8c7d6e; font-size:.78rem; }
.pi-stats { display:flex; flex-wrap:wrap; gap:7px; margin-top:10px; }
.pi-pill { border-radius:999px; padding:5px 9px; background:#f7efe5; color:#795d3f; font-size:.74rem; font-weight:900; }
.pi-pill.pending { background:#fff7d8; color:#926707; }
.pi-pill.accepted { background:#eaf8ef; color:#237944; }
.pi-pill.rejected { background:#fff0f0; color:#bd3429; }
@media (max-width: 760px) {
  .pi-head, .pi-batch { grid-template-columns:1fr; display:block; }
  .pi-grid { grid-template-columns:1fr; }
  .pi-btn { width:100%; justify-content:center; }
  .pi-batch .pi-actions { margin-top:12px; }
}
</style>
@endpush

@section('content')
<div class="pi-wrap">
    <div class="pi-head">
        <div>
            <div class="pi-kicker">PHOTOGRAPHER IMPORT</div>
            <h1 class="pi-title">カメラマン写真取り込み</h1>
            <p class="pi-lead">ZIPを解凍して一時一覧に読み込み、公開する写真だけを仕分けます。採用するまでゲスト側には表示されません。</p>
        </div>
        <a href="{{ route('admin.gallery') }}" class="pi-btn ghost"><i class="fa-solid fa-arrow-left"></i> ギャラリー管理へ</a>
    </div>

    @if (session('success')) <div class="pi-alert success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="pi-alert error">{{ session('error') }}</div> @endif

    <section class="pi-card">
        <form method="POST" action="{{ route('admin.gallery.imports.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="pi-grid">
                <div class="pi-field">
                    <label for="name">取り込み名</label>
                    <input id="name" name="name" value="{{ old('name') }}" placeholder="例：カメラマン写真 披露宴">
                </div>
                <div class="pi-field">
                    <label for="gallery_category">公開時のカテゴリ</label>
                    <select id="gallery_category" name="gallery_category">
                        @foreach ($categoryOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('gallery_category', 'reception') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="pi-field">
                    <label for="zip_file">ZIPファイル</label>
                    <input id="zip_file" type="file" name="zip_file" accept=".zip,application/zip">
                    <small>ブラウザアップロードが失敗する場合は、下のサーバパス指定を使います。</small>
                </div>
                <div class="pi-field">
                    <label for="server_zip_path">サーバ上のZIPパス</label>
                    <input id="server_zip_path" name="server_zip_path" value="{{ old('server_zip_path') }}" placeholder="{{ $serverImportPath }}/camera.zip">
                    <small>安全のため {{ $serverImportPath }} 配下のZIPだけ指定できます。</small>
                </div>
            </div>
            <div class="pi-note">
                <i class="fa-solid fa-circle-info"></i>
                <div>1.8GB級のZIPは画面アップロードより、SFTP等で <code>{{ $serverImportPath }}</code> に置いてからパス指定する方が安定します。解凍後は60枚ずつ仕分けできます。</div>
            </div>
            <div class="pi-actions">
                <button class="pi-btn primary" type="submit" @disabled(! $zipAvailable)><i class="fa-solid fa-file-zipper"></i> ZIPを解凍して読み込む</button>
                @unless($zipAvailable)
                    <span class="pi-alert error">ZipArchive が無効です。サーバ設定が必要です。</span>
                @endunless
            </div>
            @error('zip_file') <p class="form-error">{{ $message }}</p> @enderror
            @error('server_zip_path') <p class="form-error">{{ $message }}</p> @enderror
        </form>
    </section>

    <section class="pi-card">
        <h2 class="pi-title" style="font-size:1.2rem">取り込み履歴</h2>
        <div class="pi-batches">
            @forelse ($batches as $batch)
                <article class="pi-batch">
                    <div>
                        <h3>{{ $batch->name }}</h3>
                        <p>{{ $batch->original_filename ?: 'ZIP名なし' }} / {{ $batch->created_at?->format('Y/m/d H:i') }}</p>
                        <div class="pi-stats">
                            <span class="pi-pill">全{{ $batch->items_count }}枚</span>
                            <span class="pi-pill pending">未仕分け{{ $batch->pending_count }}枚</span>
                            <span class="pi-pill accepted">公開{{ $batch->accepted_count }}枚</span>
                            <span class="pi-pill rejected">除外{{ $batch->rejected_count }}枚</span>
                            <span class="pi-pill">{{ $categoryOptions[$batch->gallery_category] ?? 'その他' }}</span>
                        </div>
                        @if ($batch->error_message)
                            <p style="color:#b42318;margin-top:8px">{{ $batch->error_message }}</p>
                        @endif
                    </div>
                    <div class="pi-actions">
                        <a class="pi-btn primary" href="{{ route('admin.gallery.imports.sort', $batch) }}"><i class="fa-solid fa-list-check"></i> 仕分け</a>
                    </div>
                </article>
            @empty
                <p class="pi-lead">まだ取り込みはありません。</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
