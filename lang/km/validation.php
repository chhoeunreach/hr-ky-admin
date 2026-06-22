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
    'declined' => ':attribute ត្រូវតែត្រូវបានបដិសេធ។',
    'declined_if' => ':attribute ត្រូវតែត្រូវបានបដិសេធ នៅពេល :other ជា :value។',
    'different' => ':attribute និង :other ត្រូវតែខុសគ្នា។',
    'digits' => ':attribute ត្រូវតែមាន :digits ខ្ទង់។',
    'digits_between' => ':attribute ត្រូវតែមានចន្លោះពី :min ដល់ :max ខ្ទង់។',
    'dimensions' => ':attribute មានទំហំរូបភាពមិនត្រឹមត្រូវ។',
    'distinct' => 'វាល :attribute មានតម្លៃស្ទួន។',
    'email' => ':attribute ត្រូវតែជាអាសយដ្ឋានអ៊ីមែលត្រឹមត្រូវ។',
    'ends_with' => ':attribute ត្រូវតែបញ្ចប់ដោយតម្លៃមួយក្នុងចំណោម៖ :values។',
    'enum' => ':attribute ដែលបានជ្រើសមិនត្រឹមត្រូវទេ។',
    'exists' => ':attribute ដែលបានជ្រើសមិនត្រឹមត្រូវទេ។',
    'file' => ':attribute ត្រូវតែជាឯកសារ។',
    'filled' => 'វាល :attribute ត្រូវតែមានតម្លៃ។',
    'gt' => [
        'array' => ':attribute ត្រូវមានធាតុលើសពី :value។',
        'file' => ':attribute ត្រូវមានទំហំធំជាង :value គីឡូបៃ។',
        'numeric' => ':attribute ត្រូវធំជាង :value។',
        'string' => ':attribute ត្រូវមានតួអក្សរលើសពី :value។',
    ],
    'gte' => [
        'array' => ':attribute ត្រូវមានយ៉ាងហោចណាស់ :value ធាតុ។',
        'file' => ':attribute ត្រូវមានទំហំធំជាង ឬស្មើ :value គីឡូបៃ។',
        'numeric' => ':attribute ត្រូវធំជាង ឬស្មើ :value។',
        'string' => ':attribute ត្រូវមានតួអក្សរយ៉ាងហោចណាស់ :value។',
    ],
    'image' => ':attribute ត្រូវតែជារូបភាព។',
    'in' => ':attribute ដែលបានជ្រើសមិនត្រឹមត្រូវទេ។',
    'in_array' => 'វាល :attribute មិនមានក្នុង :other ទេ។',
    'integer' => ':attribute ត្រូវតែជាចំនួនគត់។',
    'ip' => ':attribute ត្រូវតែជាអាសយដ្ឋាន IP ត្រឹមត្រូវ។',
    'ipv4' => ':attribute ត្រូវតែជាអាសយដ្ឋាន IPv4 ត្រឹមត្រូវ។',
    'ipv6' => ':attribute ត្រូវតែជាអាសយដ្ឋាន IPv6 ត្រឹមត្រូវ។',
    'json' => ':attribute ត្រូវតែជាអត្ថបទ JSON ត្រឹមត្រូវ។',
    'lt' => [
        'array' => ':attribute ត្រូវមានធាតុតិចជាង :value។',
        'file' => ':attribute ត្រូវមានទំហំតិចជាង :value គីឡូបៃ។',
        'numeric' => ':attribute ត្រូវតិចជាង :value។',
        'string' => ':attribute ត្រូវមានតួអក្សរតិចជាង :value។',
    ],
    'lte' => [
        'array' => ':attribute មិនត្រូវមានធាតុលើសពី :value ទេ។',
        'file' => ':attribute ត្រូវមានទំហំតិចជាង ឬស្មើ :value គីឡូបៃ។',
        'numeric' => ':attribute ត្រូវតិចជាង ឬស្មើ :value។',
        'string' => ':attribute ត្រូវមានតួអក្សរតិចជាង ឬស្មើ :value។',
    ],
    'mac_address' => ':attribute ត្រូវតែជាអាសយដ្ឋាន MAC ត្រឹមត្រូវ។',
    'max' => [
        'array' => ':attribute មិនត្រូវមានធាតុលើសពី :max ទេ។',
        'file' => ':attribute មិនត្រូវមានទំហំលើសពី :max គីឡូបៃទេ។',
        'numeric' => ':attribute មិនត្រូវធំជាង :max ទេ។',
        'string' => ':attribute មិនត្រូវមានតួអក្សរលើសពី :max ទេ។',
    ],
    'mimes' => ':attribute ត្រូវតែជាឯកសារប្រភេទ៖ :values។',
    'mimetypes' => ':attribute ត្រូវតែជាឯកសារប្រភេទ៖ :values។',
    'min' => [
        'array' => ':attribute ត្រូវមានយ៉ាងហោចណាស់ :min ធាតុ។',
        'file' => ':attribute ត្រូវមានទំហំយ៉ាងហោចណាស់ :min គីឡូបៃ។',
        'numeric' => ':attribute ត្រូវយ៉ាងហោចណាស់ :min។',
        'string' => ':attribute ត្រូវមានតួអក្សរយ៉ាងហោចណាស់ :min។',
    ],
    'multiple_of' => ':attribute ត្រូវតែជាពហុគុណនៃ :value។',
    'not_in' => ':attribute ដែលបានជ្រើសមិនត្រឹមត្រូវទេ។',
    'not_regex' => 'ទម្រង់ :attribute មិនត្រឹមត្រូវទេ។',
    'numeric' => ':attribute ត្រូវតែជាលេខ។',
    'password' => [
        'letters' => ':attribute ត្រូវមានអក្សរយ៉ាងហោចណាស់មួយ។',
        'mixed' => ':attribute ត្រូវមានអក្សរធំ និងអក្សរតូចយ៉ាងហោចណាស់មួយ។',
        'numbers' => ':attribute ត្រូវមានលេខយ៉ាងហោចណាស់មួយ។',
        'symbols' => ':attribute ត្រូវមាននិមិត្តសញ្ញាយ៉ាងហោចណាស់មួយ។',
        'uncompromised' => ':attribute នេះធ្លាប់លេចធ្លាយក្នុងទិន្នន័យ។ សូមជ្រើស :attribute ផ្សេងទៀត។',
    ],
    'present' => 'វាល :attribute ត្រូវតែមានវត្តមាន។',
    'prohibited' => 'វាល :attribute ត្រូវបានហាមឃាត់។',
    'prohibited_if' => 'វាល :attribute ត្រូវបានហាមឃាត់ នៅពេល :other ជា :value។',
    'prohibited_unless' => 'វាល :attribute ត្រូវបានហាមឃាត់ លើកលែងតែ :other ស្ថិតក្នុង :values។',
    'prohibits' => 'វាល :attribute មិនអនុញ្ញាតឱ្យ :other មានវត្តមានទេ។',
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
        'array' => ':attribute ត្រូវមាន :size ធាតុ។',
        'file' => ':attribute ត្រូវមានទំហំ :size គីឡូបៃ។',
        'numeric' => ':attribute ត្រូវតែជា :size។',
        'string' => ':attribute ត្រូវមាន :size តួអក្សរ។',
    ],
    'starts_with' => ':attribute ត្រូវតែចាប់ផ្តើមដោយតម្លៃមួយក្នុងចំណោម៖ :values។',
    'doesnt_start_with' => ':attribute មិនអាចចាប់ផ្តើមដោយតម្លៃមួយក្នុងចំណោម៖ :values។',
    'string' => ':attribute ត្រូវតែជាអត្ថបទ។',
    'timezone' => ':attribute ត្រូវតែជាតំបន់ពេលវេលាត្រឹមត្រូវ។',
    'unique' => ':attribute ត្រូវបានប្រើរួចហើយ។',
    'uploaded' => ':attribute បង្ហោះមិនបានសម្រេចទេ។',
    'url' => ':attribute ត្រូវតែជា URL ត្រឹមត្រូវ។',
    'uuid' => ':attribute ត្រូវតែជា UUID ត្រឹមត្រូវ។',

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
            'rule-name' => 'សារផ្ទាល់ខ្លួន',
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
