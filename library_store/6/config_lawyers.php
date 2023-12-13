<?php
// Table name in database
$db_table= 'library_lawyers';

// Default file parse name
$file_xls_name = 'last_list_lawyers';

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
    'A','B','C'
);

// Config relation
$relation_config = array(
    'db_table'      => array(),                             // Table relation  (FROM table)              -> config_lib_model -> select_by_relation
    'config_row'    => array(),                             // Row form config ($config['A']); if doesn't set optional array from parser is empty!
    'insert_column' => array(),                             // Insert value from table relation by column (key = new table column; value = column from table select)
);

// Config for parse file
$config  = array(
    'A' => array(
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
        'field'      => 'Address',                          // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'address',                          // Name of column in database, name of input from form
        'sample'     => 'Wall Street',                      // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'required,minSize[3],maxSize[200]', // Rule for validationEngine (JS)
        'column_description'=> 'Column with address',       // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
            'required' => true,
            'minSize'  => 3,
            'maxSize'  => 200,
        ),
    ),
    'C' => array(
        'field'      => 'Phone Number',                     // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'phone',                            // Name of column in database, name of input from form
        'sample'     => '(000) 000-0000',                   // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'required,minSize[3],maxSize[150]', // Rule for validationEngine (JS)
        'column_description'=> 'Column with phone number',  // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
            'required' => true,
            'minSize'  => 3,
            'maxSize'  => 150,
        ),
    ),
    'D' => array(
        'field'      => 'Email Address',                    // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'email',                            // Name of column in database, name of input from form
        'sample'     => 'exemple@exemple.com',              // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'custom[email],maxSize[150]',       // Rule for validationEngine (JS)
        'column_description'=> 'Column with email of user', // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
//            'required' => true,
//            'email'    => true,
            'maxSize'  => 150,
        ),
    ),
    'E' => array(
        'field'      => 'Website',                          // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'url_site',                         // Name of column in database, name of input from form
        'sample'     => 'http://company.com/',              // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'maxSize[100],custom[url]',         // Rule for validationEngine (JS)
        'column_description'=> 'Column with website',       // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
//            'required' => true,
            'valid_url'=> true,
            'maxSize'  => 100,
        ),
    ),
);

