<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use UnnovateBrains\DocumentBuilder\Concerns\ExcelBuilderConcern;
use UnnovateBrains\DocumentBuilder\Concerns\ExcelBuilderTrait;
use UnnovateBrains\DocumentBuilder\Concerns\ImageBuilderTrait;
use UnnovateBrains\DocumentBuilder\Concerns\PdfBuilderTrait;
use UnnovateBrains\DocumentBuilder\Contracts\Source;
use UnnovateBrains\DocumentBuilder\Services\DocumentTransformer;
use UnnovateBrains\DocumentBuilder\Sources\ArraySource;
use UnnovateBrains\DocumentBuilder\Sources\QuerySource;
use UnnovateBrains\DocumentBuilder\Support\DocumentMetadata;
use UnnovateBrains\DocumentBuilder\Support\DocumentResult;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;
use UnnovateBrains\DocumentBuilder\Support\SourceRegistry;
use Illuminate\Support\Str;

/**
 * Class DocumentBuilder
 *
 * A fully fluent, immutable builder used to construct a document's execution blueprint.
 * Every modifier method clones the current instance to maintain transactional state isolation.
 *
 * @package UnnovateBrains\DocumentBuilder
 */

class DocumentBuilder
{

    use ExcelBuilderTrait;
    use PdfBuilderTrait;
    use ImageBuilderTrait;

    protected ?Source $source = null;
    protected ?string $view = null;
    protected ?string $templateEngine = 'blade';
    protected array $viewData = [];
    protected DocumentMetadata $metadata;
    protected ?string $filename = null;
    protected ?string $disk = null;
    protected ?int $chunkSize = null;
    protected array $options = [];
    /**
     * Merge behaviour:
     *
     * null  = automatic decision
     * true  = force merge
     * false = disable merge
     */
    protected ?bool $shouldMerge = null;

    // Changing this to a nullable boolean enables three-state logic:
    // null = Auto-evaluate rules, true = Force Queue, false = Force Sync (Bypass rules)
    protected ?bool $shouldQueue = null;

    protected ?string $outputPath = null;
    protected array $context = [];
    protected array $transformers = [];
    protected DocumentManager $manager;
    /**
     * Driver specific configuration.
     *
     * Example:
     *
     * [
     *    'pdf' => [
     *         'orientation' => 'landscape'
     *    ],
     *
     *    'xlsx' => [
     *         'auto_size' => true
     *    ]
     * ]
     */
    protected array $driverConfig = [];
    /**
     * @param string $type Document format type.
     * @param string $engine Default resolved engine for the document type.
     *
     * The engine is resolved from configuration by the facade,
     * but developers may override it using ->engine().
     */
    public function __construct(
        protected string $type,
        protected string $engine,
        ?DocumentManager $manager = null
    ) {
        $this->manager =
            $manager ?? app(DocumentManager::class);

        $this->metadata =
            new DocumentMetadata();
    }

    /**
     * Set the explicit compiling driver engine (e.g., 'mpdf', 'chrome').
     */
    public function engine(string $engine): self
    {
        $clone = clone $this;
        $clone->engine = $engine;
        return $clone;
    }

    public function option(
        string $key,
        mixed $value
    ): self {

        $clone = clone $this;

        $clone->options[$key] = $value;

        return $clone;
    }

    public function options(
        array $options
    ): self {

        $clone = clone $this;

        $clone->options = array_merge(
            $clone->options,
            $options
        );

        return $clone;
    }


    /**
     * Attach execution context information.
     */
    public function context(array $context): self
    {
        $clone = clone $this;
        $clone->context = array_merge($clone->context, $context);
        return $clone;
    }

    /**
     * Add a single context value.
     */
    public function withContext(string $key, mixed $value): self
    {
        $clone = clone $this;
        $clone->context[$key] = $value;
        return $clone;
    }

    /**
     * Define the structural view template layout and its primary layout parameters.
     */
    public function view(string $view, array $data = []): self
    {
        $clone = clone $this;
        $clone->view = $view;
        $clone->viewData = array_merge($clone->viewData, $data);
        return $clone;
    }

    /**
     * Set the template rendering engine (e.g., 'blade', 'twig', 'plates').
     */
    public function templateEngine(string $engine): self
    {
        $clone = clone $this;
        $clone->templateEngine = $engine;
        return $clone;
    }

    public function fromArray(array $data): self
    {
        $clone = clone $this;
        $clone->source = new ArraySource($data);
        return $clone;
    }

    public function fromCollection(Collection $collection): self
    {
        $clone = clone $this;
        $clone->source = new ArraySource($collection->toArray());
        return $clone;
    }

    public function fromQuery(string $model): self
    {
        $clone = clone $this;
        $clone->source = new QuerySource($model);
        return $clone;
    }

    public function fromModel(Model $model): self
    {
        $clone = clone $this;
        $clone->source = new \UnnovateBrains\DocumentBuilder\Sources\ModelSource($model);
        return $clone;
    }

    public function fromJson(string $json): self
    {
        $clone = clone $this;
        $clone->source = new \UnnovateBrains\DocumentBuilder\Sources\JsonSource($json);
        return $clone;
    }

    public function fromSource(Source $source): self
    {
        $clone = clone $this;
        $clone->source = $source;
        return $clone;
    }

    public static function registerSource(string $type, string $sourceClass): void
    {
        app(SourceRegistry::class)->register($type, $sourceClass);
    }

    /**
     * Set tenant execution context.
     */
    public function tenant(mixed $tenant): self
    {
        $clone = clone $this;
        $clone->context['tenant_id'] = is_object($tenant) ? $tenant->getKey() : $tenant;
        return $clone;
    }

    /**
     * Set school execution context.
     */
    public function school(mixed $school): self
    {
        $clone = clone $this;
        $clone->context['school_id'] = is_object($school) ? $school->getKey() : $school;
        return $clone;
    }

    /**
     * Set academic year context.
     */
    public function academicYear(mixed $year): self
    {
        $clone = clone $this;
        $clone->context['academic_year_id'] = is_object($year) ? $year->getKey() : $year;
        return $clone;
    }

    /**
     * Set term context.
     */
    public function term(mixed $term): self
    {
        $clone = clone $this;
        $clone->context['term_id'] = is_object($term) ? $term->getKey() : $term;
        return $clone;
    }

    /**
     * Set active user context.
     */
    public function user(mixed $user): self
    {
        $clone = clone $this;
        $clone->context['user_id'] = is_object($user) ? $user->getKey() : $user;
        return $clone;
    }

    /**
     * Set document locale.
     */
    public function locale(string $locale): self
    {
        $clone = clone $this;
        $clone->context['locale'] = $locale;
        return $clone;
    }

    /**
     * Set document timezone.
     */
    public function timezone(string $timezone): self
    {
        $clone = clone $this;
        $clone->context['timezone'] = $timezone;
        return $clone;
    }

    /**
     * Inject custom system auditing fields or layout metrics into the document metadata.
     */
    public function metadata(array|DocumentMetadata $metadata): self
    {
        $clone = clone $this;
        if ($metadata instanceof DocumentMetadata) {
            $clone->metadata = $metadata;
        } else {
            $clone->metadata = new DocumentMetadata(
                array_merge($clone->metadata->toArray(), $metadata)
            );
        }
        return $clone;
    }

    /**
     * Define the target document filename identifier and target storage partition.
     */
    public function filename(string $filename): self
    {
        $clone = clone $this;
        $clone->filename = $filename;


        if (str_contains($filename, '.') && Str::afterLast($filename, '.') !== $this->type)
            $clone->filename = Str::beforeLast($filename, '.') . '.' . $this->type;

        return $this->saveTo($clone->filename);
    }


    /**
     * Restrict record streams into a size-capped block sequence.
     *
     * Chunking automatically enables merging unless the developer
     * explicitly configured merge behavior.
     */
    public function chunk(int $size): self
    {
        $clone = clone $this;

        $clone->chunkSize = $size;


        /*
    |--------------------------------------------------------------------------
    | Automatic merge behaviour
    |--------------------------------------------------------------------------
    |
    | When chunking is enabled, multiple artifacts are created.
    | By default they should be merged into one final document.
    |
    | However:
    |
    | Document::pdf()
    |     ->chunk(50)
    |     ->merge(false)
    |
    | must remain respected.
    |
    */
        if ($clone->shouldMerge === null) {
            $clone->shouldMerge = true;
        }


        return $clone;
    }

    /**
     * Determine if individual chunk generation segments require structural concatenation.
     */
    public function merge(bool $merge = true): self
    {
        $clone = clone $this;
        $clone->shouldMerge = $merge;
        return $clone;
    }

    /**
     * Configure the processing execution plan to run asynchronously in the background.
     */
    public function queue(): self
    {
        $clone = clone $this;
        $clone->shouldQueue = true;
        return $clone;
    }

    /**
     * Force synchronous execution in the foreground, bypassing all auto-queueing logic.
     */
    public function sync(): self
    {
        $clone = clone $this;
        $clone->shouldQueue = false;
        return $clone;
    }

    /**
     * Fluent alias to explicitly bypass queueing rules.
     */
    public function withoutQueue(): self
    {
        return $this->sync();
    }

    public function transform(callable|DocumentTransformer $transformer): self
    {
        $clone = clone $this;
        $clone->transformers[] = $transformer;
        return $clone;
    }

    /**
     * Evaluates auto-queueing rules based on developer overrides and context structures.
     */
    protected function resolveQueueRequirement(): bool
    {
        // 1. Explicit Override: User called ->queue()
        if ($this->shouldQueue === true) {
            return true;
        }

        // 2. Explicit Override: User called ->sync() or ->withoutQueue()
        if ($this->shouldQueue === false) {
            return false;
        }

        // 3. Fallback Dynamic Rules: Decide based on size or active chunk parameters
        if ($this->chunkSize !== null) {
            return true;
        }

        if (!$this->source) {
            return false;
        }

        if (!method_exists($this->source, 'count')) {
            return false;
        }

        return $this->source->count() >= config(
            'document-builder.queue.threshold',
            500
        );
    }

    /**
     * Compile the immutable plan configuration details.
     */
    public function compilePlan(): ExecutionPlan
    {
        $shouldQueue = $this->resolveQueueRequirement();
        return new ExecutionPlan(
            type: $this->type,
            engine: $this->engine,
            source: $this->source,
            view: $this->view,
            templateEngine: $this->templateEngine ?? 'blade',
            viewData: $this->viewData,
            chunkSize: $this->chunkSize,
            shouldMerge: $this->shouldMerge,
            outputFilename: $this->filename,
            outputPath: $this->outputPath,
            disk: $this->disk ?? 'local',
            shouldQueue: $shouldQueue,
            metadata: $this->metadata,
            driverConfig: $this->driverConfig,
            context: $this->context,
            transformers: $this->transformers,
        );
    }

    /**
     * Pass configuration options directly to the selected document engine.
     */
    public function driverConfig(array $config): self
    {
        $clone = clone $this;


        $clone->driverConfig[$this->type] =
            array_merge(
                $clone->driverConfig[$this->type] ?? [],
                $config
            );


        return $clone;
    }

    public function getDriverConfig(): array
    {
        return $this->driverConfig[$this->type] ?? [];
    }


    /**
     * Set a single driver-specific option.
     *
     * Example:
     *
     * ->setDriverOption('xlsx', 'auto_size', true)
     */
    public function setDriverOption(
        string $driver,
        string $key,
        mixed $value
    ): self {

        $clone = clone $this;


        $clone->driverConfig[$driver][$key] =
            $value;


        return $clone;
    }



    /**
     * Merge multiple driver-specific options.
     *
     * Example:
     *
     * ->setDriverOptions('xlsx', [
     *     'auto_size'=>true,
     *     'creator'=>'SchoolPalm'
     * ])
     */
    public function setDriverOptions(
        string $driver,
        array $options
    ): self {

        $clone = clone $this;


        $clone->driverConfig[$driver] =
            array_merge(
                $clone->driverConfig[$driver] ?? [],
                $options
            );


        return $clone;
    }

    public function saveTo(string $path): self
    {
        $clone = clone $this;
        $clone->outputPath = ltrim($path, '/');
        return $clone;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // TERMINAL ACTION METHODS (Routed completely through DocumentManager)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Terminal action:
     * Executes the document generation workflow via DocumentManager.
     */
    public function save(): mixed
    {

        return $this->manager->generate($this->compilePlan());
    }

    /**
     * Resolve appropriate Content-Type header based on document type.
     */
    protected function resolveContentType(string $type, string $extension): string
    {
        return match (strtolower($type)) {
            'pdf' => 'application/pdf',
            'xlsx', 'excel', 'xls' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv' => 'text/csv',
            'docx', 'word', 'doc' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'html', 'htm' => 'text/html',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'avif' => 'image/avif',
            'bmp' => 'image/bmp',
            'tiff', 'tif' => 'image/tiff',
            'image' => match (strtolower($extension)) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                'gif' => 'image/gif',
                'avif' => 'image/avif',
                'bmp' => 'image/bmp',
                'tiff', 'tif' => 'image/tiff',
                default => 'image/png',
            },
            default => 'application/octet-stream',
        };
    }



    /**
     * Terminal action: Processes the document and fires a native HTTP user download stream.
     */
    /**
     * Terminal action: Processes the document and fires a native HTTP user download stream.
     */
    public function download(bool $deleteAfter = true): \UnnovateBrains\DocumentBuilder\Support\DocumentResponse
    {
        $result = $this->sync()->save();
        $content = $result->getContent();
        $filename = $result->getFilename();
        $contentType = $this->resolveContentType($result->getType(), $result->getExtension());

        $path = method_exists($result, 'getPath') ? $result->getPath() : null;
        $disk = method_exists($result, 'getDisk') ? $result->getDisk() : ($this->disk ?? 'local');

        $callback = function () use ($content) {
            $content->writeTo(function ($stream) {
                while (!feof($stream)) {
                    echo fread($stream, 8192);
                    flush();
                }
            });
        };

        $response = new \UnnovateBrains\DocumentBuilder\Support\DocumentResponse(
            callback: $callback,
            status: 200,
            headers: ['Content-Type' => $contentType],
            filename: $filename,
            isAttachment: true,
            storagePath: $path,
            disk: $disk
        );

        if ($deleteAfter) {
            $response->deleteAfter();
        }

        return $response;
    }

    /**
     * Terminal action: Processes the document and streams raw inline binaries straight back to the viewport.
     */
    public function stream(bool $deleteAfter = true): \UnnovateBrains\DocumentBuilder\Support\DocumentResponse
    {
        $result = $this->sync()->save();
        $content = $result->getContent();
        $filename = $result->getFilename();
        $contentType = $this->resolveContentType($result->getType(), $result->getExtension());

        $path = method_exists($result, 'getPath') ? $result->getPath() : null;
        $disk = method_exists($result, 'getDisk') ? $result->getDisk() : ($this->disk ?? 'local');

        $callback = function () use ($content) {
            $content->writeTo(function ($stream) {
                while (!feof($stream)) {
                    echo fread($stream, 8192);
                    flush();
                }
            });
        };

        $response = new \UnnovateBrains\DocumentBuilder\Support\DocumentResponse(
            callback: $callback,
            status: 200,
            headers: [
                'Content-Type' => $contentType,
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ],
            filename: $filename,
            isAttachment: false,
            storagePath: $path,
            disk: $disk
        );

        if ($deleteAfter) {
            $response->deleteAfter();
        }

        return $response;
    }

    /**
     * Terminal action: Forces compilation into a job entity wrapper and dispatches it immediately via DocumentManager.
     */
    public function dispatch(): mixed
    {
        return $this->manager->dispatch($this->queue()->compilePlan());
    }
}
