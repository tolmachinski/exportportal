<?php

namespace App\Documents\Versioning;

final class ContentContextEntries
{
    public const REQUIRES_DYNAMIC_FIELDS = 'requires_dynamic_fields';

    public const DYNAMIC_FIELDS_NAMES_LIST = 'dynamic_fields_names_list';

    public const DYNAMIC_FIELDS_STORED_VALUES = 'dynamic_fields_stored_values';

    public const DOCUMENT_LEGAL_TYPE_NAME = 'document_legal_type_name';

    public const DOCUMENT_LEGAL_TYPE_GROUP = 'document_legal_type_group';

    public const DOCUMENT_ELIGIBLE_FOR_TRANSFER = 'document_eligible_for_transfer';

    private function __construct()
    {
        // None shall pass!!!
    }
}
