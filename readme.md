# Unnovate Brains Document Builder

## Architecture & Development Plan

Version: 0.1 Design Specification

---

# Overview

**Document Builder** is a Laravel-friendly document processing framework designed to generate, process, and deliver different document formats through a unified and extensible API.

The goal is not to create a simple PDF generator. The goal is to create a **document execution framework** where developers define what document they want, and the framework manages the execution pipeline.

Supported document types:

* PDF
* Word
* Excel
* CSV

Future possibilities:

* Images
* Presentations
* Digital signatures
* Document encryption
* Watermarks
* Templates
* Document workflows

---

# Core Philosophy

A document is not a file.

A document is an execution definition.

Example:

```php
Document::pdf()
    ->fromCollection($students)
    ->view('reports.students')
    ->engine('mpdf')
    ->chunk(100)
    ->merge()
    ->queue()
    ->dispatch();
```

The developer describes:

* What data to use
* How to present it
* Which format to generate
* Which engine to use
* How to process it
* Where to deliver it

The framework handles execution.

---

# Package Identity

Package:

```
unnovatebrains/document-builder
```

Namespace:

```
UnnovateBrains\DocumentBuilder
```

Repository:

```
UnnovateBrains/document-builder
```

---

# Design Goals

The package must support:

## Document Formats

* PDF
* Word
* Excel
* CSV

## Rendering Engines

Example:

PDF:

* mPDF
* Chrome PDF
* TCPDF

Word:

* PHPWord

Excel:

* PhpSpreadsheet

CSV:

* Native CSV writer

## Processing

* Synchronous execution
* Queue execution
* Chunk processing
* Document merging
* Storage handling
* Status tracking

## Integrations

The package should support:

* Laravel applications
* SchoolPalm SaaS
* Multi-tenant applications
* Normal single-tenant applications

---

# High-Level Architecture

```
Document Builder

        |
        |

Execution Plan

        |
        |

Pipeline

        |
        |

Document Type Driver

        |
        |

Rendering Engine

        |
        |

Storage

        |
        |

Document Result
```

---

# Core Components

## 1. Document Builder

Responsible for creating the document definition.

Example:

```php
Document::pdf()
```

The builder collects configuration:

* document type
* engine
* data source
* view
* processing options
* storage
* queue mode

The builder does not generate immediately.

---

# 2. Execution Plan

The builder creates an immutable execution plan.

Example:

```
Document Plan

type:
    pdf

engine:
    mpdf

source:
    students collection

view:
    reports.students

chunk:
    100

merge:
    true

queue:
    true
```

The pipeline executes this plan.

---

# 3. Pipeline

The pipeline manages document execution.

Flow:

```
Request

 ↓

Create Execution Plan

 ↓

Resolve Context

 ↓

Load Data Source

 ↓

Transform Data

 ↓

Render Template

 ↓

Execute Document Engine

 ↓

Store Result

 ↓

Return Document Result
```

---

# Context System

Context is optional.

The framework should work without tenancy.

However, applications like SchoolPalm need:

* Tenant
* School
* User

The package must use contracts, not direct dependencies.

The package must NOT know:

* Stancl Tenancy
* SchoolPalm models
* Application database structure

Example:

```
Document Builder

        |

Context Contract

        |

Application Adapter

        |

SchoolPalm ContextHost
```

---

# Storage System

Documents require storage.

Storage responsibilities:

* temporary files
* generated documents
* chunks
* merged files

The package must not know:

* Local storage
* Amazon S3
* Azure
* MinIO

It uses a storage abstraction.

Example:

```
Document Builder

        |

Storage Contract

        |

Storage Implementation
```

SchoolPalm can provide:

```
StorageHostService
```

which manages:

```
tenants/{tenant}/schools/{school}/documents
```

---

# Queue System

Queue execution is optional.

Example:

```php
Document::pdf()
    ->queue()
    ->dispatch();
```

The package only defines:

```
Queue Contract
```

The implementation can be:

* Laravel Queue
* Redis
* SQS
* Database queue

---

# Document Types

The first version is one package.

```
unnovatebrains/document-builder
```

contains:

```
Core

PDF

Word

Excel

CSV
```

Example:

```
src/

Drivers/

    Pdf/

        MpdfDriver

        ChromeDriver

        TcpdfDriver


    Word/

        PhpWordDriver


    Excel/

        SpreadsheetDriver


    Csv/

        CsvDriver
```

---

# Future Package Splitting

The package should be designed so splitting is possible later.

Initial:

```
unnovatebrains/document-builder

    Core
    PDF
    Word
    Excel
    CSV
```

Future:

```
unnovatebrains/document-builder

    Core


unnovatebrains/document-builder-pdf


unnovatebrains/document-builder-word


unnovatebrains/document-builder-excel
```

However:

## Do not split before necessary.

Reasons:

* Easier maintenance
* One documentation source
* One release cycle
* Easier adoption
* Easier contribution

The architecture must allow future separation without changing the public API.

---

# Driver System

The framework must use drivers.

The core must not contain hard-coded logic:

Bad:

```
if type == pdf
    use mPDF
```

Good:

```
Document Type

        |

Driver Registry

        |

Registered Driver
```

Example:

```
PDF Driver

supports:

    mPDF

    Chrome

    TCPDF
```

---

# Public API Vision

PDF:

```php
Document::pdf()
    ->fromCollection($students)
    ->view('reports.students')
    ->engine('mpdf')
    ->save();
```

Word:

```php
Document::word()
    ->fromModel($invoice)
    ->view('invoice')
    ->save();
```

Excel:

```php
Document::excel()
    ->fromQuery(Student::query())
    ->save();
```

CSV:

```php
Document::csv()
    ->fromCollection($students)
    ->save();
```

---

# Development Roadmap

## Phase 1 - Core Framework

Build:

* package structure
* service provider
* facade
* document manager
* builder
* execution plan
* contracts

## Phase 2 - Pipeline

Build:

* source handling
* rendering pipeline
* result handling

## Phase 3 - Storage

Build:

* local storage adapter
* storage contracts

## Phase 4 - Queue

Build:

* queue contracts
* document jobs
* async execution

## Phase 5 - Document Drivers

Add:

* PDF
* Word
* Excel
* CSV

## Phase 6 - Advanced Features

Add:

* progress tracking
* events
* webhooks
* audit logs
* encryption
* permissions
* templates

---

# Important Rules

1. Core package must remain framework-independent.
2. No direct SchoolPalm dependency.
3. No direct tenancy dependency.
4. No hard-coded storage.
5. No hard-coded queue system.
6. Document types use drivers.
7. Public API should remain stable.
8. Split packages only when real problems appear.

---

# Final Vision

Unnovate Brains Document Builder should become a reusable document processing framework that powers SchoolPalm while remaining useful for other Laravel applications.

The goal is not only document generation.

The goal is a complete document execution platform.
