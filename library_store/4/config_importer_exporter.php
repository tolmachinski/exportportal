<?php
// Table name in database
$db_table= 'library_importer_exporter';

// Default file parse name
$file_xls_name = 'last_importer_exporter';

// List of format readfile
$allowed_extension = array(
    'xls',
    'xlsx',
    'xml',
    'csv',
);

// Key Colum from file parse
$key_row = 1;

// Check Colum by Char for parser
$required_column = array(
    'A','E'
);

// Config relation
$relation_config = array(
    'db_table'      => array(),                             // Table relation  (FROM table)              -> config_lib_model -> select_by_relation
    'config_row'    => array(),                             // Row form config ($config['A']); if doesn't set optional array from parser is empty!
    'insert_column' => array(),                             // Insert value from table relation by column (key = new table column; value = column from table select)
);

// Config for parse file
$config  = array(
    'A' => array(                                           // Column position in file parse
        'field'      => 'Company Name',                     // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'company',                          // Name of column in database, name of input from form
        'sample'     => 'IMacDac',                          // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'required,minSize[3],maxSize[150]', // Rule for validationEngine (JS)
        'column_description'=> 'Column with company name',  // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
            'required' => true,
            'minSize'  => 3,
            'maxSize'  => 150,
        ),
    ),
    'B' => array(
        'field'      => 'Address',
        'db_colum'   => 'address',
        'sample'     => 'Wall Street',
        'type_insert'=> 'input',
        'rule_js'    => 'maxSize[200]',
        'column_description'=> 'Column with address',
        'rule'       => array(
//            'required' => true,
//            'minSize'  => 3,
            'maxSize'  => 200,
        ),
    ),
    'C' => array(
        'field'      => 'Phone',
        'db_colum'   => 'phone',
        'sample'     => '(000) 000-0000',
        'type_insert'=> 'input',
        'rule_js'    => 'maxSize[150]',
        'column_description'=> 'Column with phone number',
        'rule'       => array(
//            'required' => true,
            'maxSize'  => 150,
        ),
    ),
    'D' => array(
        'field'      => 'Email',
        'db_colum'   => 'email',
        'sample'     => 'exemple@exemple.com',
        'type_insert'=> 'input',
        'rule_js'    => 'custom[email],maxSize[150]',
        'column_description'=> 'Column with email of user',
        'rule'       => array(
//            'required' => true,
            'email'    => true,
            'maxSize'  => 150,
        ),
    ),
    'E' => array(
        'field'      => 'Webpage',
        'db_colum'   => 'url_site',
        'sample'     => 'http://company.com/',
        'type_insert'=> 'input',
        'rule_js'    => 'required,maxSize[150],custom[url]',
        'column_description'=> 'Column with website',
        'rule'       => array(
            'required' => true,
            'valid_url'=> true,
            'maxSize'  => 150,
        ),
    ),
);

