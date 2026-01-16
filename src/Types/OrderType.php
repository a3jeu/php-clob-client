<?php

namespace Polymarket\ClobClient\Types;

enum OrderType: string
{
    case GTC = 'GTC'; // Good Till Cancel
    case FOK = 'FOK'; // Fill or Kill
    case GTD = 'GTD'; // Good Till Date
    case FAK = 'FAK'; // Fill and Kill
}
