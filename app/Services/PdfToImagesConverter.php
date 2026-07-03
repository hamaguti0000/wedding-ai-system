<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class PdfToImagesConverter
{
    private const DPI = 150;

    private const TIMEOUT_SECONDS = 120;

    /**
     * PDFの各ページをJPEG画像に変換する（poppler-utils の pdftoppm を使用）。
     *
     * @return array<int, string> 生成された画像ファイルの絶対パス（ページ順）
     */
    public function convert(string $pdfAbsolutePath, string $outputDir): array
    {
        if (! is_dir($outputDir) && ! mkdir($outputDir, 0755, true) && ! is_dir($outputDir)) {
            throw new RuntimeException("出力ディレクトリを作成できません: {$outputDir}");
        }

        $prefix = $outputDir.'/page';

        // 配列引数で渡すことでシェルインジェクションを避ける（Symfony Processはescapeshellargを内部で行う）
        $process = new Process([
            'pdftoppm', '-jpeg', '-r', (string) self::DPI, $pdfAbsolutePath, $prefix,
        ]);
        $process->setTimeout(self::TIMEOUT_SECONDS);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $files = glob($prefix.'-*.jpg') ?: [];

        if ($files === []) {
            throw new RuntimeException('PDFからページ画像を生成できませんでした。');
        }

        // pdftoppmはページ数に応じて出力ファイル名のゼロ埋め桁数を変える（-1 / -01 / -001）ため、
        // ファイル名の文字列ソートではなく、抽出したページ番号の数値でソートする。
        usort($files, fn (string $a, string $b): int => $this->extractPageNumber($a) <=> $this->extractPageNumber($b));

        return $files;
    }

    private function extractPageNumber(string $filename): int
    {
        preg_match('/-(\d+)\.jpg$/', $filename, $matches);

        return (int) ($matches[1] ?? 0);
    }
}
