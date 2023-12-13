<?php
// Table name in database
$db_table= 'library_inspection_agency';

// Default file parse name
$file_xls_name = 'last_inspection_agency';

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
//$relation_config = array(
//    'db_select'  => 'id, country',                          // Column select form database
//    'db_colum'   => 'country',                              // Column relation (WHERE column_name IN ()) -> config_lib_model -> select_by_relation
//    'db_table'   => 'port_country',                         // Table relation  (FROM table)              -> config_lib_model -> select_by_relation
//    'config_row' => 'A',                                    // Row form config ($config['A']); if doesn't set optional array from parser is empty!
//    'insert_column' => array(                               // Insert value from table relation by column (key = new table column; value = column from table select)
//        'id_country'  => 'id',
//    )
//);
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
        'rule'       => array(                              // Rule for validator PHP (validator in library phpexcel)
            'required' => true,
            'minSize'  => 3,
            'maxSize'  => 100,
        ),
    ),
    'B' => array(
        'field'      => 'Company Name',
        'db_colum'   => 'company',
        'sample'     => 'IMacDac',
        'type_insert'=> 'input',
        'rule_js'    => 'required,minSize[3],maxSize[150]',
        'column_description'=> 'Column with company name',
        'rule'       => array(
            'required' => true,
            'minSize'  => 3,
            'maxSize'  => 150,
        ),
    ),
    'C' => array(
        'field'      => 'Address',
        'db_colum'   => 'address',
        'sample'     => 'Wall Street',
        'type_insert'=> 'input',
        'rule_js'    => 'maxSize[200]',
        'column_description'=> 'Column with address',
        'rule'       => array(
            'required' => true,
            'maxSize'  => 200,
        ),
    ),
    'D' => array(
        'field'      => 'Telephone',
        'db_colum'   => 'phone',
        'sample'     => '(000) 000-0000',
        'type_insert'=> 'input',
        'rule_js'    => 'maxSize[150]', //required,minSize[3],
        'column_description'=> 'Column with phone number',
        'rule'       => array(
//            'required' => true,
//            'minSize'  => 3,
            'maxSize'  => 150,
        ),
    ),
    'E' => array(
        'field'      => 'Website',
        'db_colum'   => 'url_site',
        'sample'     => 'http://company.com/',
        'type_insert'=> 'input',
        'rule_js'    => 'maxSize[100],custom[url]',
        'column_description'=> 'Column with website',
        'rule'       => array(
            'valid_url'=> true,
            'maxSize'  => 100,
        ),
    ),
    'F' => array(
        'field'      => 'Email',
        'db_colum'   => 'email',
        'sample'     => 'exemple@exemple.com',
        'type_insert'=> 'input',
        'rule_js'    => 'maxSize[150],custom[email]',   //required,
        'column_description'=> 'Column with email of user',
        'rule'       => array(
//            'required' => true,
            'email'    => true,
            'maxSize'  => 150,
        ),
    ),
    'G' => array(
        'field'      => 'Services provided',
        'db_colum'   => 'services_provided',
        'sample'     => 'Description',
        'type_insert'=> 'textarea',
        'rule_js'    => '',
        'column_description'=> 'Column with services provided',
        'rule'       => array(
//            'required' => true,
        ),
    ),
);

