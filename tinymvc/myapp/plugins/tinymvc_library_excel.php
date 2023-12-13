<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * @author Tatiana Bendiucov
 * @deprecated [01.12.2021]
 * @see TinyMVC_Library_Phpexcel
 */
class TinyMVC_Library_excel
{
    /**
     * File name for parse.
     */
    private $file;

    /**
     * The cached file content.
     *
     * @var array
     */
    private $content;

    /**
     * Set file for parse.
     *
     * @throws RuntimeException if file is not found
     */
    public function set_file(string $file): void
    {
        if (!is_file($file)) {
            throw new RuntimeException("File {$file} doesn't found!");
        }

        $this->file = $file;
    }

    /**
     * Get content from file by selected sheet.
     */
    public function extract_content(int $index = 0): array
    {
        if (empty($this->file)) {
            return array();
        }

        if (null !== $this->content) {
            return $this->content;
        }

        $reader = IOFactory::createReaderForFile($this->file);
        $reader->setReadDataOnly(true);
        $file = $reader->load($this->file);

        return $this->content = $file->setActiveSheetIndex($index)->toArray(null, false, false, true);
    }

    /**
     * Get content from file from all sheets.
     */
    public function extract_content_all(): array
    {
        $reader = IOFactory::createReaderForFile($this->file);
        $reader->setReadDataOnly(true);
        $file = $reader->load($this->file);

        // Get content
        $content = array();
        foreach ($file->getWorksheetIterator() as $worksheet) {
            $content[$worksheet->getTitle()] = $worksheet->toArray(null, false, false, true);
        }

        return $content;
    }
}
