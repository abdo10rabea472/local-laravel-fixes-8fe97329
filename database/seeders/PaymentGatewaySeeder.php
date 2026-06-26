<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        $gateways = [
            ['code' => 'cod',           'driver' => 'cod',          'name' => 'الدفع عند الاستلام',  'description' => 'ادفع نقدًا عند استلام الطلب', 'is_active' => true,  'sandbox' => false],
            ['code' => 'paymob',        'driver' => 'Paymob',       'name' => 'Paymob',              'description' => 'بطاقات الائتمان ومحافظ Paymob من إعداد واحد'],
            ['code' => 'paymob_wallet', 'driver' => 'PaymobWallet', 'name' => 'محافظ Paymob',        'description' => 'مدمجة داخل بوابة Paymob الرئيسية'],
            ['code' => 'fawry',         'driver' => 'Fawry',        'name' => 'فوري',                'description' => 'الدفع عبر فوري'],
            ['code' => 'kashier',       'driver' => 'Kashier',      'name' => 'Kashier',             'description' => 'بطاقات عبر Kashier'],
            ['code' => 'hyperpay',      'driver' => 'HyperPay',     'name' => 'HyperPay',            'description' => 'بطاقات / مدى / Apple Pay'],
            ['code' => 'paypal',        'driver' => 'PayPal',       'name' => 'PayPal',              'description' => 'الدفع عبر PayPal'],
            ['code' => 'stripe',        'driver' => 'Stripe',       'name' => 'Stripe',              'description' => 'بطاقات عبر Stripe'],
            ['code' => 'tap',           'driver' => 'Tap',          'name' => 'Tap',                 'description' => 'بوابة Tap'],
            ['code' => 'opay',          'driver' => 'Opay',         'name' => 'OPay',                'description' => 'بوابة OPay'],
            ['code' => 'paytabs',       'driver' => 'PayTabs',      'name' => 'PayTabs',             'description' => 'بوابة PayTabs'],
            ['code' => 'thawani',       'driver' => 'Thawani',      'name' => 'Thawani',             'description' => 'بوابة Thawani'],
            ['code' => 'telr',          'driver' => 'Telr',         'name' => 'Telr',                'description' => 'بوابة Telr'],
            ['code' => 'clickpay',      'driver' => 'ClickPay',     'name' => 'ClickPay',            'description' => 'بوابة ClickPay'],
            ['code' => 'binance',       'driver' => 'Binance',      'name' => 'Binance Pay',         'description' => 'الدفع بالعملات الرقمية عبر Binance'],
            ['code' => 'nowpayments',   'driver' => 'NowPayments',  'name' => 'NowPayments',         'description' => 'مدفوعات العملات الرقمية'],
            ['code' => 'payeer',        'driver' => 'Payeer',       'name' => 'Payeer',              'description' => 'بوابة Payeer'],
            ['code' => 'perfectmoney',  'driver' => 'PerfectMoney', 'name' => 'Perfect Money',       'description' => 'بوابة Perfect Money'],
        ];

        foreach ($gateways as $i => $g) {
            PaymentGateway::updateOrCreate(
                ['code' => $g['code']],
                array_merge([
                    'is_active' => false,
                    'sandbox'   => true,
                    'position'  => $i + 1,
                    'config'    => [],
                ], $g)
            );
        }
    }
}
