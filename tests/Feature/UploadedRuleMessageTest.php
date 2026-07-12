<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

it('REPRO: uploadedルール失敗時に日本語メッセージが表示される（英語デフォルトにならない）', function () {
    // isValid() が false を返す（PHPレベルのアップロード失敗）ファイルを模倣する
    $file = UploadedFile::fake()->image('bride.jpg');
    $mock = Mockery::mock($file)->makePartial();
    $mock->shouldReceive('isValid')->andReturn(false);
    $mock->shouldReceive('getError')->andReturn(UPLOAD_ERR_INI_SIZE);

    $validator = Validator::make(
        ['bride_photo' => $mock],
        ['bride_photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120'],
        [
            'bride_photo.image'    => '新婦の写真は画像ファイルを選択してください',
            'bride_photo.max'      => '新婦の写真は5MB以下にしてください',
            'bride_photo.uploaded' => '新婦の写真のアップロードに失敗しました。ファイルサイズが大きすぎる可能性があります。時間をおいて再度お試しください',
        ]
    );

    expect($validator->fails())->toBeTrue();
    $message = $validator->errors()->first('bride_photo');
    dump('bride_photo error message', $message);

    expect($message)->not->toContain('failed to upload');
    expect($message)->toContain('アップロードに失敗しました');
});
