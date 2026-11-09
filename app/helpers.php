<?php

if (! function_exists('versioned_asset')) {
    /**
     * CSS/JS のブラウザキャッシュをデプロイごとに確実に更新する。
     */
    function versioned_asset(string $path): string
    {
        $fullPath = public_path($path);
        $version = is_file($fullPath)
            ? filemtime($fullPath) . '-' . filesize($fullPath)
            : time();

        return asset($path) . '?v=' . $version;
    }
}

if (! function_exists('css_asset')) {
    /**
     * デプロイ後にブラウザが古いCSSをキャッシュし続けてレイアウト崩れに
     * 見えるトラブルを防ぐため、ファイルの更新時刻をクエリ文字列に付与する。
     */
    function css_asset(string $path): string
    {
        return versioned_asset($path);
    }
}
