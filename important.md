# SchoolPalm Infrastructure Packages

Reference documentation for reusable infrastructure packages used across SchoolPalm and other Laravel applications.

## 1. schoolpalm/cache-store

A context-aware cache abstraction layer that hides cache drivers from developers.

Developers should not directly interact with Redis, Files, Database cache, or other providers.

Application | | CacheStore API | | +----------------+ | Redis Driver | | File Driver | | Database | | Array | +----------------+

### Goals

- Unified cache API
- Multi-tenant isolation
- Automatic context prefixes
- Driver independence
- Central cache configuration

### Basic API

use SchoolPalm\\CacheStore\\Facades\\CacheStore; CacheStore::put( 'students.count', 500, 3600 ); $count = CacheStore::get( 'students.count' ); CacheStore::forget( 'students.count' );

### Remember API

CacheStore::remember( 'students.count', 3600, function(){ return Student::count(); } );

### Context Awareness

Cache keys are automatically isolated by tenant and school context.

Developer writes: students.count Internal key: tenant:emma.school:emma-high.students.count

### Drivers

| Driver   | Usage                             |
| -------- | --------------------------------- |
| Redis    | Production high performance cache |
| File     | Small deployments                 |
| Database | Shared hosting environments       |
| Array    | Testing                           |

### Suggested Package Structure

schoolpalm/cache-store src/ Contracts/ CacheStoreInterface.php Drivers/ RedisDriver.php FileDriver.php DatabaseDriver.php Context/ CacheContextResolver.php Facades/ CacheStore.php

## 2. schoolpalm/message-delivery

A unified communication delivery system.

Developers send messages without knowing the provider, credentials, or delivery mechanism.

Application | Message API | Message Manager | +----------------+ | Email | | SMS | | WhatsApp | | Push | | In-App | +----------------+ | Providers +----------------+ | SMTP | | SES | | Mailgun | | Twilio | | AfricaTalking | | Firebase | +----------------+

### Design Principle

The application decides WHAT to send. The client decides HOW it is delivered.

### Email API

use SchoolPalm\\Message\\Facades\\Message; Message::email() -&gt;to( 'parent@example.com' ) -&gt;template( 'fee-reminder' ) -&gt;send();

### SMS API

Message::sms() -&gt;to( '+256700000000' ) -&gt;text( 'Your fees are due' ) -&gt;send();

### WhatsApp API

Message::whatsapp() -&gt;to( '+256700000000' ) -&gt;template( 'payment-receipt' ) -&gt;send();

### Push Notification API

Message::push() -&gt;user( $user ) -&gt;title( 'New Report' ) -&gt;body( 'Results are available' ) -&gt;send();

### Client Provider Configuration

Each tenant/client configures their own communication providers.

Emma High School SMS Provider: AfricaTalking API Key: \*\*\*\*\*\*\** Another School SMS Provider: Twilio Credentials: \*\*\*\*\*\*\**

### Context Resolution

Message::sms() -&gt;send(); Automatically resolves: Current Tenant | School Configuration | Provider Credentials | Delivery Driver

### Message Tracking

Database example:

messages ------------------ id tenant\_id school\_id channel recipient status provider provider\_message\_id sent\_at failed\_reason

### Status Values

| Status | Description            |
| ------ | ---------------------- |
| queued | Waiting for delivery   |
| sent   | Successfully delivered |
| failed | Delivery error         |

### Fallback Delivery

Message::notify() | Try WhatsApp | Failed | Try SMS | Failed | Try Email

### Suggested Package Structure

schoolpalm/message-delivery src/ Contracts/ MessageChannel.php MessageProvider.php Channels/ EmailChannel.php SmsChannel.php WhatsappChannel.php Drivers/ SesDriver.php SmtpDriver.php TwilioDriver.php AfricaTalkingDriver.php Models/ MessageLog.php Facades/ Message.php

## Future SchoolPalm Infrastructure Ecosystem

schoolpalm/ app-logger document-builder module-sdk module-bridge cache-store message-delivery notification audit-trail media-library search


# SchoolPalm Queue Context

**Package:** schoolpalm/queue-context

A context-aware Laravel queue extension that automatically captures, transports, and restores application context when jobs execute asynchronously.

## Overview

Laravel Queue already provides job execution, drivers, retries and workers. SchoolPalm Queue Context solves the missing context problem in multi-tenant applications.

Application | | Dispatch Job | | Capture Context | | Queue Storage | | Worker | | Restore Context | | Execute Job

## Problem Without Queue Context

Web Request Tenant: Emma High School User: Administrator | | Dispatch Job | | Queue Worker Tenant: UNKNOWN Database: UNKNOWN Storage: UNKNOWN

Background workers run outside the original HTTP request. Without context restoration, jobs may execute against the wrong tenant, database or configuration.

## Solution

Queue Context stores the application context together with the queued job and restores it before execution.

Captured Context: { tenant\_id: "emma", school\_id: "emma-high", user\_id: 25, locale: "en", timezone: "Africa/Kampala", database: "tenant\_emma", storage: "tenant\_emma" }

## Basic API

### Dispatch Context-Aware Job

use SchoolPalm\\QueueContext\\Facades\\QueueContext; QueueContext::dispatch( new GenerateReport( $student ) );

### Using Laravel Style Dispatch

GenerateReport::dispatch( $student ); The package automatically attaches context.

## Context Lifecycle

1\. User sends request 2. Tenant context detected 3. Job dispatched 4. Context snapshot created 5. Job stored in queue 6. Worker receives job 7. Context restored 8. Job executes 9. Context cleared

## Supported Context

| Context  | Purpose                            |
| -------- | ---------------------------------- |
| Tenant   | Identify SaaS tenant               |
| School   | Identify school branch             |
| User     | Track executing user               |
| Database | Restore tenant database connection |
| Storage  | Restore tenant storage location    |
| Locale   | Restore language settings          |

## Context Job Base Class

Packages can provide a base job class that automatically includes context handling.

use SchoolPalm\\QueueContext\\Jobs\\ContextJob; class GenerateReport extends ContextJob { public function handle() { // Tenant context already restored Document::pdf() -&gt;generate(); } }

## Middleware Support

class GenerateReport implements ShouldQueue { public function middleware() { return \[ new RestoreContext() ]; } }

## Multi-Tenant Safety

A job created by Emma High School must never execute inside another school's environment.

Created: Tenant: Emma High School: Main Campus Worker: Restore: Tenant Emma High School Main Campus Execute Job

## Integration With SchoolPalm Packages

### Document Builder

GenerateCertificateJob::dispatch( $student ); When executed: Document Builder knows: - School logo - Templates - Storage location - Database

### Message Delivery

SendFeeReminder::dispatch( $parent ); Automatically resolves: - SMS provider - Email settings - Sender ID - Client API credentials

### App Logger

Job Started Tenant: Emma High Job: GenerateReport Job Completed

## Package Structure

schoolpalm/queue-context src/ Contracts/ ContextResolver.php ContextStore.php Context/ ContextManager.php ContextSnapshot.php Middleware/ RestoreContext.php Jobs/ ContextJob.php Providers/ TenantContextProvider.php SchoolContextProvider.php UserContextProvider.php StorageContextProvider.php Facades/ QueueContext.php

## Future Extensions

- Scheduled task context restoration
- Event listener context restoration
- Notification context
- Batch processing context
- Audit context tracking

## SchoolPalm Infrastructure Ecosystem

schoolpalm/ app-logger document-builder cache-store message-delivery queue-context module-sdk module-bridge

Queue Context acts as the bridge that allows all asynchronous operations to run safely inside the correct tenant environment.