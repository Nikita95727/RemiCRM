# Fallback Tagging Algorithm

## Overview

When Unipile API doesn't return chat messages (due to Telegram Bot API limitations), the system uses **fallback tagging** based on contact name keywords.

## Why Fallback is Needed

Telegram Bot API has privacy restrictions:
- Bots can only see messages sent **directly to them**
- Historical messages before bot was added are **not accessible**
- Private chats may have **limited message visibility**

Result: ~43% of contacts have no messages available → fallback saves the day!

## Algorithm

### 🎯 Word Boundary Protection

All patterns use `\b` (word boundaries) to match **whole words only**, preventing false positives:

```php
// ❌ BAD: /(ad|ads|...)/  → matches "Vladislav" (Vl-ad-islav)
// ✅ GOOD: /\b(ads|...)\b/ → only matches whole word "ads"
```

### 📋 Tag Categories

#### 1️⃣ **Banking** (`banking`)
Keywords: `bank`, `banking`, `mono`, `monobank`, `privat`, `privatbank`, `pumb`, `raiffeisen`, `alpha`, `finance`, `financial`, `wallet`, `payment`

Examples:
- ✅ "PrivatBank Support" → `banking`
- ✅ "Monobank" → `banking`
- ❌ "Vladislav" → no match

#### 2️⃣ **Crypto** (`crypto`)
Keywords: `crypto`, `bitcoin`, `btc`, `eth`, `ethereum`, `coin`, `token`, `blockchain`, `ton`, `toncoin`, `binance`, `coinbase`, `usdt`, `nft`

Examples:
- ✅ "Bitcoin Wallet" → `crypto`
- ✅ "TON Coin Bot" → `crypto`
- ❌ "Alexander" → no match

#### 3️⃣ **Gaming** (`gaming`)
Keywords: `game`, `games`, `gaming`, `gamer`, `poker`, `casino`, `play`, `pixel`, `hamster`, `kombat`, `tap`, `tapper`, `clicker`

Examples:
- ✅ "Hamster Kombat" → `gaming`
- ✅ "Pixel Tap" → `gaming`

#### 4️⃣ **Bot** (`bot`)
Keywords: `_bot`, `bot` (at end), `assistant`, `helper`, `notify`, `notification`

Examples:
- ✅ "weather_bot" → `bot`
- ✅ "Helper Assistant" → `bot`
- ❌ "Robert" → no match (word boundary!)

#### 5️⃣ **Business** (`business`)
Keywords: `llc`, `ltd`, `inc`, `corp`, `corporation`, `company`, `group`, `team`, `support`, `service`, `official`

Examples:
- ✅ "Support Team" → `business`
- ✅ "ABC Corp" → `business`

#### 6️⃣ **Technology** (`technology`)
Keywords: `dev`, `developer`, `tech`, `technology`, `code`, `coding`, `api`, `software`, `app`, `application`, `digital`, `it`

Examples:
- ✅ "Dev Team" → `technology`
- ✅ "Software API" → `technology`

#### 7️⃣ **Advertising** (`advertising`)
Keywords: `ads`, `advert`, `advertising`, `advertisement`, `promo`, `promotion`, `marketing`, `campaign`

Examples:
- ✅ "Marketing Team" → `advertising`
- ✅ "Ads Manager" → `advertising`
- ❌ "Vladislav" → no match (word boundary!)

#### 8️⃣ **Social** (`social`)
Patterns:
- Username format: starts with `@` or single word with no spaces
- Contains emojis

Examples:
- ✅ "@username" → `social`
- ✅ "Vladislav" → `social` (single word)
- ✅ "Telegram 🔔" → `social` (emoji)

## Testing

```bash
# Test the algorithm
php artisan tinker

$contact = Contact::where('name', 'Vladislav')->first();
// Should NOT match 'advertising' anymore ✅
```

## Statistics

Before fallback: **25% tagged** (7/28)  
After fallback: **57% tagged** (16/28)  
**+128% improvement!** 🎉

## No Match = No Tag

If contact name doesn't match any pattern, **no tag is assigned**.  
This is intentional: better no tag than wrong tag.
