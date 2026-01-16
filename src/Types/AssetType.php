<?php

namespace Polymarket\ClobClient\Types;

enum AssetType: string
{
    case COLLATERAL = 'COLLATERAL';
    case CONDITIONAL = 'CONDITIONAL';
}
