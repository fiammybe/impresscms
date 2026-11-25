<?php

declare(strict_types=1);

namespace App\Ipf\Definition;

/**
 * The IPF data types (XOBJ_DTYPE_* of Icms\Core\Entity and Icms\Ipf\Entity),
 * by their constant values, so a definition does not need the legacy
 * constants to be defined.
 */
enum DataType: int
{
    case TXTBOX = 1;
    case TXTAREA = 2;
    case INT = 3;
    case URL = 4;
    case EMAIL = 5;
    case ARRAY = 6;
    case OTHER = 7;
    case SOURCE = 8;
    case STIME = 9;
    case MTIME = 10;
    case LTIME = 11;
    case SIMPLE_ARRAY = 101;
    case CURRENCY = 200;
    case FLOAT = 201;
    case TIME_ONLY = 202;
    case URLLINK = 203;
    case FILE = 204;
    case IMAGE = 205;
    case FORM_SECTION = 210;
    case FORM_SECTION_CLOSE = 211;

    public function constantName(): string
    {
        return 'XOBJ_DTYPE_' . $this->name;
    }

    /**
     * The column type family Icms\Db\Legacy\Updater\Handler gives a
     * persistent variable of this type (its $_dbTypesArray): varchar, text,
     * int or float. Form sections have no column.
     */
    public function columnFamily(): ?string
    {
        return match ($this) {
            self::TXTBOX, self::URL, self::EMAIL, self::IMAGE => 'varchar',
            self::TXTAREA, self::ARRAY, self::OTHER, self::SOURCE, self::SIMPLE_ARRAY, self::CURRENCY => 'text',
            self::INT, self::STIME, self::MTIME, self::LTIME, self::TIME_ONLY, self::URLLINK, self::FILE => 'int',
            self::FLOAT => 'float',
            self::FORM_SECTION, self::FORM_SECTION_CLOSE => null,
        };
    }

    /**
     * The control Icms\Ipf\Form\Base::createElements() gives a variable of
     * this type that has no control of its own; null when the legacy form
     * shows no element for it.
     */
    public function defaultControl(): ?string
    {
        return match ($this) {
            self::TXTBOX, self::INT, self::FLOAT, self::CURRENCY => 'text',
            self::LTIME => 'datetime',
            self::STIME => 'date',
            self::TIME_ONLY => 'time',
            self::URLLINK => 'urllink',
            self::FILE => 'richfile',
            self::TXTAREA => 'textarea',
            self::SOURCE => 'source',
            self::FORM_SECTION, self::FORM_SECTION_CLOSE => 'section',
            self::URL, self::EMAIL, self::ARRAY, self::OTHER, self::MTIME, self::SIMPLE_ARRAY, self::IMAGE => null,
        };
    }
}
