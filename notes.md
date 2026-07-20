document-builder/

├── src/
│
│   ├── DocumentBuilderServiceProvider.php
│   ├── DocumentManager.php
│   │
│   ├── Facades/
│   │   └── Document.php
│   │
│   ├── Contracts/
│   │   ├── DocumentEngine.php
│   │   ├── DocumentStorage.php
│   │   ├── DocumentQueue.php
│   │   ├── DocumentContext.php
│   │   └── Renderer.php
│   │
│   ├── Builder/
│   │   └── DocumentBuilder.php
│   │
│   ├── Pipeline/
│   │   ├── ExecutionPlan.php
│   │   └── Pipeline.php
│   │
│   ├── Sources/
│   │   ├── CollectionSource.php
│   │   ├── QuerySource.php
│   │   ├── ModelSource.php
│   │   └── ArraySource.php
│   │
│   ├── Drivers/
│   │
│   │   ├── Pdf/
│   │   │   ├── MpdfDriver.php
│   │   │   ├── ChromeDriver.php
│   │   │   └── TcpdfDriver.php
│   │   │
│   │   ├── Word/
│   │   │   └── PhpWordDriver.php
│   │   │
│   │   ├── Excel/
│   │   │   └── SpreadsheetDriver.php
│   │   │
│   │   └── Csv/
│   │       └── CsvDriver.php
│   │
│   ├── Queue/
│   │
│   ├── Storage/
│   │
│   └── Context/
│
├── tests/
├── README.md
├── LICENSE
└── composer.json

Replace current ChunkingStage with the pure version above.
Create ChunkExecutor.
Move the old workspace + dispatch logic into QueueChunkExecutor.
Create SyncChunkExecutor.
Then implement MergeStage.