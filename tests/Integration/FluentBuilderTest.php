<?php

use UnnovateBrains\DocumentBuilder\Facades\Document;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;

it('builds documents using a sleek fluent api profile design syntax', function () {
    // Arrange
    $students = [
        ['id' => 1, 'name' => 'John Doe', 'grade' => 'A+']
    ];

    $mockStorage = new \UnnovateBrains\DocumentBuilder\Tests\Mocks\MockVirtualStorage();
    app()->instance(DocumentStorage::class, $mockStorage);

    // Act: Fire the exact targeted operational structure profile syntax code!
    $result = Document::pdf()
        ->fromCollection(collect($students))
        ->view('transcripts.student')
        ->engine('mpdf')
        ->filename('fluent_transcript_report')
        ->save();

    // Assert
    expect($result)->not->toBeNull();
    expect($result->getContent())->toStartWith('%PDF-');
    expect($mockStorage->exists('documents/fluent_transcript_report.pdf'))->toBeTrue();
});