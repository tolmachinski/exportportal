<?php
// Table name in database
$db_table= 'library_icc_country';

// Default file parse name
$file_xls_name = 'last_icc_country';

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
    'B'
);

// Config relation
$relation_config = array(
    'db_table'   => array(
        'A' => array(
            'from_table'    => 'port_country',              // Table relation  (FROM table)              -> config_lib_model -> select_by_relation
            'from_column'   => 'id, country',               // Column select form database
            'return_key'    => 'country',                   // Column relation (WHERE column_name IN ()) -> config_lib_model -> select_by_relation
        )
    ),
    'config_row' => array(                                  // Row form config ($config['A']);  if doesn't set optional array from parser is empty!
        'A' => 'id_country'
    ),
    'insert_column' => array(                               // Insert value from table relation by column (key = new table column; value = column from table select)
        'id_country'  => 'id',
    )
);

// Config for parse file
$config  = array(
    'A' => array(                                           // Column position in file parse
        'field'      => 'Country',                          // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'country',                          // Name of column in database, name of input from form
        'sample'     => 'USA',                              // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'required,minSize[3],maxSize[100]', // Rule for validationEngine (JS)
        'column_description'=> 'Column with country',       // Description of input
        'repeat_val' => true,                               // Repeat value if next row doesn't set
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
            'required' => true,
            'minSize'  => 3,
            'maxSize'  => 100,
        ),
    ),
    'B' => array(                                           // Column position in file parse
        'field'      => 'Agencies',                         // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'agencies',                         // Name of column in database, name of input from form
        'sample'     => 'Armenian Customs',                 // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'required,minSize[3],maxSize[100]', // Rule for validationEngine (JS)
        'column_description'=> 'Column with name of agencies',// Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
            'required' => true,
            'minSize'  => 3,
            'maxSize'  => 100,
        ),
    ),
    'C' => array(                                           // Column position in file parse
        'field'      => 'Phone Number',                     // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'phone',                            // Name of column in database, name of input from form
        'sample'     => '(000) 000-0000',                   // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'maxSize[150]',                     // Rule for validationEngine (JS)
        'column_description'=> 'Column with phone number',  // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
//            'required' => true,
//            'minSize'  => 3,
            'maxSize'  => 150,
        ),
    ),
    'D' => array(                                           // Column position in file parse
        'field'      => 'Email Address',                    // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'email',                            // Name of column in database, name of input from form
        'sample'     => 'exemple@exemple.com',              // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'maxSize[150]', //custom[email],    // Rule for validationEngine (JS)
        'column_description'=> 'Column with email of user', // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
//            'required' => true,
//            'email'    => true,
            'maxSize'  => 150,
        ),
    ),
    'E' => array(                                           // Column position in file parse
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

