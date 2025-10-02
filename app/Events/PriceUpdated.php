<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class PriceUpdated implements ShouldBroadcast
{
    use SerializesModels;

    public $symbol;
    public $buy_price;
    public $sell_price;

    public function __construct($symbol, $buy_price, $sell_price)
    {
        $this->symbol = $symbol;
        $this->buy_price = $buy_price;
        $this->sell_price = $sell_price;
    }

    public function broadcastOn()
    {
        return new Channel('prices'); // قناة عامة
    }
}
