<?php

use App\Common\Exceptions\FileException as AppFileException;
use App\Common\Exceptions\FileNotFoundException;
use App\Common\Exceptions\FileWriteException;
use App\Common\Exceptions\NotFoundException;
use App\Common\Exceptions\ParseException;
use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\ConstraintViolationList;
use App\Common\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [03.12.2021]
 * library refactoring: code style, optimize code
 */
class TinyMVC_Library_Phpexcel
{
    private const MAX_FILE_SIZE = 4000000;

    /**
     * ROOT PATH.
     */
    private $root;

    /**
     * File name for parse.
     */
    private $file;

    /**
     * Path to config file.
     */
    private $pathToLibrary;

    /**
     * Id config.
     */
    private $configId;

    /**
     * Config file name.
     */
    private $libraryFileName;

    /**
     * Array with config parameters.
     */
    private $configs;

    /**
     * Set if file have sheets.
     */
    private $parseSheet = false;

    /**
     * Table name from data base.
     */
    private $tableName;

    /**
     * Name of file xls.
     */
    private $sourceFileName;

    /**
     * Permitted formats.
     */
    private $allowedExtensions;

    /**
     * Start parse from this row.
     */
    private $rowIndex = 1;

    /**
     * Required columns in file.
     */
    private $requiredColumns = array();

    /**
     * Config with parameters with relation.
     */
    private $relationshipConfigs = array();

    /**
     * Message with error.
     */
    private $errorTexts = array(
        'required'      => 'The [column] field in row [row] is required. Exemple of insert: [sample]',
        'minSize'       => 'The [column] field in row [row] must be at least [minSize] characters in length. Exemple of insert: [sample]',
        'maxSize'       => 'The [column] field in row [row] cannot exceed [maxSize] characters in length. Exemple of insert: [sample]',
        'email'         => 'The [column] field in row [row] must contain a valid email address. Exemple of insert: [sample]',
        'valid_url'     => 'The [column] field in row [row] must contain a valid URL. Exemple of insert: [sample]',
        'float'         => 'The [column] field in row [row] must contain only numeric characters. Exemple of insert: [sample]',
        'alpha_numeric' => 'The [column] field in row [row] must contain only alpha numeric characters. Exemple of insert: [sample]',
        'null_col'      => 'Error column [column] in row [row] not exists!!!',
        'not_colum'     => 'Error column [column] not exists!!!',
    );

    /**
     * @param ContainerInterface $container The container instance
     */
	public function __construct(ContainerInterface $container)
	{
        $this->root = sprintf('%s/', rtrim(
            $container->hasParameter('library.phpexcel.base_path') ? $container->getParameter('library.phpexcel.base_path') : $container->getParameter('kernel.project_dir'),
            '/'
        ));
    }

    /**
     * Set file for parsing.
     *
     * @throws RuntimeException
     */
    public function set_file(string $file)
    {
        if (!is_file($filepath = realpath("{$this->root}{$this->pathToLibrary}{$this->configId}/{$file}"))) {
            throw new AppFileException("File {$file} is not found!");
        }

        $this->file = $filepath;
    }

    /**
     * Set configuration for start parser.
     *
     * @param null|string $pathToLibrary path to library of parser
     * @param null|int    $configId      ID of the configuration
     * @param null|string $filename      file name where need parse
     */
    public function set_config(?string $pathToLibrary = null, ?int $configId = 0, ?string $filename = null): void
    {
        if (!is_dir("{$pathToLibrary}{$configId}")) {
            throw new FileNotFoundException("Path to config file doesn't exist!");
        }

        /** @var Config_Lib_Model $libraryRepository */
        $libraryRepository = model(Config_Lib_Model::class);
        $library = $libraryRepository->get_lib_config($configId);
        if (empty($library)) {
            throw new NotFoundException("Library doesn't found, please check id!");
        }
        if ('manual' == $library['lib_type']) {
            throw new DomainException('Type of file is manual!');
        }

        $this->configId = $configId;
        $this->pathToLibrary = $pathToLibrary;
        $this->libraryFileName = $library['file_name'] ?? null;
        if (!is_file($configFile = realpath("{$this->root}{$this->pathToLibrary}{$this->configId}/config_{$this->libraryFileName}.php"))) {
            throw new FileNotFoundException("File with config doesn't found!");
        }

        // Loading configurations without polluting the context
        list(
            'config'            => $configs,
            'key_row'           => $rowIndex,
            'db_table'          => $tableName,
            'sheetParse'        => $parseSheet,
            'file_xls_name'     => $xlsFile,
            'allowed_extension' => $allowedExtensions,
            'required_column'   => $requiredColumns,
            'relation_config'   => $relationshipConfigs
        ) = (function (string $file) {
            require $file;

            return compact(
                'config',
                'key_row',
                'db_table',
                'sheetParse',
                'file_xls_name',
                'relation_config',
                'required_column',
                'allowed_extension',
            );
        })->call($this, $configFile);

        $this->configs = $configs;
        $this->rowIndex = $rowIndex;
        $this->tableName = $tableName;
        $this->parseSheet = $parseSheet ?? false;
        $this->sourceFileName = $xlsFile;
        $this->relationshipConfigs = $relationshipConfigs;
        $this->requiredColumns = $requiredColumns;
        $this->allowedExtensions = $allowedExtensions;

        if (!empty($filename)) {
            $this->set_file($filename);
        }
    }

    /**
     * Get data from excel file by index of sheet.
     */
    public function excel_by_index(int $index = 0): array
    {
        $reader = IOFactory::createReaderForFile($this->file);
        $reader->setReadDataOnly(true);

        return $this->parse_worksheet(
            $reader->load($this->file)->getSheet($index)
        );
    }

    /**
     * Get data form excel file by all sheets.
     */
    public function excel_all_sheet(): array
    {
        $reader = IOFactory::createReaderForFile($this->file);
        $file = $reader->load($this->file);

        $result['content'] = array();
        foreach ($file->getWorksheetIterator() as $worksheet) {
            $temp = $this->parse_worksheet($worksheet, $worksheet->getTitle());
            $result['content'] = array_merge($result['content'], $temp['content']);
            if (empty($temp['optional'])) {
                continue;
            }

            $table = key($temp['optional']);
            if (!empty($result['optional'][$table])) {
                $result['optional'][$table] = array_merge($result['optional'][$table], $temp['optional'][$table]);
                $result['optional'][$table] = array_unique($result['optional'][$table]);
            } else {
                $result['optional'][$table] = $temp['optional'][$table];
            }

            // if (!empty($temp['optional'])){
            //     $result['optional'].= $temp['optional'] . ",";
            // }
        }

        // if (!empty($relation['db_table']['SHEET']['from_table'])){
        //     $table_sheet = $relation['db_table']['SHEET']['from_table'];
        //     if (!empty($result['optional'][$table_sheet])){
        //         $result['optional'][$table_sheet] = array_merge($result['optional'][$table_sheet], $allSheets);
        //         $result['optional'][$table_sheet] = array_unique($result['optional'][$table_sheet]);
        //     } else {
        //         $result['optional'][$table_sheet] = $allSheets;
        //     }
        // }

        return $result;
    }

    /**
     * Get content from file by selected sheet.
     */
    public function extract_content(int $index = 0, ?string $sheetTitle = null): array
    {
        $reader = IOFactory::createReaderForFile($this->file);
        $reader->setReadDataOnly(true);

        return $this->process_content(
            $reader->load($this->file)
                ->setActiveSheetIndex($index)
                ->toArray(null, false, false, true),
            $sheetTitle
        );
    }

    /**
     * Get all content from file.
     */
    public function extract_all_content(): array
    {
        $reader = IOFactory::createReaderForFile($this->file);
        $reader->setReadDataOnly(true);
        $file = $reader->load($this->file);
        $result['content'] = array();
        foreach ($file->getWorksheetIterator() as $worksheet) {
            $content = $worksheet->toArray(null, false, false, true);
            $temp = $this->process_content($content, $worksheet->getTitle());
            $result['content'] = array_merge($result['content'], $temp['content']);
            if (empty($temp['optional'])) {
                continue;
            }

            $table = key($temp['optional']);
            if (!empty($result['optional'][$table])) {
                $result['optional'][$table] = array_merge($result['optional'][$table], $temp['optional'][$table]);
                $result['optional'][$table] = array_unique($result['optional'][$table]);
            } else {
                $result['optional'][$table] = $temp['optional'][$table];
            }
        }

        return $result;
    }

    /**
     * Parse file, select method for parse file, create array for insert in db.
     */
    public function excel_parse(int $index = 0, ?string $sheetTitle = null): array
    {
        if (!$this->parseSheet) {
            $output = $this->extract_content($index, $sheetTitle);
        } else {
            $output = $this->extract_all_content();
        }

        // Functional relation
        if (!empty($output['optional'])) {
            $realtions = $this->get_relationship_information($output['optional']) ?? array();
            if (!empty($realtions)) {
                $result = array();
                foreach ($output['content'] as $i => $row) {
                    $result[$i] = $row;

                    // foreach($this->relationshipConfigs['insert_column'] as $to_column => $form_column) {
                    //     $result[$i][$to_column] = '';

                    foreach ($this->relationshipConfigs['config_row'] as $cell => $column_name) {
                        $cellKey = $cell;
                        $relatedColumn = $column_name;

                        if ('SHEET' == $cell) {
                            $cellKey = key($column_name);
                            $relatedColumn = $column_name[$cellKey];
                        }

                        $baseColumn = $this->relationshipConfigs['insert_column'][$relatedColumn];
                        $result[$i][$relatedColumn] = '';
                        if (!empty($realtions[$this->relationshipConfigs['db_table'][$cell]['from_table']])) {
                            $relatedTable = $this->relationshipConfigs['db_table'][$cell]['from_table'];
                            if (!empty($realtions[$relatedTable][$row[$this->configs[$cellKey]['db_colum']]])) {
                                $result[$i][$relatedColumn] = $realtions[$relatedTable][$row[$this->configs[$cellKey]['db_colum']]][$baseColumn];
                            }
                        }

                        // if ($this->configs[$char_key]['insert_relation'] === $relatedColumn){
                        //     if (!empty($realtions[$row[$this->configs[$char_key]['db_colum']]])){
                        //         $result[$i][$relatedColumn]= $realtions[$row[$this->configs[$char_key]['db_colum']]][$baseColumn];
                        //     }
                        // }
                    }

                    // if(!empty($realtions[$row[$this->relationshipConfigs['db_colum']]])) {
                    //     $result[$i][$relatedColumn]= $realtions[$row[$this->relationshipConfigs['db_colum']]][$baseColumn];
                    // }
                // }
                }
            }
        } else {
            $result = $output['content'];
        }

        return $result;
    }

    /**
     * Generate a *.xls file with content with structure from config file.
     *
     * @throws FileWriteException if failed to write file
     */
    public function example_xls(): bool
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($this->configs as $key => $array) {
            // if (isset($array['empty'])) {
            //     continue;
            // }

            $i = $this->rowIndex;
            $sheet->setCellValue($key . $i, $array['field']);
            $sheet->setCellValue($key . (++$i), $array['sample']);
            $sheet->getColumnDimension($key)->setAutoSize(true);
        }

        if (isset($this->parseSheet) && !empty($this->relationshipConfigs['config_row']['SHEET'])) {
            $char = key($this->relationshipConfigs['config_row']['SHEET']);
            if (!empty($this->configs[$char]['field'])) {
                $sheet->setTitle($this->configs[$char]['field']);
            }
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');

        try {
            $writer->save("{$this->root}{$this->pathToLibrary}{$this->configId}/sample_{$this->libraryFileName}.xls");
        } catch (\Throwable $exception) {
            throw new FileWriteException("sample_{$this->libraryFileName}.xls", 0, $exception);
        }

        return true;
    }

    /**
     * Creates the file from uploaded file.
     *
     * @throws ValidationException on validation error
     * @throws FileWriteException  if failed to upload file
     */
    public function file_create(UploadedFile $file): string
    {
        $path = "{$this->root}{$this->pathToLibrary}{$this->configId}/'";
        $fileName = $this->sourceFileName;

        //region Cleanup
        // List of format permision to read form file config
        if (!empty($this->allowedExtensions)) {
            foreach ($this->allowedExtensions as $fileFormat) {
                if (is_file("{$path}{$fileName}.{$fileFormat}")) {
                    unlink("{$path}{$fileName}.{$fileFormat}");
                }
            }
        }
        //endregion Cleanup

        //region Validate file
        $constraintViolations = new ConstraintViolationList();
        if ($file->getSize() > static::MAX_FILE_SIZE) {
            $constraintViolations->add(new ConstraintViolation(
                static::MAX_FILE_SIZE,
                $file->getSize(),
                null,
                sprintf('The maximum file size has to be %s.', fileSizeSuffixText(static::MAX_FILE_SIZE)),
                'The maximum file size has to be %s.',
            ));
        }
        if (!in_array($file->getExtension(), $this->_allowed_extentsion)) {
            $constraintViolations->add(new ConstraintViolation(
                $this->_allowed_extentsion,
                $file->getExtension(),
                null,
                sprintf('Invalid file format. List of supported formats: %s.', implode(', ', $this->_allowed_extentsion)),
                'Invalid file format. List of supported formats: %s.',
            ));
        }
        if (0 !== $constraintViolations->count()) {
            throw new ValidationException('Failed to upload file due to validation errors', 0, null, $constraintViolations);
        }
        //endregion Validate file

        // Move the file to the directory where file must be stored
        $newFilename = "{$fileName}.{$file->guessExtension()}";

        try {
            $file->move($path, $newFilename);
        } catch (FileException $e) {
            throw new FileWriteException($newFilename, 0, $e);
        }

        return $newFilename;
    }

    /**
     * Get realtionship information.
     */
    private function get_relationship_information(array $relationships): array
    {
        /** @var Config_Lib_Model $libraryRepository */
        $libraryRepository = model(Config_Lib_Model::class);
        $result = $temp = array();
        foreach ($this->relationshipConfigs['db_table'] as $relation) {
            $tableName = $relation['from_table'];

            if (empty($relationships[$tableName]) || $temp[$tableName] == $relation['from_column']) {
                continue;
            }

            $temp[$tableName] = trim($relation['from_column']);
            $condition['db_select'] = $relation['from_column'];
            $condition['db_colum'] = $relation['return_key'];
            $condition['db_table'] = $tableName;
            $condition['where'] = '"' . implode('","', $relationships[$tableName]) . '"';
            $condition['where'] = str_replace('\\', '', $condition['where']);

            $result[$tableName] = $libraryRepository->select_by_relation($condition);
        }

        return $result;
    }

    /**
     * Parses the worksheet content.
     *
     * @param Worksheet   $worksheet   current sheet all data from this
     * @param null|string $sheetsTitle name of sheet
     *
     * @throws DomainException     if configuration is empty
     * @throws ParseException      if columns are invalid or not exist
     * @throws ValidationException if worksheet contains invalid data
     */
    private function parse_worksheet(Worksheet $worksheet, ?string $sheetsTitle = null): array
    {
        if (empty($this->configs)) {
            throw new DomainException('Configurations cannot be empty');
        }

        $topRow = $worksheet->getHighestDataRow();
        $topColumn = $worksheet->getHighestDataColumn();
        $configRelation = !empty($this->relationshipConfigs);
        $columns = array();

        for ($char = 65; $char <= ord($topColumn); ++$char) {
            if ($worksheet->cellExists(chr($char) . $this->rowIndex)) {
                if (trim($worksheet->getCell(chr($char) . $this->rowIndex)->getValue()) === $this->configs[chr($char)]['field']) {
                    $columns[chr($char)] = trim($worksheet->getCell(chr($char) . $this->rowIndex)->getValue());
                } else {
                    throw new ParseException(
                        $this->get_error_message('null_col', array('[column]' => $this->configs[chr($char)]['field'], '[row]' => $this->rowIndex))
                    );
                }
            }
        }

        $temp = array();
        $source = array();
        $result = array();
        $optional = array();
        $constraintViolations = new ConstraintViolationList();
        // Get value from cell
        for ($row = $this->rowIndex + 1; $row <= $topRow; ++$row) {
            $currentRow = array();
            $rowEmpty = false;

            foreach ($this->requiredColumns as $requiredColumn) {
                if ('' == trim($worksheet->getCell($requiredColumn . $row)->getValue())) {
                    $rowEmpty = true;
                }
            }

            if ($rowEmpty) {
                continue;
            }
            // Insert value from cell in current row list
            foreach ($columns as $key => $columnName) {
                $cell = trim($worksheet->getCell("{$key}{$row}")->getValue());
                $currentRow[$columnName] = '';

                if (isset($this->configs[$key]['empty']) && !empty($sheetsTitle)) {
                    $currentRow[$columnName] = $sheetsTitle;
                } else {
                    if ('' != $cell) {
                        $currentRow[$columnName] = $cell;
                    } else {
                        // Get value form last row
                        if ($this->configs[$key]['repeat_val'] && !empty($temp[$columnName])) {
                            $currentRow[$columnName] = $temp[$columnName];
                        }
                    }
                }

                // Clear input for functional relation
                if ($configRelation) {
                    if (is_array($this->relationshipConfigs['config_row'])) {
                        foreach ($this->relationshipConfigs['config_row'] as $configKey => $rowConfig) {
                            if ($this->configs[$configKey]['field'] === $columnName && '' != $currentRow[$columnName]) {
                                $currentRow[$columnName] = $this->normalize_text($currentRow[$columnName]);
                            }
                        }
                    }
                }

                // if($columnName === 'Country' && $currentRow[$columnName] != ''){
                //     $currentRow[$columnName] = $this->normalize_text($currentRow[$columnName]);
                // }

                // Validation column form rule config file
                try {
                    $this->validate_sheet_entry($this->configs[$key]['rule'], $columnName, $row, $currentRow, $this->configs[$key]['sample']);
                } catch (ValidationException $exception) {
                    $constraintViolations->merge(
                        $exception->getValidationErrors()
                    );

                    continue;
                }

                if (isset($this->configs[$key]['db_colum'])) {
                    $source[$this->configs[$key]['db_colum']] = $currentRow[$columnName];

                    // Functional relation
                    if ($configRelation) {
                        if (is_array($this->relationshipConfigs['config_row'])) {
                            foreach ($this->relationshipConfigs['config_row'] as $configKey => $rowConfig) {
                                if ($this->configs[$key]['db_colum'] === $this->configs[$configKey]['db_colum']) {
                                    if (!in_array($currentRow[$columnName], $optional[$this->relationshipConfigs['db_table'][$configKey]['from_table']])) {
                                        $optional[$this->relationshipConfigs['db_table'][$configKey]['from_table']][] = str_replace(
                                            "'",
                                            "\\'",
                                            $currentRow[$columnName]
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Set temp var
            $temp = $currentRow;
            $result['content'][] = $source;
        }

        if (0 !== $constraintViolations->count()) {
            throw new ValidationException('The content contains validation errors', 0, null, $constraintViolations);
        }

        // Functional relation
        if (!empty($optional)) {
            // $optional = array_map("trim", $optional);
            // $result['optional'] = "'" . implode("','", $optional) . "'";
            $result['optional'] = $optional;
        }

        return $result;
    }

    /**
     * Processes the content of the worksheet.
     *
     * @throws ParseException       if content empty
     * @throws ParseException       if columns are empty or invalid
     * @throws OutOfBoundsException if selected row doesn't exist
     * @throws ValidationException  if content is invalid
     */
    private function process_content(array $content, ?string $sheetTitle = null): array
    {
        // Check if content form file parse exist
        if (empty($content)) {
            throw new ParseException('The file content is empty');
        }

        // Check if row with key colum exist
        if (empty($content[$this->rowIndex])) {
            throw new OutOfBoundsException('Selected row is empty. Please select another row.');
        }

        // Create array with key column name
        $columns = array();
        foreach ($this->configs as $key => $params) {
            if (empty($content[$this->rowIndex][$key]) && !($content[$this->rowIndex][$key] == $params['field'])) {
                throw new ParseException(
                    $this->get_error_message('null_col', array('[column]' => $params['field'], '[row]' => $this->rowIndex))
                );
            }

            $columns[$key] = trim($content[$this->rowIndex][$key]);
        }

        unset($content[$this->rowIndex]);

        $temp = array();
        $result = array();
        $optional = array();
        $configRelation = !empty($this->relationshipConfigs['db_table']);
        $constraintViolations = new ConstraintViolationList();

        foreach ($content as $item => $rows) {
            $currentRow = array();
            $rowEmpty = false;

            // Check required columns
            foreach ($this->requiredColumns as $required_cell) {
                if (empty($rows[$required_cell])) {
                    $rowEmpty = true;
                }
            }

            if ($rowEmpty) {
                continue;
            }
            // Create array by config file
            foreach ($columns as $key => $column) {
                $cell = $rows[$key];
                $dbColumnName = $this->configs[$key]['db_colum'];
                $currentRow[$dbColumnName] = '';

                if (isset($this->configs[$key]['empty']) && !empty($sheetTitle)) {
                    $currentRow[$dbColumnName] = $sheetTitle;
                } else {
                    if ('' != $cell) {
                        $currentRow[$dbColumnName] = $cell;
                    } else {
                        // Get value form last row (temporary variable)
                        if ($this->configs[$key]['repeat_val'] && !empty($temp[$dbColumnName])) {
                            $currentRow[$dbColumnName] = $temp[$dbColumnName];
                        }
                    }
                }

                // Checking if this library have relation with another table from db
                if ($configRelation) {
                    foreach ($this->relationshipConfigs['config_row'] as $relationKey => $relation) {
                        $char_select = $relationKey;

                        // If file have sheets
                        if (is_array($relation)) {
                            $relationKey = key($relation);
                        }

                        // Clear value from cell
                        if ($this->configs[$relationKey]['db_colum'] === $dbColumnName && '' != $currentRow[$dbColumnName]) {
                            $currentRow[$dbColumnName] = $this->normalize_text($currentRow[$dbColumnName]);
                        }

                        // Filter value
                        if ($this->configs[$key]['db_colum'] === $this->configs[$relationKey]['db_colum']) {
                            if (!in_array($currentRow[$dbColumnName], $optional[$this->relationshipConfigs['db_table'][$char_select]['from_table']])) {
                                $optional[$this->relationshipConfigs['db_table'][$char_select]['from_table']][] = str_replace(
                                    "'",
                                    "\\'",
                                    $currentRow[$dbColumnName]
                                );
                            }
                        }
                    }
                }

                try {
                    $this->validate_sheet_entry($this->configs[$key]['rule'], $dbColumnName, $item, $currentRow, $this->configs[$key]['sample']);
                } catch (ValidationException $exception) {
                    $constraintViolations->merge(
                        $exception->getValidationErrors()
                    );
                }
            }

            // Set result array and temporary variable
            $result['content'][] = $temp = $currentRow;
        }

        if (0 !== $constraintViolations->count()) {
            throw new ValidationException('The content contains validation errors', 0, null, $constraintViolations);
        }

        // Functional relation
        if (!empty($optional)) {
            $result['optional'] = $optional;
        }

        return $result;
    }

    /**
     * Normalizes the text value.
     */
    private function normalize_text(string $value): string
    {
        $value = trim($value);

        return preg_replace('/([*])/', '', $value);
    }

    /**
     * Normalizes the URL address.
     */
    private function normalize_url(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        if (!preg_match('/^(http|https):\/\//', $url)) {
            return 'http://' . $url;
        }

        return $url;
    }

    /**
     * Get the error message.
     */
    private function get_error_message(string $key, ?array $context = null): string
    {
        if (!array_key_exists($key, $this->errorTexts)) {
            throw new OutOfBoundsException(
                sprintf('The error with the key "%s" is not found.', $key)
            );
        }

        return str_replace(
            array_keys($context ?? array()),
            array_values($context ?? array()),
            $this->errorTexts[$key]
        );
    }

    /**
     * Validated the row entry.
     *
     * @param mixed $sample
     */
    private function validate_sheet_entry(array $rules, string $columnName, int $rowIndex, array &$currentRow, $sample = null): void
    {
        $constraintViolations = new ConstraintViolationList();
        $validator = new class() {
            public function required($str): bool
            {
                return !empty(trim($str));
            }

            public function valid_url($str): bool
            {
                if (empty($str)) {
                    return true;
                }
                if (!preg_match('/^(http|https):\/\//', $str)) {
                    $str = 'http://' . $str;
                }

                return filter_var(trim($str), FILTER_VALIDATE_URL);
            }

            public function float($str): bool
            {
                return filter_var($str, FILTER_VALIDATE_FLOAT);
            }

            public function email($str): bool
            {
                if (empty($str)) {
                    return true;
                }
                $str = preg_replace('/\s+/', '', $str);

                return filter_var(trim($str), FILTER_VALIDATE_EMAIL);
            }

            public function minSize($str, $val): bool
            {
                if (!is_numeric($val)) {
                    return false;
                }

                return $val <= mb_strlen($str);
            }

            public function maxSize($str, $val): bool
            {
                if (!is_numeric($val)) {
                    return false;
                }

                return $val >= mb_strlen($str);
            }

            public function alpha_numeric($str): bool
            {
                return (!preg_match('/^([a-z0-9])+$/i', $str)) ? false : true;
            }
        };

        foreach ($rules as $rule => $params) {
            if ('valid_url' === $rule) {
                $currentRow[$columnName] = $this->normalize_url($currentRow[$columnName]);
            }

            if (!$validator->{$rule}($currentRow[$columnName], $params)) {
                $constraintViolations->add(new ConstraintViolation(
                    $currentRow[$columnName],
                    null,
                    null,
                    $this->get_error_message($rule, array('[row]' => $rowIndex, '[column]' => $columnName, "[{$rule}]" => $params, '[sample]' => $sample)),
                    $this->get_error_message($rule),
                ));
            }
        }

        if (0 !== $constraintViolations->count()) {
            throw new ValidationException('The entry is invalid.', 0, null, $constraintViolations);
        }
    }
}

// End of file tinymvc_library_sheet_parser.php
// Location: /tinymvc/myapp/plugins/tinymvc_library_sheet_parser.php
