<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Services;

use Illuminate\Support\Facades\DB;
use UnnovateBrains\DocumentBuilder\Jobs\MergeDocumentJob;

final class BatchManager
{
    public function createBatch(array $data): string
    {
        $uuid = \Illuminate\Support\Str::uuid()->toString();
        
        DB::table('document_batches')->insert([
            'id' => $uuid,
            'tenant_id' => $data['tenant_id'],
            'status' => 'processing',
            'total_chunks' => $data['total_chunks'],
            // ... other fields
        ]);
        
        return $uuid;
    }

    public function markChunkComplete(string $batchId, int $chunkNumber, string $path): void
    {
        DB::transaction(function () use ($batchId, $chunkNumber, $path) {
            // 1. Mark chunk as done
            DB::table('document_chunks')
                ->where('document_batch_id', $batchId)
                ->where('chunk_number', $chunkNumber)
                ->update([
                    'status' => 'completed',
                    'storage_path' => $path,
                    'completed_at' => now()
                ]);

            // 2. Check if this was the last chunk
            $stats = DB::table('document_batches')
                ->where('id', $batchId)
                ->select('total_chunks')
                ->first();

            $completedCount = DB::table('document_chunks')
                ->where('document_batch_id', $batchId)
                ->where('status', 'completed')
                ->count();

            // 3. Dispatch Merge if finished
            if ($completedCount >= $stats->total_chunks) {
                DB::table('document_batches')->where('id', $batchId)->update(['status' => 'merging']);
                dispatch(new MergeDocumentJob($batchId));
            }
        });
    }
}