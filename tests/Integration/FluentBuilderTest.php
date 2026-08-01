<?php

use UnnovateBrains\DocumentBuilder\Facades\Document;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;

it('builds documents using a sleek fluent api profile design syntax', function () {
    // Arrange
    $students = [
        ['id' => 1, 'name' => 'John Doe', 'grade' => 'A+']
    ];


    $storage = app(DocumentStorage::class);

    // Act: Fire the exact targeted operational structure profile syntax code!
    $result = Document::pdf()
        ->fromCollection(collect($students))
        ->view('students')
        ->context(['tenant_id' => 'emma', 'school_id' => 'emma-4353', 'user_id' => 263])
        ->filename('fluent_transcript_report')
        ->save();

    // Assert
    expect($result)->not->toBeNull();
    expect($storage->exists('documents/fluent_transcript_report.pdf'))->toBeTrue();
});
