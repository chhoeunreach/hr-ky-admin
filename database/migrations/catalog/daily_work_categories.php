<?php

$activity = [
    ['Sales', 'ការលក់', [['Customer consultation', 'ការប្រឹក្សាអតិថិជន'], ['Product demonstration', 'ការបង្ហាញផលិតផល'], ['Sales follow-up', 'ការតាមដានការលក់'], ['Sales support', 'ការគាំទ្រការលក់'], ['Customer communication', 'ការទំនាក់ទំនងជាមួយអតិថិជន']]],
    ['Customer Service', 'សេវាអតិថិជន', [['Customer assistance', 'ជំនួយអតិថិជន'], ['Customer follow-up', 'ការតាមដានអតិថិជន'], ['Product information', 'ព័ត៌មានផលិតផល'], ['After-sales support', 'ការគាំទ្រក្រោយការលក់']]],
    ['Inventory', 'ស្តុក', [['Stock counting', 'ការរាប់ស្តុក'], ['Stock transfer', 'ការផ្ទេរស្តុក'], ['Product inspection', 'ការត្រួតពិនិត្យផលិតផល'], ['Inventory organization', 'ការរៀបចំស្តុក'], ['Stock reconciliation', 'ការផ្ទៀងផ្ទាត់ស្តុក']]],
    ['Repair', 'ជួសជុល', [['Device inspection', 'ការត្រួតពិនិត្យឧបករណ៍'], ['Diagnosis', 'ការវិនិច្ឆ័យ'], ['Repair work', 'ការងារជួសជុល'], ['Device testing', 'ការធ្វើតេស្តឧបករណ៍'], ['Customer device handover', 'ការប្រគល់ឧបករណ៍ជូនអតិថិជន']]],
    ['Administration & HR', 'រដ្ឋបាល និងធនធានមនុស្ស', [['Meetings', 'ការប្រជុំ'], ['Employee coordination', 'ការសម្របសម្រួលបុគ្គលិក'], ['Training', 'ការបណ្តុះបណ្តាល'], ['Document preparation', 'ការរៀបចំឯកសារ'], ['Administrative follow-up', 'ការតាមដានការងាររដ្ឋបាល']]],
    ['Finance', 'ហិរញ្ញវត្ថុ', [['Payment verification', 'ការផ្ទៀងផ្ទាត់ការទូទាត់'], ['Invoice checking', 'ការត្រួតពិនិត្យវិក្កយបត្រ'], ['Cash reconciliation', 'ការផ្ទៀងផ្ទាត់សាច់ប្រាក់'], ['Financial documentation', 'ឯកសារហិរញ្ញវត្ថុ']]],
    ['General Work', 'ការងារទូទៅ', [['Workplace organization', 'ការរៀបចំកន្លែងធ្វើការ'], ['Cleaning', 'ការសម្អាត'], ['Equipment preparation', 'ការរៀបចំឧបករណ៍'], ['Other assigned work', 'ការងារផ្សេងទៀតដែលបានប្រគល់']]],
];

$incident = [
    ['Customer Issues', 'បញ្ហាអតិថិជន', [
        ['Service dissatisfaction', 'មិនពេញចិត្តសេវាកម្ម'],
        ['Price complaint', 'តវ៉ាតម្លៃ'],
        ['Phone problem after purchase', 'បញ្ហាទូរសព្ទក្រោយទិញ'],
        ['Warranty issue', 'បញ្ហាការធានា'],
        ['Customer wants to sell a phone', 'ភ្ញៀវចង់លក់ទូរសព្ទ'],
        ['Buyback request declined', 'ហាងមិនអាចទិញចូល'],
        ['Product unavailable', 'ទំនិញដែលភ្ញៀវត្រូវការមិនមាន'],
        ['Customer communication problem', 'បញ្ហាការប្រាស្រ័យទាក់ទង'],
        ['Other customer issue', 'ផ្សេងៗ'],
    ]],
    ['Employee Issues', 'បញ្ហាបុគ្គលិក', [
        ['Late arrival', 'ចូលយឺត'],
        ['Unauthorized absence', 'អវត្តមានមិនមានការអនុញ្ញាត'],
        ['Workplace conflict', 'ទំនាស់ក្នុងការងារ'],
        ['Inappropriate communication', 'ការប្រាស្រ័យទាក់ទងមិនសមរម្យ'],
        ['Gossip or harmful workplace conduct', 'និយាយដើម ឬអាកប្បកិរិយាមិនសមរម្យ'],
        ['Poor customer service', 'មិនរាក់ទាក់អតិថិជន'],
        ['Failure to follow instructions', 'មិនអនុវត្តតាមការណែនាំ'],
        ['Damage to store property', 'ធ្វើខូចទ្រព្យសម្បត្តិហាង'],
        ['Damage to customer property', 'ធ្វើខូចទ្រព្យសម្បត្តិអតិថិជន'],
        ['Suspected dishonest conduct', 'សង្ស័យអំពើមិនស្មោះត្រង់'],
        ['Other employee issue', 'ផ្សេងៗ'],
    ]],
    ['Product & Stock Issues', 'បញ្ហាទំនិញ និងស្តុក', [
        ['Product shortage', 'ទំនិញខ្វះ'], ['Damaged product', 'ទំនិញខូច'], ['Missing inventory', 'ទំនិញបាត់ពីស្តុក'], ['Stock quantity mismatch', 'បរិមាណស្តុកមិនត្រូវគ្នា'], ['Wrong product model', 'ម៉ូដែលទំនិញមិនត្រឹមត្រូវ'],
        ['Product quality issue', 'បញ្ហាគុណភាពទំនិញ'], ['Stock transfer problem', 'បញ្ហាផ្ទេរស្តុក'], ['Product received in incorrect condition', 'ទំនិញទទួលបានក្នុងស្ថានភាពមិនត្រឹមត្រូវ'], ['Other inventory issue', 'បញ្ហាស្តុកផ្សេងៗ'],
    ]],
    ['Operational & System Issues', 'បញ្ហាប្រតិបត្តិការ និងប្រព័ន្ធ', [
        ['POS system issue', 'បញ្ហាប្រព័ន្ធ POS'], ['HRMS system issue', 'បញ្ហាប្រព័ន្ធ HRMS'], ['Network issue', 'បញ្ហាបណ្ដាញ'], ['Delivery issue', 'បញ្ហាដឹកជញ្ជូន'], ['Shift handover issue', 'បញ្ហាប្រគល់វេន'],
        ['Equipment issue', 'បញ្ហាឧបករណ៍'], ['Workplace facility issue', 'បញ្ហាបរិក្ខារកន្លែងធ្វើការ'], ['Internal coordination issue', 'បញ្ហាសម្របសម្រួលផ្ទៃក្នុង'], ['Other operational issue', 'បញ្ហាប្រតិបត្តិការផ្សេងៗ'],
    ]],
    ['Financial Issues', 'បញ្ហាហិរញ្ញវត្ថុ', [
        ['Incorrect payment', 'ការទូទាត់មិនត្រឹមត្រូវ'], ['Cash discrepancy', 'ភាពខុសគ្នានៃសាច់ប្រាក់'], ['Incorrect selling price', 'តម្លៃលក់មិនត្រឹមត្រូវ'], ['Invoice discrepancy', 'ភាពខុសគ្នានៃវិក្កយបត្រ'], ['Customer payment issue', 'បញ្ហាការទូទាត់របស់អតិថិជន'],
        ['Installment issue', 'បញ្ហាបង់រំលស់'], ['Supplier payment issue', 'បញ្ហាទូទាត់ជូនអ្នកផ្គត់ផ្គង់'], ['Other financial issue', 'បញ្ហាហិរញ្ញវត្ថុផ្សេងៗ'],
    ]],
    ['Other Incidents', 'បញ្ហាផ្សេងៗ', [['Other incident', 'ហេតុការណ៍ផ្សេងៗ']]],
];

$field = static fn ($key, $en, $km, $type = 'text') => [
    'key' => $key, 'label_en' => $en, 'label_km' => $km, 'type' => $type, 'required' => false,
];
$fields = [
    'Customer Issues' => [
        $field('customer', 'Customer name', 'ឈ្មោះអតិថិជន'),
        $field('contact', 'Customer contact', 'ទំនាក់ទំនងអតិថិជន'),
        $field('device', 'Product or device model', 'ម៉ូដែលទំនិញ ឬឧបករណ៍'),
        $field('invoice', 'Invoice reference', 'លេខយោងវិក្កយបត្រ'),
    ],
    'Employee Issues' => [
        $field('employee', 'Employee involved', 'បុគ្គលិកពាក់ព័ន្ធ'),
        $field('witnesses', 'Witnesses', 'សាក្សី', 'multiline'),
    ],
    'Product & Stock Issues' => [
        $field('product', 'Product', 'ទំនិញ'), $field('sku', 'SKU', 'លេខកូដទំនិញ'),
        $field('quantity', 'Quantity affected', 'ចំនួនដែលរងផលប៉ះពាល់', 'number'),
        $field('movement', 'Stock movement reference', 'លេខយោងចលនាស្តុក'),
    ],
    'Financial Issues' => [$field('transaction', 'Transaction reference', 'លេខយោងប្រតិបត្តិការ')],
];

foreach (['activity' => $activity, 'incident' => $incident] as $kind => $catalog) {
    foreach ($catalog as $position => [$en, $km, $children]) {
        $parent = \Illuminate\Support\Facades\DB::table('daily_record_categories')->insertGetId([
            'kind' => $kind, 'name_en' => $en, 'name_km' => $km, 'position' => $position,
            'sensitive' => $en === 'Employee Issues', 'fields' => json_encode($fields[$en] ?? [], JSON_THROW_ON_ERROR),
        ]);
        foreach ($children as $order => $label) {
            [$childEn, $childKm] = is_array($label) ? $label : [$label, $label];
            $extra = match ($childEn) {
                'Buyback request declined' => [[
                    'key' => 'buyback_reason', 'label_en' => 'Reason the buyback was declined',
                    'label_km' => 'មូលហេតុដែលមិនអាចទិញចូល', 'type' => 'select', 'required' => true,
                    'options' => ['Product not originally purchased from the store', 'Product condition', 'Price disagreement',
                        'Product not accepted under store policy', 'Other reason'],
                ]],
                'Phone problem after purchase', 'Warranty issue' => [
                    $field('brand', 'Device brand', 'ម៉ាកឧបករណ៍'),
                    $field('serial', 'IMEI or serial if needed', 'លេខ IMEI ឬលេខស៊េរី'),
                    $field('condition', 'Device condition', 'ស្ថានភាពឧបករណ៍'),
                    $field('diagnosis', 'Diagnosis', 'ការវិនិច្ឆ័យ', 'multiline'),
                ],
                default => [],
            };
            \Illuminate\Support\Facades\DB::table('daily_record_categories')->insert([
                'kind' => $kind, 'parent_id' => $parent, 'name_en' => $childEn, 'name_km' => $childKm,
                'position' => $order, 'fields' => json_encode($extra, JSON_THROW_ON_ERROR),
            ]);
        }
    }
}
