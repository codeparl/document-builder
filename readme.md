# Unnovate Brains — Document Builder

Comprehensive documentation, examples, and API usage for the Document Builder package.

This package provides a driver-based, contract-first document execution framework. Use the fluent Document facade to build an execution plan and run the document pipeline (render, store, merge, queue, etc.). The core is Laravel-friendly (uses a facade and service provider) but stays decoupled via contracts.

---

## Quick links

- Full HTML documentation: [docs/documentation.html](E:/dev-server/www/packages/document-builder/docs/documentation.html)
- Source: [DocumentBuilder class](E:/dev-server/www/packages/document-builder/src/DocumentBuilder.php)
- Facade: [Document](E:/dev-server/www/packages/document-builder/src/Facades/Document.php)
- Manager: [DocumentManager](E:/dev-server/www/packages/document-builder/src/DocumentManager.php)

---

## Installation

Install via Composer inside your application:

```bash
composer require unnovatebrains/document-builder
```

If using Laravel, the package provides a service provider — it may be auto-discovered. Publish configuration when available.

---

## Overview / Concepts

- Document facade (Document) produces a fluent, immutable DocumentBuilder for a document type (pdf, xlsx, csv, docx, html, image).
- DocumentBuilder collects options and compiles an immutable ExecutionPlan.
- DocumentManager executes the plan via pipeline processors, drivers, and storage.
- Drivers implement format-specific rendering (mPDF, Chrome, TCPDF, PHPWord, PhpSpreadsheet, native CSV, etc.).
- Storage and queue are abstracted by contracts so implementations can vary (local, S3, MinIO, Laravel Queue, Redis, SQS).

---

## Public API — Basic usage

The package exposes the Document facade. Create builders for different types:

- Document::pdf()
- Document::excel()
- Document::csv()
- Document::word()
- Document::html()
- Document::image()

Example — build and save a PDF synchronously:

```php
use UnnovateBrains\DocumentBuilder\Facades\Document;

$result = Document::pdf()
    ->fromCollection($students)
    ->view('reports.students')
    ->engine('mpdf') // optional: overrides configured default
    ->filename('students-report.pdf')
    ->save();

// $result is a DocumentResult-like object with metadata/getContent/getFilename methods
```

Stream download (HTTP response):

```php
return Document::pdf()
    ->fromCollection($students)
    ->view('reports.students')
    ->download();
```

Inline stream (open in browser):

```php
return Document::pdf()
    ->fromCollection($students)
    ->view('reports.students')
    ->stream();
```

Dispatch to queue (create a job and dispatch):

```php
Document::pdf()
    ->fromCollection($students)
    ->view('reports.students')
    ->chunk(200)
    ->merge(true)
    ->queue()
    ->dispatch();
```

---

## Public API — Builder methods (reference)

These methods are available on DocumentBuilder (fluent, immutable — each call returns a cloned instance):

- engine(string $engine): Set the driver engine (e.g., 'mpdf', 'chrome').
- option(string $key, mixed $value) / options(array $options): Pass generic options consumed by the pipeline/driver.
- context(array $context) / withContext(string $key, mixed $value): Attach execution context (tenant, school, user, locale, timezone, etc.).
- view(string $view, array $data = []): Template/view name and initial data for rendering.
- templateEngine(string $engine): Template renderer for views ('blade', 'twig', ...).
- fromArray(array $data), fromCollection(Collection $collection), fromQuery(string $model), fromModel(Model $model), fromJson(string $json), fromSource(Source $source): Set the document data source.
- tenant(mixed $tenant), school(mixed $school), academicYear(mixed $year), term(mixed $term), user(mixed $user): Common context helpers.
- locale(string $locale), timezone(string $timezone): Context helpers.
- metadata(array|DocumentMetadata $metadata): Inject metadata.
- filename(string $filename): Set output filename.
- chunk(int $size): Enable chunking (creates multiple artifacts). Automatically sets merge unless overridden.
- merge(bool $merge = true): Force merge behavior for chunked outputs.
- queue(): Force queue execution.
- sync() / withoutQueue(): Force synchronous execution.
- transform(callable|DocumentTransformer $transformer): Add a transformer to modify data before rendering.
- driverConfig(array $config), setDriverOption(string $driver, string $key, mixed $value), setDriverOptions(string $driver, array $options): Configure engine-specific settings.
- saveTo(string $path): Set output path on the configured storage disk.

Terminal actions (execute or enqueue):
- save(): Execute generation (sync or background depending on decision) and return a result or queued job descriptor.
- download(): Stream a download HTTP response (sync).
- stream(): Stream inline HTTP response (sync).
- dispatch(): Force job creation and dispatch via DocumentManager.

See the implementation: [src/DocumentBuilder.php](E:/dev-server/www/packages/document-builder/src/DocumentBuilder.php)

---

## Examples — practical patterns

1) Generate a single PDF and return for download

```php
return Document::pdf()
    ->fromModel($invoice)
    ->view('documents.invoice', ['includeTotals' => true])
    ->filename("invoice-{$invoice->id}.pdf")
    ->download();
```

2) Generate many PDFs in chunks and merge into one final file (queued)

```php
Document::pdf()
    ->fromQuery(App\Models\Student::class)
    ->view('reports.student-list')
    ->chunk(250)
    ->merge(true)
    ->filename('students-full.pdf')
    ->queue()
    ->dispatch();
```

3) Create an Excel workbook from a query and save to a storage path

```php
$result = Document::excel()
    ->fromQuery(App\Models\Student::class)
    ->setDriverOptions('xlsx', ['auto_size' => true, 'creator' => 'SchoolPalm'])
    ->filename('students.xlsx')
    ->saveTo('reports/2026/')
    ->save();

// Inspect $result->getPath() or $result->getFilename() depending on the implementation
```

4) Generate a CSV from an array and return the result

```php
$csv = Document::csv()
    ->fromArray([['name'=>'Alice','score'=>92], ['name'=>'Bob','score'=>85]])
    ->filename('scores.csv')
    ->save();
```

5) Use transformers to normalize data before rendering

```php
Document::pdf()
    ->fromCollection($students)
    ->transform(function($row) {
        $row['full_name'] = $row['first_name'] . ' ' . $row['last_name'];
        return $row;
    })
    ->view('reports.students')
    ->save();
```

---

## Advanced topics

- Driver configuration: Use driverConfig/setDriverOption(s) to pass engine-specific options (paper size, orientation, PDF margins, PhpSpreadsheet options, etc.).
- Sources: Register custom sources via DocumentBuilder::registerSource(
  string $type, string $sourceClass) to support special data providers.
- Context and multi-tenancy: Supply tenant/school/user via ->tenant(), ->school(), ->user() or ->context([...]) to let host services restore the execution environment during queued jobs. See [important.md](E:/dev-server/www/packages/document-builder/important.md) for examples integrating with context jobs.

---

## Testing and examples

This repository includes tests and example driver docs under `src/Drivers` and `tests/` which show more usage patterns (image API, integration flows). Explore:

- [src/Drivers/Image/readme.md](E:/dev-server/www/packages/document-builder/src/Drivers/Image/readme.md)
- [tests/Integration](E:/dev-server/www/packages/document-builder/tests/Integration)

---

## Contributing

Contributions welcome. Open an issue for design discussions. When contributing:

- Keep the core package independent and rely on contracts.
- Add tests for new drivers and pipeline changes.
- Update README and docs for public API changes.

---

## License

Specify license (e.g., MIT) when publishing. If private, document internal usage guidelines.

---

If you want specific API examples converted into copy-paste-ready code that integrates with your application (service provider registration, config keys, full controller examples), tell me which integration point to document and I'll add it.
