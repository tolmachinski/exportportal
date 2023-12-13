<?php
// Table name in database
$db_table= 'library_accreditation_body';

// Default file parse name
$file_xls_name = 'last_accreditation';

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
    'db_table'   => array(
        'A' => array(
            'from_table'    => 'port_country',              // Table relation  (FROM table)              -> config_lib_model -> select_by_relation
            'from_column'   => 'id, country',               // Column select form database
            'return_key'    => 'country',                   // Column relation (WHERE column_name IN ()) -> config_lib_model -> select_by_relation
        )
    ),
    'config_row' => array(
        'A' => 'id_country'                                 // Row form config ($config['A']);  if doesn't set optional array from parser is empty!
    ),
    'insert_column' => array(                               // Insert value from table relation by column (key = new table column; value = column from table select)
        'id_country'  => 'id',
//        'name_country'=> 'country'
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
        'column_description'=> 'Column with country name',  // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
            'required' => true,
            'minSize'  => 3,
            'maxSize'  => 100,
        ),
    ),
    'B' => array(                                           // Column position in file parse
        'field'      => 'Body',                             // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'body',                             // Name of column in database, name of input from form
        'sample'     => 'Organismo Argentino de Acreditacion (OAA)',// Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'required,minSize[3],maxSize[150]', // Rule for validationEngine (JS)
        'column_description'=> 'Column with phone number',  // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
            'required' => true,
            'minSize'  => 3,
            'maxSize'  => 150,
        ),
    ),
    'C' => array(                                           // Column position in file parse
        'field'      => 'Contact',                          // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'contact',                          // Name of column in database, name of input from form
        'sample'     => 'Mr. Mario Aramburu',               // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'required,minSize[3],maxSize[150]', // Rule for validationEngine (JS)
        'column_description'=> 'Column with user contact',  // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
            'required' => true,
            'minSize'  => 3,
            'maxSize'  => 150,
        ),
    ),
    'D' => array(                                           // Column position in file parse
        'field'      => 'Title',                            // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'title',                            // Name of column in database, name of input from form
        'sample'     => 'General Manager Accreditation',    // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'maxSize[150]',                     // Rule for validationEngine (JS)
        'column_description'=> 'Column with title',         // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
//            'required' => true,
//            'minSize'  => 3,
            'maxSize'  => 150,
        ),
    ),
    'E' => array(                                           // Column position in file parse
        'field'      => 'Address',                          // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'address',                          // Name of column in database, name of input from form
        'sample'     => 'Wall Street',                      // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'required,minSize[3],maxSize[300]', // Rule for validationEngine (JS)
        'column_description'=> 'Column with address',       // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
            'required' => true,
            'minSize'  => 3,
            'maxSize'  => 300,
        ),
    ),
    'F' => array(                                           // Column position in file parse
        'field'      => 'Telephone Number',                 // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
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
    'G' => array(                                           // Column position in file parse
        'field'      => 'Email',                            // Name of field in file *.xls, Name of lable for form Add and Edit (folder 'library_setting/admin/common_form')
        'db_colum'   => 'email',                            // Name of column in database, name of input from form
        'sample'     => 'exemple@exemple.com',              // Example of record
        'type_insert'=> 'input',                            // Type of insert if insert record manual
        'rule_js'    => 'required,custom[email],maxSize[150]',// Rule for validationEngine (JS)
        'column_description'=> 'Column with email of user', // Description of input
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
            'required' => true,
            'email'    => true,
            'maxSize'  => 150,
        ),
    ),
    'H' => array(                                           // Column position in file parse
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

