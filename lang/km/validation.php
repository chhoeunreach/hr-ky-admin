<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute ត្រូវតែត្រូវបានទទួលយក។',
    'accepted_if' => ':attribute ត្រូវតែត្រូវបានទទួលយកនៅពេល :other ជា :value។',
    'active_url' => ':attribute មិនមែនជា URL ត្រឹមត្រូវទេ។',
    'after' => ':attribute ត្រូវតែជាកាលបរិច្ឆេទបន្ទាប់ពី :date។',
    'after_or_equal' => ':attribute ត្រូវតែជាកាលបរិច្ឆេទបន្ទាប់ពី ឬស្មើ :date។',
    'alpha' => ':attribute ត្រូវមានតែអក្សរប៉ុណ្ណោះ។',
    'alpha_dash' => ':attribute ត្រូវមានតែអក្សរ លេខ សញ្ញា - និង _ ប៉ុណ្ណោះ។',
    'alpha_num' => ':attribute ត្រូវមានតែអក្សរ និងលេខប៉ុណ្ណោះ។',
    'array' => ':attribute ត្រូវតែជាអារេ។',
    'before' => ':attribute ត្រូវតែជាកាលបរិច្ឆេទមុន :date។',
    'before_or_equal' => ':attribute ត្រូវតែជាកាលបរិច្ឆេទមុន ឬស្មើ :date។',
    'between' => [
        'array' => ':attribute ត្រូវមានចន្លោះពី :min ដល់ :max ធាតុ។',
        'file' => ':attribute ត្រូវមានទំហំចន្លោះពី :min ដល់ :max គីឡូបៃ។',
        'numeric' => ':attribute ត្រូវស្ថិតនៅចន្លោះពី :min ដល់ :max។',
        'string' => ':attribute ត្រូវមានចន្លោះពី :min ដល់ :max តួអក្សរ។',
    ],
    'boolean' => 'វាល :attribute ត្រូវតែជា true ឬ false។',
    'confirmed' => 'ការបញ្ជាក់ :attribute មិនត្រូវគ្នាទេ។',
    'current_password' => 'ពាក្យសម្ងាត់មិនត្រឹមត្រូវទេ។',
    'date' => ':attribute មិនមែនជាកាលបរិច្ឆេទត្រឹមត្រូវទេ។',
    'date_equals' => ':attribute ត្រូវតែជាកាលបរិច្ឆេទស្មើនឹង :date។',
    'date_format' => ':attribute មិនត្រូវនឹងទម្រង់ :format ទេ។',
    'declined' => 'The :attribute must be declined.',
    'declined_if' => 'The :attribute must be declined when :other is :value.',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'dimensions' => 'The :attribute has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'email' => ':attribute ត្រូវតែជាអាសយដ្ឋានអ៊ីមែលត្រឹមត្រូវ។',
    'ends_with' => 'The :attribute must end with one of the following: :values.',
    'enum' => 'The selected :attribute is invalid.',
    'exists' => ':attribute ដែលបានជ្រើសមិនត្រឹមត្រូវទេ។',
    'file' => ':attribute ត្រូវតែជាឯកសារ។',
    'filled' => 'វាល :attribute ត្រូវតែមានតម្លៃ។',
    'gt' => [
        'array' => 'The :attribute must have more than :value items.',
        'file' => 'The :attribute must be greater than :value kilobytes.',
        'numeric' => 'The :attribute must be greater than :value.',
        'string' => 'The :attribute must be greater than :value characters.',
    ],
    'gte' => [
        'array' => 'The :attribute must have :value items or more.',
        'file' => 'The :attribute must be greater than or equal to :value kilobytes.',
        'numeric' => 'The :attribute must be greater than or equal to :value.',
        'string' => 'The :attribute must be greater than or equal to :value characters.',
    ],
    'image' => ':attribute ត្រូវតែជារូបភាព។',
    'in' => ':attribute ដែលបានជ្រើសមិនត្រឹមត្រូវទេ។',
    'in_array' => 'វាល :attribute មិនមានក្នុង :other ទេ។',
    'integer' => ':attribute ត្រូវតែជាចំនួនគត់។',
    'ip' => ':attribute ត្រូវតែជាអាសយដ្ឋាន IP ត្រឹមត្រូវ។',
    'ipv4' => 'The :attribute must be a valid IPv4 address.',
    'ipv6' => 'The :attribute must be a valid IPv6 address.',
    'json' => 'The :attribute must be a valid JSON string.',
    'lt' => [
        'array' => 'The :attribute must have less than :value items.',
        'file' => 'The :attribute must be less than :value kilobytes.',
        'numeric' => 'The :attribute must be less than :value.',
        'string' => 'The :attribute must be less than :value characters.',
    ],
    'lte' => [
        'array' => 'The :attribute must not have more than :value items.',
        'file' => 'The :attribute must be less than or equal to :value kilobytes.',
        'numeric' => 'The :attribute must be less than or equal to :value.',
        'string' => 'The :attribute must be less than or equal to :value characters.',
    ],
    'mac_address' => 'The :attribute must be a valid MAC address.',
    'max' => [
        'array' => 'The :attribute must not have more than :max items.',
        'file' => 'The :attribute must not be greater than :max kilobytes.',
        'numeric' => 'The :attribute must not be greater than :max.',
        'string' => 'The :attribute must not be greater than :max characters.',
    ],
    'mimes' => 'The :attribute must be a file of type: :values.',
    'mimetypes' => 'The :attribute must be a file of type: :values.',
    'min' => [
        'array' => 'The :attribute must have at least :min items.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'numeric' => 'The :attribute must be at least :min.',
        'string' => 'The :attribute must be at least :min characters.',
    ],
    'multiple_of' => 'The :attribute must be a multiple of :value.',
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute format is invalid.',
    'numeric' => ':attribute ត្រូវតែជាលេខ។',
    'password' => [
        'letters' => 'The :attribute must contain at least one letter.',
        'mixed' => 'The :attribute must contain at least one uppercase and one lowercase letter.',
        'numbers' => 'The :attribute must contain at least one number.',
        'symbols' => 'The :attribute must contain at least one symbol.',
        'uncompromised' => 'The given :attribute has appeared in a data leak. Please choose a different :attribute.',
    ],
    'present' => 'The :attribute field must be present.',
    'prohibited' => 'The :attribute field is prohibited.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'prohibits' => 'The :attribute field prohibits :other from being present.',
    'regex' => 'ទម្រង់ :attribute មិនត្រឹមត្រូវទេ។',
    'required' => 'សូមបំពេញវាល :attribute។',
    'required_array_keys' => 'វាល :attribute ត្រូវមានធាតុសម្រាប់៖ :values។',
    'required_if' => 'សូមបំពេញវាល :attribute នៅពេល :other ជា :value។',
    'required_unless' => 'សូមបំពេញវាល :attribute លើកលែងតែ :other ស្ថិតក្នុង :values។',
    'required_with' => 'សូមបំពេញវាល :attribute នៅពេលមាន :values។',
    'required_with_all' => 'សូមបំពេញវាល :attribute នៅពេលមាន :values។',
    'required_without' => 'សូមបំពេញវាល :attribute នៅពេលគ្មាន :values។',
    'required_without_all' => 'សូមបំពេញវាល :attribute នៅពេលគ្មាន :values ទាំងអស់។',
    'same' => ':attribute និង :other ត្រូវតែដូចគ្នា។',
    'size' => [
        'array' => 'The :attribute must contain :size items.',
        'file' => 'The :attribute must be :size kilobytes.',
        'numeric' => 'The :attribute must be :size.',
        'string' => 'The :attribute must be :size characters.',
    ],
    'starts_with' => 'The :attribute must start with one of the following: :values.',
    'doesnt_start_with' => 'The :attribute may not start with one of the following: :values.',
    'string' => ':attribute ត្រូវតែជាអត្ថបទ។',
    'timezone' => 'The :attribute must be a valid timezone.',
    'unique' => ':attribute ត្រូវបានប្រើរួចហើយ។',
    'uploaded' => ':attribute បង្ហោះមិនបានសម្រេចទេ។',
    'url' => ':attribute ត្រូវតែជា URL ត្រឹមត្រូវ។',
    'uuid' => 'The :attribute must be a valid UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
