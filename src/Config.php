<?php

namespace Polymarket\ClobClient;

class Config
{
    // Note: Amoy (80002) V2 contract addresses are not yet publicly documented.
    // These are V1 addresses and may not work with V2 signing.
    private const AMOY_CONTRACTS = [
        'exchange' => '0xdFE02Eb6733538f8Ea35D585af8DE5958AD99E40',
        'negRiskAdapter' => '0xd91E80cF2E7be2e162c6513ceD06f1dD0dA35296',
        'negRiskExchange' => '0xC5d563A36AE78145C45a50134d48A1215220f80a',
        'collateral' => '0x9c4e1703476e875070ee25b56a58b008cfb8fa78',
        'conditionalTokens' => '0x69308FB512518e39F9b16112fA8d994F4e2Bf8bB',
    ];

    // V2 contracts (deployed April 2026). Collateral is now pUSD (proxy).
    private const MATIC_CONTRACTS = [
        'exchange' => '0xE111180000d2663C0091e4f400237545B87B996B',
        'negRiskAdapter' => '0xd91E80cF2E7be2e162c6513ceD06f1dD0dA35296',
        'negRiskExchange' => '0xe2222d279d744050d28e00520010520000310F59',
        'collateral' => '0xC011a7E12a19f7B1f670d46F03B03f3342E82DFB',
        'conditionalTokens' => '0x4D97DCd97eC945f40cF65F87097ACe5EA0476045',
    ];

    public const COLLATERAL_TOKEN_DECIMALS = 6;
    public const CONDITIONAL_TOKEN_DECIMALS = 6;

    public static function getContractConfig(int $chainId): array
    {
        return match ($chainId) {
            137 => self::MATIC_CONTRACTS,
            80002 => self::AMOY_CONTRACTS,
            default => throw new \InvalidArgumentException('Invalid network'),
        };
    }
}
