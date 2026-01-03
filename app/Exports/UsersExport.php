<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return User::select('membership_number','username','first_name','last_name','email','phone','address','region','country_id','birthday')->get();
    }

    public function headings(): array
    {
        return ['membership_number','username','first_name','last_name','email','phone','address','region','country_id','birthday'];
    }
}
