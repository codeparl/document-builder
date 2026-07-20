<?php

use UnnovateBrains\DocumentBuilder\Tests\TestCase;

// Bind our custom package test case base globally for all Pest files in the Integration folder
uses(TestCase::class)->in('Integration');
//<?php

// DO NOT add namespace declarations here!

//uses(UnnovateBrains\DocumentBuilder\Tests\TestCase::class)->in('Integration');