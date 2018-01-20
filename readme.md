# Yobit Api implementation

This is a implementation of Yobit Api on PHP.

Api documentation: https://yobit.net/en/api

# Installation

#### 1. Append to composer package requires
```
composer require olegstyle/yobitapi
```

#### 2. Use it `^_^`

# How to use?

Like a original api package have public api (`\OlegStyle\YobitApi\YobitPublicApi`)
and trade api (`\OlegStyle\YobitApi\YobitTradeApi`)

### Public Api usage

```
$pairs = [
   new \OlegStyle\YobitApi\CurrencyPair('btc', 'eth'),
   new \OlegStyle\YobitApi\CurrencyPair('bch, 'btc'),
];
$publicApi = new \OlegStyle\YobitApi\YobitPublicApi();
$publicApi->getInfo(); // get info about all pairs
$publicApi->getTickers($pairs); // limit - 50 pairs
$publicApi->getTicker('btc', 'eth');
$publicApi->getDepths($pairs); // limit - 50 pairs
$publicApi->getDepth('btc', 'eth');
$publicApi->getTrades($pairs); // limit - 50 pairs
$publicApi->getTrade('btc', 'eth');
```

### Trade Api usage

Make sure that you are using different public/secret keys in development and production

```
$publicKey = 'YOR_PUBLIC_KEY'; 
$privateKey = 'YOR_PRIVATE_KEY'; // or secret key

$tradeApi = new \OlegStyle\YobitApi\YobitTradeApi($publicKey, $privateKey);
$tradeApi->getInfo(); // Method returns information about user's balances and priviledges of API-key as well as server time.
$tradeApi->trade( // Method that allows creating new orders for stock exchange trading
  new \OlegStyle\YobitApi\CurrencyPair('bch, 'btc'), // pair
  \OlegStyle\YobitApi\Enums\TransactionTypeEnum::BUY, // type of trade. can be: TransactionTypeEnum::BUY or TransactionTypeEnum::SELL
  0.023, // rate
  0.1 // amount 
);
$tradeApi->getActiveOrders( // Method returns list of user's active orders (trades)
  new \OlegStyle\YobitApi\CurrencyPair('bch, 'btc') // pair
);
$tradeApi->getOrderInfo($orderId); // Method returns detailed information about the chosen order (trade)
$tradeApi->cancelOrder($orderId); // Method cancells the chosen order
```
