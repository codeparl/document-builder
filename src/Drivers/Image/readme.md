# Image Document Driver

> A fluent, expressive API for generating, manipulating, and outputting image-based documents within the Document Builder ecosystem. This driver is designed to support use cases such as certificates, posters, banners, ID cards, thumbnails, avatars, and composite images.

---

## Table of Contents

- [Overview](#overview)
- [School Management System Use Case](#school-management-system-use-case)
- [1. Canvas & Image Creation APIs](#1-canvas--image-creation-apis)
- [2. Input Source APIs](#2-input-source-apis)
- [3. Resize APIs](#3-resize-apis)
- [4. Crop APIs](#4-crop-apis)
- [5. Rotation & Orientation APIs](#5-rotation--orientation-apis)
- [6. Compression & Optimization APIs](#6-compression--optimization-apis)
- [7. Image Format Conversion APIs](#7-image-format-conversion-apis)
- [8. Watermark APIs](#8-watermark-apis)
- [9. Text Rendering APIs](#9-text-rendering-apis)
- [10. Filters & Effects APIs](#10-filters--effects-apis)
- [11. Shapes & Drawing APIs](#11-shapes--drawing-apis)
- [12. Border APIs](#12-border-apis)
- [13. Metadata APIs](#13-metadata-apis)
- [14. Advanced Image Generation APIs](#14-advanced-image-generation-apis)
- [15. Raw Configuration Escape Hatch](#15-raw-configuration-escape-hatch)
- [Recommended Supported Output Types](#recommended-supported-output-types)
- [Engine Capability Map](#engine-capability-map)

---

## Overview

The **Image Document Driver** provides a unified, chainable interface for creating and manipulating images. Whether you need to generate a dynamic certificate, resize profile photos in bulk, apply watermarks to brand assets, or build composite posters — this driver abstracts the underlying engine (GD, Imagick, etc.) behind a clean, declarative API.

All API calls begin with the `Document::image()` entry point and can be chained fluently.

```php
Document::image()
    ->canvas(1200, 800)
    ->background('#0047AB')
    ->save();
```

---

## School Management System Use Case

In a school management platform like **SchoolPalm**, the Image Document Driver serves as the central engine for generating all image-based assets across the system. Below is a summary of how different modules leverage image generation:

| SchoolPalm Module                | Image Document Use Case                                                   |
| -------------------------------- | ------------------------------------------------------------------------- |
| **Student Profiles**             | Generate ID cards, crop & resize profile photos, create avatar thumbnails |
| **Certificates & Awards**        | Render graduation certificates, merit awards, participation certificates  |
| **Attendance & Reports**         | Generate student attendance charts, progress report cover images          |
| **Timetables & Notices**         | Create schedule posters, event banners, announcement flyers               |
| **Library System**               | Generate book cover thumbnails, barcode labels, borrower ID cards         |
| **Examinations**                 | Create hall tickets, admit cards, rank cards, mark sheet cover pages      |
| **Transport Management**         | Generate route map thumbnails, bus pass ID cards                          |
| **Fee Management**               | Create fee receipt headers, payment confirmation badges                   |
| **Sports & Events**              | Generate tournament posters, team photo collages, winner certificates     |
| **Alumni Management**            | Create alumni ID cards, reunion event banners, yearbook composites        |
| **Communication (Notice Board)** | Generate newsletter headers, circular thumbnails, event flyers            |
| **Hostel Management**            | Generate room allotment cards, visitor pass images, hostel ID cards       |

Each API category below includes a **School Use Case** column that maps directly to SchoolPalm scenarios.

---

## 1. Canvas & Image Creation APIs

Used when creating blank images, thumbnails, posters, certificates, banners, etc.

| Builder API             | Option Key                      | Type   | Purpose                       | SchoolPalm Use Case                                   |
| ----------------------- | ------------------------------- | ------ | ----------------------------- | ----------------------------------------------------- |
| `width(1200)`           | `width`                         | int    | Set output width              | Define certificate dimensions (e.g., 1200×800 for A4) |
| `height(800)`           | `height`                        | int    | Set output height             | Set poster height for event announcements             |
| `size(1200, 800)`       | `width`, `height`               | int    | Define image dimensions       | Standardize ID card size across all students          |
| `canvas(1200, 800)`     | `canvas_width`, `canvas_height` | int    | Create a drawing canvas       | Create blank certificate template for graduation      |
| `background('#ffffff')` | `background`                    | string | Set canvas background color   | Set school-branded background color on report covers  |
| `transparent()`         | `transparent`                   | bool   | Enable transparent background | Generate transparent logo overlays for watermarks     |

### Example — SchoolPalm: Create a school-branded certificate canvas

```php
Document::image()
    ->canvas(1200, 800)
    ->background('#0047AB') // School primary color
    ->save();
```

---

## 2. Input Source APIs

Used for loading existing images from various sources. *(Engine implementation dependent.)*

| API                 | Option       | Description                                | SchoolPalm Use Case                                      |
| ------------------- | ------------ | ------------------------------------------ | -------------------------------------------------------- |
| `fromImage($path)`  | `source`     | Load an image from a local file path       | Load student uploaded profile photo for ID card          |
| `fromUrl($url)`     | `source_url` | Load an image from a remote URL            | Fetch student image from cloud storage or external SIS   |
| `fromBase64($data)` | `base64`     | Load an image from a base64-encoded string | Accept camera-captured photos from mobile app uploads    |
| `fromSvg($svg)`     | `svg`        | Load an SVG string or file                 | Load school logo SVG asset for placement on certificates |

### Example — SchoolPalm: Load student photo from storage

```php
Document::image()
    ->fromImage(storage_path("students/{$student->id}/photo.jpg"))
    ->save();
```

---

## 3. Resize APIs

Used for thumbnails, profile images, school cards, and responsive image variants.

| API                 | Option                            | Description                                          | SchoolPalm Use Case                                              |
| ------------------- | --------------------------------- | ---------------------------------------------------- | ---------------------------------------------------------------- |
| `resize(500, 500)`  | `resize_width`, `resize_height`   | Resize to exact dimensions                           | Standardize all student profile photos to 500×500                |
| `fit(500, 500)`     | `fit_width`, `fit_height`         | Resize to fit within bounds (maintains aspect ratio) | Fit library book cover thumbnails into a uniform 300×300 grid    |
| `contain(500, 500)` | `contain_width`, `contain_height` | Resize to contain within bounds                      | Ensure student photos fit inside ID card photo slot (400×500)    |
| `scale(0.5)`        | `scale`                           | Scale by a multiplier (e.g., 0.5 = 50%)              | Generate thumbnail previews of event posters at 50% size         |
| `keepAspectRatio()` | `keep_aspect_ratio`               | Preserve aspect ratio during resize                  | Prevent distortion when resizing diverse student-uploaded photos |

### Example — SchoolPalm: Resize student profile photos to uniform dimensions

```php
Document::image()
    ->fromImage(storage_path("students/{$student->id}/photo.jpg"))
    ->fit(400, 400)
    ->keepAspectRatio()
    ->save();
```

---

## 4. Crop APIs

Used for avatars, ID photos, certificates, and extracting regions of interest.

| API                    | Option                      | Description                      | SchoolPalm Use Case                                       |
| ---------------------- | --------------------------- | -------------------------------- | --------------------------------------------------------- |
| `crop(300, 300)`       | `crop_width`, `crop_height` | Crop to the specified dimensions | Crop student face region from full-body photo for ID card |
| `cropPosition(50, 50)` | `crop_x`, `crop_y`          | Set the top-left origin for crop | Center the crop origin on the detected face area          |

### Example — SchoolPalm: Crop student ID photo to standard passport size

```php
Document::image()
    ->fromImage(storage_path("students/{$student->id}/photo.jpg"))
    ->crop(300, 300)
    ->cropPosition(20, 20)
    ->save();
```

---

## 5. Rotation & Orientation APIs

Used for correcting orientation or applying creative transformations.

| API                | Option            | Description                         | SchoolPalm Use Case                                          |
| ------------------ | ----------------- | ----------------------------------- | ------------------------------------------------------------ |
| `rotate(90)`       | `rotate`          | Rotate image by degrees (clockwise) | Correct smartphone-captured student photos that are sideways |
| `flipHorizontal()` | `flip_horizontal` | Mirror the image horizontally       | Create mirror-effect for sports team photo compositions      |
| `flipVertical()`   | `flip_vertical`   | Mirror the image vertically         | Apply creative poster layouts for event banners              |

### Example — SchoolPalm: Auto-correct orientation of uploaded student photos

```php
Document::image()
    ->fromImage(storage_path("students/{$student->id}/mobile_upload.jpg"))
    ->rotate(90) // Correct portrait orientation
    ->save();
```

---

## 6. Compression & Optimization APIs

Used to reduce file size for web delivery or storage efficiency.

| API               | Option           | Description                            | SchoolPalm Use Case                                            |
| ----------------- | ---------------- | -------------------------------------- | -------------------------------------------------------------- |
| `quality(80)`     | `quality`        | Set output quality (1–100)             | Reduce storage costs for thousands of student profile photos   |
| `optimize()`      | `optimize`       | Apply lossless optimization            | Optimize ID card exports before bulk printing                  |
| `progressive()`   | `progressive`    | Enable progressive/interlaced encoding | Speed up loading of student gallery photos on slow connections |
| `stripMetadata()` | `strip_metadata` | Remove EXIF and other metadata         | Strip GPS/exif data from student photos for privacy compliance |

### Example — SchoolPalm: Optimize student photo for web display with privacy protection

```php
Document::image()
    ->fromImage(storage_path("students/{$student->id}/photo.jpg"))
    ->quality(75)
    ->stripMetadata() // Remove EXIF for child privacy
    ->optimize()
    ->save();
```

---

## 7. Image Format Conversion APIs

Convert between supported image formats.

### Supported Formats

| Format | Extension        | Status             | SchoolPalm Use Case                                                |
| ------ | ---------------- | ------------------ | ------------------------------------------------------------------ |
| JPEG   | `.jpg` / `.jpeg` | ✅ Supported        | Export certificates for email attachment (universal compatibility) |
| PNG    | `.png`           | ✅ Supported        | Save school logo with transparent background                       |
| WebP   | `.webp`          | ✅ Supported        | Serve student gallery thumbnails on web portal (smaller size)      |
| GIF    | `.gif`           | ⏳ Planned          | Create animated event teasers for notice board                     |
| BMP    | `.bmp`           | ⏳ Planned          | Legacy compatibility with older school print systems               |
| TIFF   | `.tiff`          | ⏳ Planned          | High-resolution archiving of certificates                          |
| AVIF   | `.avif`          | 🔮 Future           | Next-gen compression for future school portal                      |
| SVG    | `.svg`           | ⚠️ Driver-dependent | Scalable school crests and badge assets                            |

### Example — SchoolPalm: Convert student ID photo to WebP for web portal

```php
Document::image()
    ->fromImage(storage_path("students/{$student->id}/photo.png"))
    ->convert('webp')
    ->save();
```

---

## 8. Watermark APIs

Used for branding, school documents, certificates, and copyright protection.

| API                           | Option               | Description                 | SchoolPalm Use Case                                                |
| ----------------------------- | -------------------- | --------------------------- | ------------------------------------------------------------------ |
| `watermark($path)`            | `watermark`          | Path to the watermark image | Overlay school crest/logo on all certificates                      |
| `watermarkOpacity(50)`        | `watermark_opacity`  | Opacity percentage (0–100)  | Set subtle watermark (30%) so certificate content remains readable |
| `watermarkPosition('center')` | `watermark_position` | Position of the watermark   | Place school emblem in center of graduation certificates           |

### Supported Positions

| Position       | Description         | SchoolPalm Use Case                               |
| -------------- | ------------------- | ------------------------------------------------- |
| `top-left`     | Top-left corner     | Branding on student ID cards (school logo corner) |
| `top-right`    | Top-right corner    | Exam hall tickets with school header mark         |
| `center`       | Center of the image | Transparent watermark on certificates             |
| `bottom-left`  | Bottom-left corner  | Copyright notice on published event photos        |
| `bottom-right` | Bottom-right corner | School accreditation badge on report covers       |

### Example — SchoolPalm: Apply school watermark to graduation certificate

```php
Document::image()
    ->canvas(1200, 800)
    ->background('#ffffff')
    ->watermark(storage_path('school-crest.png'))
    ->watermarkOpacity(40)
    ->watermarkPosition('center')
    ->save();
```

---

## 9. Text Rendering APIs

For creating posters, certificates, banners, and branded graphics.

| API                        | Option             | Description                      | SchoolPalm Use Case                                      |
| -------------------------- | ------------------ | -------------------------------- | -------------------------------------------------------- |
| `text('Emma High School')` | `text`             | The text string to render        | Render student name on certificate                       |
| `font('arial.ttf')`        | `font`             | Path to the TrueType font file   | Use school-branded custom font for all documents         |
| `fontSize(40)`             | `font_size`        | Font size in pixels              | Large title on event posters, smaller text on ID cards   |
| `fontColor('#ffffff')`     | `font_color`       | Text color (hex, RGB, or name)   | White text on dark school-themed certificates            |
| `textPosition(100, 200)`   | `text_x`, `text_y` | X and Y coordinates for the text | Position student name exactly in certificate placeholder |

### Example — SchoolPalm: Generate a student award certificate with personalized text

```php
Document::image()
    ->canvas(800, 400)
    ->background('#1a1a2e')
    ->text('Coding Competition 2026')
    ->fontSize(50)
    ->fontColor('#ffffff')
    ->textPosition(100, 100)
    ->text("Winner: {$student->full_name}")
    ->fontSize(30)
    ->fontColor('#FFD700') // Gold color
    ->textPosition(100, 200)
    ->save();
```

---

## 10. Filters & Effects APIs

Apply visual filters and adjustments to images.

| API              | Option       | Description                     | SchoolPalm Use Case                                          |
| ---------------- | ------------ | ------------------------------- | ------------------------------------------------------------ |
| `grayscale()`    | `grayscale`  | Convert image to grayscale      | Create mourning/posthumous tribute photos for alumni section |
| `sepia()`        | `sepia`      | Apply a sepia tone effect       | Vintage yearbook photo styling for alumni gallery            |
| `blur(5)`        | `blur`       | Apply Gaussian blur (radius)    | Blur background behind student name on ID cards for contrast |
| `sharpen(10)`    | `sharpen`    | Sharpen the image               | Enhance blurry student-uploaded profile photos               |
| `brightness(20)` | `brightness` | Adjust brightness (−100 to 100) | Brighten underexposed classroom event photos                 |
| `contrast(10)`   | `contrast`   | Adjust contrast (−100 to 100)   | Improve readability of scanned documents for report covers   |

### Example — SchoolPalm: Create a vintage yearbook style for alumni photos

```php
Document::image()
    ->fromImage(storage_path("alumni/{$alumnus->id}/photo.jpg"))
    ->sepia()
    ->contrast(15)
    ->save();
```

---

## 11. Shapes & Drawing APIs

> ⚠️ **Future Extension** — Planned for a later release.

Draw primitive shapes onto the canvas.

| API           | Description      | SchoolPalm Use Case                                    |
| ------------- | ---------------- | ------------------------------------------------------ |
| `rectangle()` | Draw a rectangle | Draw border frames on ID cards or certificate layouts  |
| `circle()`    | Draw a circle    | Create circular photo cutouts for student avatars      |
| `line()`      | Draw a line      | Add signature lines on certificates or admission forms |
| `polygon()`   | Draw a polygon   | Create star-shaped merit badges for achievement awards |
| `ellipse()`   | Draw an ellipse  | Design oval photo frames for yearbook composites       |

### Configuration Options — SchoolPalm: Draw a certificate award badge

```php
Document::image()->shape([
    'type'   => 'rectangle',
    'x'      => 20,
    'y'      => 20,
    'width'  => 200,
    'height' => 100,
    'color'  => '#ff0000',
    'filled' => true,
]);
```

---

## 12. Border APIs

Used for cards, profile images, and framed certificates.

| API                  | Option                         | Description                              | SchoolPalm Use Case                                     |
| -------------------- | ------------------------------ | ---------------------------------------- | ------------------------------------------------------- |
| `border(5, '#000')`  | `border_width`, `border_color` | Set border thickness and color           | Add school-color border around student ID cards         |
| `roundedCorners(30)` | `corner_radius`                | Apply rounded corners (radius in pixels) | Modern rounded-corner profile photos for student portal |

### Example — SchoolPalm: Create a modern student ID card with rounded corners and school border

```php
Document::image()
    ->canvas(400, 400)
    ->background('#ffffff')
    ->roundedCorners(50)
    ->border(2, '#0047AB') // School primary blue
    ->save();
```

---

## 13. Metadata APIs

Manage image metadata and EXIF information.

| API                | Option        | Description               | SchoolPalm Use Case                                           |
| ------------------ | ------------- | ------------------------- | ------------------------------------------------------------- |
| `author('name')`   | `author`      | Set the author field      | Tag certificates with principal's name as author              |
| `copyright('...')` | `copyright`   | Set the copyright notice  | Add "© SchoolPalm / Emma High School" to all published images |
| `description('')`  | `description` | Set the image description | Store student admission number in image metadata for tracking |

### Example — SchoolPalm: Set metadata on generated certificate

```php
Document::image()
    ->canvas(1200, 800)
    ->background('#ffffff')
    ->text('Certificate of Achievement')
    ->fontSize(40)
    ->textPosition(100, 100)
    ->author('Emma High School')
    ->copyright('© 2026 Emma High School. All rights reserved.')
    ->description('Annual Sports Day Certificate - Student ID: STU-2026-001')
    ->save();
```

---

## 14. Advanced Image Generation APIs

> 🚀 **Recommended Future APIs** — Planned enhancements for richer image generation.

### QR Codes

Embed QR codes into images.

```php
Document::image()
    ->qrCode('https://schoolpalm.com/student/verify/123')
    ->qrSize(200)
    ->qrColor('#000000')
    ->save();
```

**SchoolPalm Use Case:** Generate QR codes on certificates for instant online verification by employers or universities.

**Options:** `qr_data`, `qr_size`, `qr_color`

---

### Barcodes

Generate and embed barcodes.

```php
Document::image()
    ->barcode('LIB-2026-00142', 'CODE128')
    ->save();
```

**SchoolPalm Use Case:** Print barcodes on library book labels, student ID cards for checkout scanning, and exam hall tickets.

**Options:** `barcode_value`, `barcode_type`

---

### Templates

Use pre-defined templates for posters, certificates, and more.

```php
Document::image()
    ->template('certificate')
    ->variables([
        'name'       => 'John Doe',
        'course'     => 'Computer Science',
        'graduation' => '2026',
        'school'     => 'Emma High School',
    ])
    ->save();
```

**SchoolPalm Use Case:** Use school-branded certificate templates with placeholder variables for bulk generation of graduation, merit, and participation certificates.

**Options:** `template`, `variables`

---

### Collage / Composite Images

Place multiple images onto a single canvas.

```php
Document::image()
    ->canvas(1200, 800)
    ->background('#f0f0f0')
    ->place(storage_path('sports-day/photo1.jpg'), 0, 0)
    ->place(storage_path('sports-day/photo2.jpg'), 600, 0)
    ->place(storage_path('sports-day/photo3.jpg'), 0, 400)
    ->place(storage_path('sports-day/photo4.jpg'), 600, 400)
    ->save();
```

**SchoolPalm Use Case:** Create sports day collage posters, class photo composites, or annual day event recap banners combining multiple images.

**Options:** `layers` (array of image paths with coordinates)

---

### Animated Images (GIF / WebP)

Create simple animations from multiple frames.

```php
Document::image()
    ->frames([
        storage_path('science-fair/frame1.png'),
        storage_path('science-fair/frame2.png'),
        storage_path('science-fair/frame3.png'),
    ])
    ->duration(100)  // milliseconds per frame
    ->loop(true)     // infinite loop
    ->save();
```

**SchoolPalm Use Case:** Create animated GIFs for the school notice board showing rotating announcements, event teasers, or "Student of the Week" spotlights.

**Options:** `frames`, `duration`, `loop`

---

### Sprite Sheets

Generate sprite sheets for games or UI assets.

```php
Document::image()
    ->spriteSheet(
        columns: 4,
        rows: 4
    )
    ->save();
```

**SchoolPalm Use Case:** Generate sprite sheets for educational game assets used in the school's e-learning module, or UI icon sprites for the student portal.

**Options:** `columns`, `rows`, `sprite_width`, `sprite_height`

---

## 15. Raw Configuration Escape Hatch

For advanced use cases, pass raw configuration directly to the underlying engine.

```php
Document::image()
    ->imageConfig([
        'driver'      => 'imagick',
        'density'     => 300,         // High-res for print
        'compression' => 'lossless',  // Preserve quality for archiving
    ])
    ->save();
```

**SchoolPalm Use Case:** Configure engine-specific settings for high-resolution certificate printing (300 DPI), or adjust compression behavior for large bulk operations.

**Options:** All driver-specific and engine-specific settings.

---

## Recommended Supported Output Types

| Format | Priority   | Implementation Phase | SchoolPalm Use Case                                            |
| ------ | ---------- | -------------------- | -------------------------------------------------------------- |
| PNG    | 🔴 Required | Initial              | School logos, transparent overlays, digital certificates       |
| JPEG   | 🔴 Required | Initial              | Student photos, event images, scanned document covers          |
| WebP   | 🔴 Required | Initial              | Web portal galleries, mobile app thumbnails (bandwidth saving) |
| GIF    | 🟡 Later    | Phase 2              | Animated event teasers on digital notice boards                |
| TIFF   | 🟡 Later    | Phase 2              | High-resolution certificate archiving for legal records        |
| BMP    | 🟡 Later    | Phase 2              | Legacy printer compatibility in school administration offices  |
| AVIF   | 🟢 Future   | Phase 3              | Next-gen compression for large-scale photo storage             |

---

## Engine Capability Map

Each image driver engine will advertise its capabilities through feature-detection methods. The table below outlines the expected capabilities for the two primary engines.

### ImagickEngine

| Feature         | Supported | SchoolPalm Relevance                        |
| --------------- | --------- | ------------------------------------------- |
| Resize          | ✅         | Bulk resize student photos                  |
| Crop            | ✅         | Crop ID card face regions                   |
| Watermark       | ✅         | School crest overlay on certificates        |
| Text Rendering  | ✅         | Student name & details on documents         |
| GIF Animation   | ✅         | Animated notice board banners               |
| WebP            | ✅         | Web-optimized student galleries             |
| Metadata (EXIF) | ✅         | Copyright & student ID tracking in metadata |
| SVG             | ⚠️ Partial | School logo vector assets                   |

### GdEngine

| Feature           | Supported | SchoolPalm Relevance                   |
| ----------------- | --------- | -------------------------------------- |
| Resize            | ✅         | Thumbnail generation for gallery       |
| Crop              | ✅         | Profile photo cropping                 |
| Watermark         | ✅         | Basic watermark on documents           |
| Text Rendering    | ✅         | Certificate text                       |
| JPEG / PNG        | ✅         | Standard output formats                |
| GIF Animation     | ❌         | Cannot produce animated banners        |
| Advanced Metadata | ❌         | Cannot embed copyright/author metadata |
| SVG               | ❌         | Cannot render vector school crest      |

### Capability Detection Methods

Engines will implement the following capability check methods, allowing SchoolPalm to dynamically choose the best engine per task:

```php
$engine->supportsResize();     // bool — e.g., for bulk photo processing
$engine->supportsWatermark();  // bool — e.g., for certificate branding
$engine->supportsText();       // bool — e.g., for rendering student names
$engine->supportsAnimation();  // bool — e.g., for animated notice board
$engine->supportsVector();     // bool — e.g., for SVG school crest
$engine->supportsMetadata();   // bool — e.g., for EXIF tracking
```

---

> 📘 **Note:** This document describes the intended API surface for the Image Document Driver within the SchoolPalm school management ecosystem. Some features are planned for future releases and may not yet be implemented. Refer to the project roadmap and changelog for the current implementation status.

