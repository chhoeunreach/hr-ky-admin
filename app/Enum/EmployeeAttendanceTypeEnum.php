<?php

namespace App\Enum;

enum EmployeeAttendanceTypeEnum:string
{
    case wifi = 'wifi';
    case nfc = 'nfc';
    case qr = 'qr';
    case qr_pass = 'qr_pass';
    case biometric = 'biometric';
    case face = 'face';
}
