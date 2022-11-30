<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;

class PaymentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Payment::create([
                'amount'	=> 102500,
                'reff'	    => 2000837452,
                'name'	    => "Bagus Wahyu Prabakti",
                'code'	    => "8834081854323334",
                'status'	=> "paid",
                'expired'	=> "2021-07-28T09:12:48+07:00",
        ]);
    }
}
