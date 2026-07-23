<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Concerns;

trait ExcelBuilderTrait
{

    /**
     * Define spreadsheet columns.
     *
     * Example:
     *
     * ->columns([
     *     'name'  => 'Student Name',
     *     'phone' => 'Phone Number',
     * ])
     */
    public function columns(
        array $columns
    ): self {

        return $this->setDriverOption(
            'xlsx',
            'columns',
            $columns
        );
    }



    /**
     * Define worksheet name.
     *
     * Example:
     *
     * ->sheet('Students')
     */
    public function sheet(
        string $name
    ): self {

        return $this->setDriverOption(
            'xlsx',
            'sheet',
            $name
        );
    }



    /**
     * Freeze worksheet rows.
     *
     * Example:
     *
     * ->freezeRows(1)
     */
    public function freezeRows(
        int $rows
    ): self {

        return $this->setDriverOption(
            'xlsx',
            'freeze_rows',
            $rows
        );
    }



    /**
     * Freeze worksheet columns.
     *
     * Example:
     *
     * ->freezeColumns(2)
     */
    public function freezeColumns(
        int $columns
    ): self {

        return $this->setDriverOption(
            'xlsx',
            'freeze_columns',
            $columns
        );
    }



    /**
     * Enable automatic column sizing.
     *
     * Example:
     *
     * ->autoSize()
     */
    public function autoSize(
        bool $enabled = true
    ): self {

        return $this->setDriverOption(
            'xlsx',
            'auto_size',
            $enabled
        );
    }



    /**
     * Enable or disable header row generation.
     *
     * Example:
     *
     * ->headers(false)
     */
    public function headers(
        bool $enabled = true
    ): self {

        return $this->setDriverOption(
            'xlsx',
            'headers',
            $enabled
        );
    }



    /**
     * Protect worksheet.
     *
     * Example:
     *
     * ->protectSheet('password123')
     */
    public function protectSheet(
        ?string $password = null
    ): self {

        return $this->setDriverOption(
            'xlsx',
            'sheet_protection',
            [
                'enabled'  => true,
                'password' => $password,
            ]
        );
    }



    /**
     * Protect workbook structure.
     *
     * Example:
     *
     * ->protectWorkbook('secret')
     */
    public function protectWorkbook(
        ?string $password = null
    ): self {

        return $this->setDriverOption(
            'xlsx',
            'workbook_protection',
            [
                'enabled'  => true,
                'password' => $password,
            ]
        );
    }



    /**
     * Enable worksheet grid lines.
     */
    public function gridLines(
        bool $enabled = true
    ): self {

        return $this->setDriverOption(
            'xlsx',
            'grid_lines',
            $enabled
        );
    }



    /**
     * Raw PhpSpreadsheet configuration.
     *
     * Example:
     *
     * ->excelConfig([
     *     'creator'=>'SchoolPalm'
     * ])
     */
    public function excelConfig(
        array $config
    ): self {

        return $this->setDriverOptions(
            'xlsx',
            $config
        );
    }
}
