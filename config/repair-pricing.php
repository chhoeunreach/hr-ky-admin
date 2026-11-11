<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Repair Price Suggestion
    |--------------------------------------------------------------------------
    |
    | Used by the repair price forms to suggest a professional selling price
    | from the part cost. The part cost is the primary value: the suggested
    | selling price keeps the part at `part_share` of the sale (rounded up to
    | the nearest `price_step`) and the service fee is the remainder.
    |
    */

    // Target share of the selling price covered by the part cost (0.70 = 70%).
    'part_share' => 0.70,

    // Suggested selling prices are rounded up to this increment (0.50 = $0.50).
    'price_step' => 0.50,

];
