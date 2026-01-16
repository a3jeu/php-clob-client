<?php

namespace Polymarket\ClobClient;

class Endpoints
{
    // Server Time
    public const TIME = '/time';

    // API Key endpoints
    public const CREATE_API_KEY = '/auth/api-key';
    public const GET_API_KEYS = '/auth/api-keys';
    public const DELETE_API_KEY = '/auth/api-key';
    public const DERIVE_API_KEY = '/auth/derive-api-key';
    public const CLOSED_ONLY = '/auth/ban-status/closed-only';

    // Readonly API Key endpoints
    public const CREATE_READONLY_API_KEY = '/auth/readonly-api-key';
    public const GET_READONLY_API_KEYS = '/auth/readonly-api-keys';
    public const DELETE_READONLY_API_KEY = '/auth/readonly-api-key';
    public const VALIDATE_READONLY_API_KEY = '/auth/validate-readonly-api-key';

    // Builder API Key endpoints
    public const CREATE_BUILDER_API_KEY = '/auth/builder-api-key';
    public const GET_BUILDER_API_KEYS = '/auth/builder-api-key';
    public const REVOKE_BUILDER_API_KEY = '/auth/builder-api-key';

    // Markets
    public const GET_SAMPLING_SIMPLIFIED_MARKETS = '/sampling-simplified-markets';
    public const GET_SAMPLING_MARKETS = '/sampling-markets';
    public const GET_SIMPLIFIED_MARKETS = '/simplified-markets';
    public const GET_MARKETS = '/markets';
    public const GET_MARKET = '/markets/';
    public const GET_ORDER_BOOK = '/book';
    public const GET_ORDER_BOOKS = '/books';
    public const GET_MIDPOINT = '/midpoint';
    public const GET_MIDPOINTS = '/midpoints';
    public const GET_PRICE = '/price';
    public const GET_PRICES = '/prices';
    public const GET_SPREAD = '/spread';
    public const GET_SPREADS = '/spreads';
    public const GET_LAST_TRADE_PRICE = '/last-trade-price';
    public const GET_LAST_TRADES_PRICES = '/last-trades-prices';
    public const GET_TICK_SIZE = '/tick-size';
    public const GET_NEG_RISK = '/neg-risk';
    public const GET_FEE_RATE = '/fee-rate';

    // Order endpoints
    public const POST_ORDER = '/order';
    public const POST_ORDERS = '/orders';
    public const CANCEL_ORDER = '/order';
    public const CANCEL_ORDERS = '/orders';
    public const GET_ORDER = '/data/order/';
    public const CANCEL_ALL = '/cancel-all';
    public const CANCEL_MARKET_ORDERS = '/cancel-market-orders';
    public const GET_OPEN_ORDERS = '/data/orders';
    public const GET_TRADES = '/data/trades';
    public const IS_ORDER_SCORING = '/order-scoring';
    public const ARE_ORDERS_SCORING = '/orders-scoring';

    // Price history
    public const GET_PRICES_HISTORY = '/prices-history';

    // Notifications
    public const GET_NOTIFICATIONS = '/notifications';
    public const DROP_NOTIFICATIONS = '/notifications';

    // Balance
    public const GET_BALANCE_ALLOWANCE = '/balance-allowance';
    public const UPDATE_BALANCE_ALLOWANCE = '/balance-allowance/update';

    // Live activity
    public const GET_MARKET_TRADES_EVENTS = '/live-activity/events/';

    // Rewards
    public const GET_EARNINGS_FOR_USER_FOR_DAY = '/rewards/user';
    public const GET_TOTAL_EARNINGS_FOR_USER_FOR_DAY = '/rewards/user/total';
    public const GET_LIQUIDITY_REWARD_PERCENTAGES = '/rewards/user/percentages';
    public const GET_REWARDS_MARKETS_CURRENT = '/rewards/markets/current';
    public const GET_REWARDS_MARKETS = '/rewards/markets/';
    public const GET_REWARDS_EARNINGS_PERCENTAGES = '/rewards/user/markets';

    // Builder endpoints
    public const GET_BUILDER_TRADES = '/builder/trades';

    // Heartbeats
    public const POST_HEARTBEAT = '/v1/heartbeats';

    // RFQ
    public const CREATE_RFQ_REQUEST = '/rfq/request';
    public const CANCEL_RFQ_REQUEST = '/rfq/request';
    public const GET_RFQ_REQUESTS = '/rfq/data/requests';
    public const CREATE_RFQ_QUOTE = '/rfq/quote';
    public const CANCEL_RFQ_QUOTE = '/rfq/quote';
    public const RFQ_REQUESTS_ACCEPT = '/rfq/request/accept';
    public const RFQ_QUOTE_APPROVE = '/rfq/quote/approve';
    public const GET_RFQ_REQUESTER_QUOTES = '/rfq/data/requester/quotes';
    public const GET_RFQ_QUOTER_QUOTES = '/rfq/data/quoter/quotes';
    public const GET_RFQ_BEST_QUOTE = '/rfq/data/best-quote';
    public const RFQ_CONFIG = '/rfq/config';
}
