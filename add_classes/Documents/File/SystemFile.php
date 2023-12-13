<?php

namespace App\Documents\File;

final class SystemFile extends File
{
    /**
     * {@inheritdoc}
     */
    protected $type = FileTypesInterface::SYSTEM_FILE;
}
