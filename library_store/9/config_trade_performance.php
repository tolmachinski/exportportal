<?php
$sheetParse = true;

// Table name in database
$db_table= 'library_consulates';

// Default file parse name
$file_xls_name = 'last_consulate';

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
    'B','M'
);

// Config relation
$relation_config = array(
    'db_table' => array(
        'SHEET' => array(
            'from_table'  => 'port_country',                // Table relation  (FROM table)              -> config_lib_model -> select_by_relation
            'from_column' => 'id, country',                 // Column select from database
            'return_key'  => 'country',                     // Column relation (WHERE column_name IN ()) -> config_lib_model -> select_by_relation
        ),
    ),
    'config_row'=> array(                                   // Column insert by relation. THIS NEED COINCIDE WITH NAME FROM ARRAY! -> $relation_config['insert_column']
        'SHEET' => array(
            'A' => 'id_country',
        ),
    ),
    'insert_column' => array(                               // Insert value from table relation by column (key = new table column; value = column from table select)
        'id_country' => 'id',
    )
);

// Config for parse file
$config  = array(
    'A' => array(                                           // Column position in file parse
        'field'      => 'Country',                          // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'country',                          // Name of column in database, name of input from form
        'empty'      => true,                               // If in this column not data
        'sample'     => 'USA',                              // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'required',                         // Rule for validationEngine (JS)
        'column_description'=> 'Column with country',       // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
//            'required' => true,
//            'minSize'  => 3,
//            'maxSize'  => 100,
        ),
    ),
    'B' => array(                                           // Column position in file parse
        'field'      => 'Industry',                         // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'industry',                         // Name of column in database, name of input from form
        'sample'     => 'All industries',                   // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'required,maxSize[150]',            // Rule for validationEngine (JS)
        'column_description'=> 'Column with Industry name',       // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
            'required' => true,
//            'minSize'  => 2,
            'maxSize'  => 100,
        ),
    ),
    'D' => array(                                           // Column position in file parse
        'field'      => 'Exports in value',                 // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'export',                           // Name of column in database, name of input from form
        'sample'     => '0000000',                           // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'maxSize[20]',                     // Rule for validationEngine (JS)
        'column_description'=> 'Column with value',  // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
//            'required' => true,
//            'minSize'  => 3,
            'maxSize'  => 20,
        ),
    ),
    'E' => array(                                           // Column position in file parse
        'field'      => 'Imports in value',                 // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'import',                           // Name of column in database, name of input from form
        'sample'     => '0000000',                           // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'maxSize[20]',                      //custom[email],    // Rule for validationEngine (JS)
        'column_description'=> 'Column with value',          // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
//            'required' => true,
//            'email'    => true,
            'maxSize'  => 20,
        ),
    ),
    'F' => array(                                           // Column position in file parse
        'field'      => 'Net trade in value',               // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'trade',                            // Name of column in database, name of input from form
        'sample'     => '0000000',                           // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'maxSize[20]',                      // Rule for validationEngine (JS)
        'column_description'=> 'Column with net trade in value',       // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
//            'required' => true,
//            'valid_url'=> true,
            'maxSize'  => 20,
        ),
    ),
    'G' => array(
        'field'      => 'Exports as a share of total exports (%)',// Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'total_export',                    // Name of column in database, name of input from form
        'sample'     => '0000000',                           // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'maxSize[20]',//custom[email],      // Rule for validationEngine (JS)
        'column_description'=> 'Column with value',         // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
//            'required' => true,
//            'email'    => true,
            'maxSize'  => 20,
        ),
    ),
    'H' => array(
        'field'      => 'Imports as a share of total imports (%)',// Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'total_import',                     // Name of column in database, name of input from form
        'sample'     => '0000000',                          // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'maxSize[20]',                      // Rule for validationEngine (JS)
        'column_description'=> 'Column with value',         // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
//            'required' => true,
//            'minSize'  => 3,
            'maxSize'  => 20,
        ),
    ),
    'I' => array(                                           // Column position in file parse
        'field'      => 'Exports as a share of world exports (%)',// Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'world_export',                     // Name of column in database, name of input from form
        'sample'     => '0000000',                          // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'maxSize[20]',                      // Rule for validationEngine (JS)
        'column_description'=> 'Column with world exports (%)',// Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
//            'required' => true,
//            'minSize'  => 3,
            'maxSize'  => 20,
        ),
    ),
    'J' => array(                                           // Column position in file parse
        'field'      => 'Imports as a share of world imports (%)',// Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'world_import',                     // Name of column in database, name of input from form
        'sample'     => '0000000',                          // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'maxSize[20]',                      // Rule for validationEngine (JS)
        'column_description'=> 'Column with world imports (%)',// Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
//            'required' => true,
//            'minSize'  => 3,
            'maxSize'  => 20,
        ),
    ),
    'K' => array(                                           // Column position in file parse
        'field'      => 'Growth of exports in value (% p.a.)',// Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'growth_export',                    // Name of column in database, name of input from form
        'sample'     => '00',                               // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'maxSize[20]',                      // Rule for validationEngine (JS)
        'column_description'=> 'Column with growth of exports',// Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
//            'required' => true,
//            'minSize'  => 3,
            'maxSize'  => 20,
        ),
    ),
    'L' => array(                                           // Column position in file parse
        'field'      => 'Growth of imports in value (% p.a.)',// Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'growth_import',                    // Name of column in database, name of input from form
        'sample'     => '00',                               // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'maxSize[20]',                      // Rule for validationEngine (JS)
        'column_description'=> 'Column with growth of imports',// Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
//            'required' => true,
//            'minSize'  => 3,
            'maxSize'  => 20,
        ),
    ),
    'M' => array(                                           // Column position in file parse
        'field'      => 'Net Trade (X-M)/(X+M) * 100',      // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'net_trade',                        // Name of column in database, name of input from form
        'sample'     => '00',                               // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'required, maxSize[20]',            // Rule for validationEngine (JS)
        'column_description'=> 'Column with net Trade (X-M)/(X+M) * 100',// Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
            'required' => true,
//            'minSize'  => 3,
            'maxSize'  => 20,
        ),
    ),
);
