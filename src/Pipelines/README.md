# Document Pipeline Architecture

The Document Builder package uses a **pipeline-based document generation architecture**.  
Every document generation request passes through a sequence of specialized stages, where each stage has a single responsibility.

The pipeline is responsible for transforming a high-level document definition into a final generated artifact such as:

- PDF
- Excel
- Word
- CSV
- Other supported document formats

The architecture separates concerns such as:

- context resolution
- data loading
- chunk processing
- data transformation
- template rendering
- document compilation
- metadata generation
- output storage

This makes the system extensible and allows new drivers, renderers, storage backends, and processing strategies to be added without changing the core pipeline.

---

# Pipeline Lifecycle

The default document pipeline executes the following stages:

```text
EnterContextStage
        |
        v
ResolveContextStage
        |
        v
ResolveSourceStage
        |
        v
ChunkingStage
        |
        v
ConfigureEngineStage
        |
        v
TransformStage
        |
        v
RenderTemplateStage
        |
        v
CompileDriverStage
        |
        v
MetadataStage
        |
        v
GenerateDocumentStage
        |
        v
MergeStage
        |
        v
OutputStage
        |
        v
LeaveContextStage
```


# Document Generation Flow

The Document Builder pipeline supports two execution modes:

1. **Standard generation** — the complete dataset is processed in one pipeline execution.
2. **Chunked generation** — large datasets are split into independent chunks, processed separately, then merged.

The pipeline stages remain the same conceptually, but chunking changes the execution strategy.

---

# Standard Document Generation Flow

When chunking is not enabled, the document follows a straight pipeline execution.

```mermaid
flowchart TD

    A[Document Request] --> B[EnterContextStage]

    B --> C[ResolveContextStage]

    C --> D[ResolveSourceStage]

    D --> E[ConfigureEngineStage]

    E --> F[TransformStage]

    F --> G[RenderTemplateStage]

    G --> H[CompileDriverStage]

    H --> I[MetadataStage]

    I --> J[GenerateDocumentStage]

    J --> K[MergeStage]

    K --> L[OutputStage]

    L --> M[LeaveContextStage]

    M --> N[Final Document]
```

# Chunked generation 

```mermaid
    flowchart TD

    A[Document Request] --> B[EnterContextStage]

    B --> C[ResolveContextStage]

    C --> D[ResolveSourceStage]

    D --> E[ChunkingStage]

    E --> F{Chunking Enabled?}

    F -->|No| G[Continue Normal Pipeline]

    F -->|Yes| H[Create Batch Execution]


    H --> I[Split Records Into Chunks]

    I --> J[Chunk 1]

    I --> K[Chunk 2]

    I --> L[Chunk N]


    J --> M[Sync/Queue Chunk Executor]

    K --> M

    L --> M


    M --> N[Generate Chunk Documents]

    N --> O[MergeStage]

    O --> P[OutputStage]

    P --> Q[Final Document]
```


```mermaid
flowchart TD

    A[DocumentManager::generate] --> B{Execution Mode}

    B -->|sync| C[Generate Immediately]
    C --> D[DocumentResult]

    B -->|queue/chunk| E[Create Batch]
    E --> F[DocumentBatchRepository]
    F --> G[Save Batch Metadata]

    G --> H[Dispatch Jobs]
    H --> I[Update Progress]

    I --> J[Completed]
    J --> K[Retrieve Final Document]
```