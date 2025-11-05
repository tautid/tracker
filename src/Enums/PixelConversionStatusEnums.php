<?php

namespace Tautid\Tracker\Enums;

enum PixelConversionStatusEnums: string
{
    case Success = 'succes';
    case Failed = 'failed';
    case Duplicate = 'duplicate';
    case Queued = 'queued';
}
