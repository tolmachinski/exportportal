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
    'B','C'
);

// Config relation
$relation_config = array(
    'db_table'   => array(
        'SHEET' => array(
            'from_table'    => 'port_country',              // Table relation  (FROM table)              -> config_lib_model -> select_by_relation
            'from_column'   => 'id, country',               // Column select form database
            'return_key'    => 'country',                   // Column relation (WHERE column_name IN ()) -> config_lib_model -> select_by_relation
        ),
        'B' => array(
            'from_table'    => 'port_country',              // Table relation  (FROM table)              -> config_lib_model -> select_by_relation
            'from_column'   => 'id, country',               // Column select form database
            'return_key'    => 'country',                   // Column relation (WHERE column_name IN ()) -> config_lib_model -> select_by_relation
        ),
    ),
    'config_row' => array(                                  // Column insert by relation. THIS NEED COINCIDE WITH NAME FROM ARRAY! -> $relation_config['insert_column']
        'SHEET' => array(
            'A' => 'id_country',
        ),
        'B'     => 'id_country_cons',
    ),
    'insert_column' => array(                               // Insert value from table relation by column (key = new table column; value = column from table select)
        'id_country'     => 'id',
        'id_country_cons'=> 'id',
    )
);

// Config for parse file
$config  = array(
    'A' => array(                                           // Column position in file parse
        'field'      => 'Country 1',                        // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'country_main',                     // Name of column in database, name of input from form
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
        'field'      => 'Country 2',                        // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'country_consulate',                // Name of column in database, name of input from form
        'sample'     => 'USA',                              // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'required,minSize[3],maxSize[100]', // Rule for validationEngine (JS)
        'column_description'=> 'Column with country',       // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
            'required' => true,
//            'minSize'  => 2,
            'maxSize'  => 100,
        ),
    ),
    'C' => array(                                           // Column position in file parse
        'field'      => 'Mission/Name',                     // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'mission_name',                     // Name of column in database, name of input from form
        'sample'     => 'Australian Honorary Consulate in Luanda',// Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'maxSize[200]',                     // Rule for validationEngine (JS)
        'column_description'=> 'Column with Mission/Name',  // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
//            'required' => true,
//            'minSize'  => 3,
            'maxSize'  => 200,
        ),
    ),
    'D' => array(                                           // Column position in file parse
        'field'      => 'Head',                             // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'head',                             // Name of column in database, name of input from form
        'sample'     => 'Clive Paul de Souza, Honorary Consul',// Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'maxSize[200]', //custom[email],    // Rule for validationEngine (JS)
        'column_description'=> 'Column with Head', // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
//            'required' => true,
//            'email'    => true,
            'maxSize'  => 200,
        ),
    ),
    'E' => array(                                           // Column position in file parse
        'field'      => 'Website',                          // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'url_site',                         // Name of column in database, name of input from form
        'sample'     => 'http://company.com/',              // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'maxSize[150],custom[url]',         // Rule for validationEngine (JS)
        'column_description'=> 'Column with website',       // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
//            'required' => true,
//            'valid_url'=> true,
            'maxSize'  => 150,
        ),
    ),
    'F' => array(
        'field'      => 'Email',                            // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'email',                            // Name of column in database, name of input from form
        'sample'     => 'exemple@exemple.com',              // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'maxSize[150]',//custom[email],     // Rule for validationEngine (JS)
        'column_description'=> 'Column with email of user', // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
//            'required' => true,
//            'email'    => true,
            'maxSize'  => 150,
        ),
    ),
    'G' => array(
        'field'      => 'Address',                          // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'address',                          // Name of column in database, name of input from form
        'sample'     => 'Wall Street',                      // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'required,minSize[3],maxSize[200]', // Rule for validationEngine (JS)
        'column_description'=> 'Column with address',       // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
//            'required' => true,
//            'minSize'  => 3,
            'maxSize'  => 200,
        ),
    ),
    'H' => array(                                           // Column position in file parse
        'field'      => 'Phone',                            // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'phone',                            // Name of column in database, name of input from form
        'sample'     => '(000) 000-0000',                   // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'maxSize[250]',                     // Rule for validationEngine (JS)
        'column_description'=> 'Column with phone number',  // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
//            'required' => true,
//            'minSize'  => 3,
            'maxSize'  => 250,
        ),
    ),
    'I' => array(                                           // Column position in file parse
        'field'      => 'Contact person',                   // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'person_name',                      // Name of column in database, name of input from form
        'sample'     => 'Test Test',                        // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'maxSize[500]',                     // Rule for validationEngine (JS)
        'column_description'=> 'Column with name of person',// Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
//            'required' => true,
//            'minSize'  => 3,
            'maxSize'  => 500,
        ),
    ),
    'J' => array(                                           // Column position in file parse
        'field'      => 'Contact person email',             // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'person_email',                     // Name of column in database, name of input from form
        'sample'     => 'exemple@exemple.com',                        // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'custom[email],maxSize[150]',                     // Rule for validationEngine (JS)
        'column_description'=> 'Column with name of person',// Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
//            'required' => true,
//            'minSize'  => 3,
            'maxSize'  => 150,
        ),
    ),
);
