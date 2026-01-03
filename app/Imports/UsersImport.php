<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UsersImport implements ToModel, WithHeadingRow, WithChunkReading, SkipsEmptyRows, WithValidation
{
    public function model(array $row)
    {
        // // Check if created_at exists and convert the date format
        // if (isset($row['created_at']) && !empty($row['created_at'])) {
        //     try {
        //         $created_at = Carbon::createFromFormat('m/d/Y H:i:s', $row['created_at'])->format('Y-m-d H:i:s');
        //     } catch (\Exception $e) {
        //         try {
        //             $created_at = Carbon::createFromFormat('m/d/Y H:i', $row['created_at'])->format('Y-m-d H:i:s');
        //         } catch (\Exception $e) {
        //             // If both formats fail, use current date-time as fallback
        //             $created_at = Carbon::now()->format('Y-m-d H:i:s');
        //         }
        //     }
        // } else {
        //     $created_at = Carbon::now()->format('Y-m-d H:i:s');
        // }

        // $updated_at = Carbon::now()->format('Y-m-d H:i:s');

        // // Check if email is present
        // if (isset($row['email']) && !empty($row['email'])) {
        //     $user = User::where('email', $row['email'])->first();

        //     if ($user) {
        //         // Generate membership number
        //         $prefix = 'PAM';
        //         $numericPart = str_pad($user->id, 4, '0', STR_PAD_LEFT);
        //         $membershipNumber = $prefix . $numericPart;

        //         // Update existing record
        //         $response = $user->update([
        //             'username' => $row['email'],
        //             'updated_at' => $updated_at,
        //         ]);
        //         Log::info("Record Exists Update", [$response]);

        //         event(new Registered($user));

        //     } else {
        //         // Create new record
        //         $response = new User([
        //             'email' => $row['email'],
        //             'username' => $row['email'],
        //             'created_at' => $created_at,
        //             'updated_at' => $updated_at,
        //         ]);

        //         Log::info("Record Does Not Exist Create", [$response]);

        //         // Save the record to the database
        //         // $user->save();

        //         event(new Registered($user));

        //         Log::info("USER Record Created", [$user]);

        //         return $response;
        //     }
        // }


        // Fetch the data from mytable
        $rows = DB::table('mytable')->get();

        foreach ($rows as $row) {
            // Convert created_at to Y-m-d H:i:s format
            try {
                $created_at = Carbon::createFromFormat('m/d/Y H:i:s', $row->created_at)->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                $created_at = Carbon::now()->format('Y-m-d H:i:s'); // Fallback to current date-time if conversion fails
            }

            // Prepare data for insertion
            $data = [
                'created_at' => $created_at,
                'email' => $row->email,
                'username' => $row->email,
                'password' => $row->password,
                'first_name' => $row->first_name,
                // 'middle_name' => $row->middle_name,
                'last_name' => $row->last_name,
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'), // Assuming you want to set updated_at to current time
            ];

            // Insert data into vg_users table
            DB::table('vg_users')->insert($data);
        }

        echo "Data inserted successfully!";

    }

    public function chunkSize(): int
    {
        $email_addresses = array(
            'albertsoko39@gmail.com',
            'anitachiumia11@gmail.com',
            'awiriesther@gmail.com',
            'beautiebanda88@gmail.com',
            'cecilianjolomole@gmail.com',
            'machemaryann@gmail.com',
            'njolomolec@gmail.com',
            'chikuzingano50@gmail.com',
            'clarawawanya31@gmail.com',
            'deborahphiri76@gmail.com',
            'emilytembo8@gmail.com',
            'munthaliemily@gmail.com',
            'daisy333pk@gmail.com',
            'evelynmalunga@gmail.com',
            'falesspedro@gmail.com',
            'anishaajana@gmail.com',
            'nankhomaflo@yahoo.co.uk',
            'franksaidi0101@gmail.com',
            'nyazimba@gmail.com',
            'bmwanakhu07@gmail.com',
            'giftchikapa10@gmail.com',
            'gracebwanali5@gmail.com',
            'heathermalopa@gmail.com',
            'hopechipinda@gmail.com',
            'izzymazia@gmail.com',
            'jalen4ever05@gmail.com',
            'jkunts@googlemail.com',
            'kanaventisteven@gmail.com',
            'julichakana@gmail.com',
            'jsingini37@gmail.com',
            'katemsosa1986@gmail.com',
            'lthembachako@gmail.com',
            'tasshachilembwe@gmail.com',
            'lucykanaventi@gmail.com',
            'madamak37.mm@gmail.com',
            'mnmafu28@gmail.com',
            'sokomandie@gmail.com',
            'kasusubertha812@gmail.com',
            'mercymopiha@yahoo.com',
            'salimamercy@gmail.com',
            'melissamwaphoka07@gmail.com',
            'mgsaiti@gmail.com',
            'nyangunganga@gmail.com',
            'soutermoira0@gmail.com',
            'mphatsodzinkamban@gmail.com',
            'mwalie4@gmail.com',
            'mmpambe@gmail.com',
            'chitsampi@aol.com',
            'nekumatikanya245@gmail.com',
            'osambakunsi@gmail.com',
            'pkasiyamphanje85@gmail.com',
            'phiriedith10@gmail.com',
            'renataseg5@yahoo.co.uk',
            'mphambahrhodwell@gmail.com',
            'ronasaybeautiful@gmail.com',
            'saragondwe@hotmail.com',
            '15shapilhellen@gmail.com',
            'silvanodombe75@gmail.com',
            'mwanzasophie@hotmail.co.uk',
            'thokozirenjoka02@gmail.com',
            'sbsaiti@gmail.com',
            'tamarachvura@gmail.com',
            'tiffeharris@yahoo.co.uk',
            'mollykachipande@gmail.com',
            'jhtaiw21@gmail.com',
            'tiwonge80@googlemail.com',
            'acjuwls@gmail.com',
            'agathamazengera867@gmail.com',
            'itsfunnymuffin@gmail.com',
            'lisalida159@gmail.com',
            'chidakwanialice25@gmail.com',
            'ndawanacatherine38@gmail.com',
            'chanazisinalo@gmail.com',
            'chawezisuzank@gmail.com',
            'natembomakwinja@gmail.com',
            'chisomo_c@yahoo.com',
            'chisomomakwale2@gmail.com',
            'cmillinyu16@gmail.com',
            'claire.lewis93@gmail.com',
            'clifford.sulumba@gmail.com',
            'lungudaria223@gmail.com',
            'dklsby.27@gmail.com',
            'egnatkazembe@gmail.com',
            'eleanormeki@googlemail.com',
            'emkukuma@yahoo.co.uk',
            'enettrhymer23@gmail.com',
            'dauyaephraim@gmail.com',
            'faith1nkhoma@gmail.com',
            'fsolo753namiyango@gmail.com',
            'filozasheriff1@gmail.com',
            'gchinoko@gmail.com',
            'gertrudesulumba@gmail.com',
            'grace.mwanza@gmail.com',
            'g.chunga@yahoo.com',
            'tucker.grace014@gmail.com',
            'weddndeco@gmail.com',
            'harrietsukasuka@gmail.com',
            'chiwandaj@gmail.com',
            'loveysamuel47@gmail.com',
            'sejukemi@gmail.com',
            'acjudi@googlemail.com',
            'bittywilson945@gmail.com',
            'simbotalessa20@gmail.com',
            'martinmvula3@gmail.com',
            'lchilemba@gmail.com',
            'lusungumoyo123@gmail.com',
            'madalitsomc@yahoo.co.uk',
            'mtalamaba@gmail.com',
            'merciechavula@gmail.com',
            'chinkhumbaeled@gmail.com',
            'lunguqanda@gmail.com',
            'nyanyiwenkhata@gmail.com',
            'olivechunga3@gmail.com',
            'pamqg@hotmail.com',
            'pngundende@yahoo.co.uk',
            'phiogug@gmail.com',
            'prince.chibwana@gmail.com',
            'samshalomc@gmail.com',
            'sharonmalunga58@gmail.com',
            'sokoshupe29@gmail.com',
            'pamtsinje@gmail.com',
            'stellawachepa27@gmail.com',
            'kagwaspraggsusan@gmail.com',
            'tamandanazitwere@googlemail.com',
            'temwanii2012@gmail.com',
            'tendairashid371@gmail.com',
            'krazytha@gmail.com',
            'namalunga2@yahoo.co.uk',
            'veeshaba@googlemail.com',
            'ngwendev@gmail.com',
            'ysambani1@gmail.com',
            'znamanja@gmail.com',
            'anderson.kazembe@googlemail.com',
            'angymwaya@gmail.com',
            'lisamkoko@gmail.com',
            'barbarakalea@gmail.com',
            'bernaguwah@gmail.com',
            'bckauma@gmail.com',
            'carolinegross2@gmail.com',
            'charityali86@gmail.com',
            'chikosworld0@gmail.com',
            'dchrissie8@gmail.com',
            'christinasikwese28@gmail.com',
            'debsbanda@googlemail.com',
            'malungadiana1@gmail.com',
            'donnyondo@gmail.com',
            'mteteeliza@gmail.com',
            'nyangu72@gmail.com',
            'talamaesther@gmail.com',
            'pandekha@gmail.com',
            'madhartley72@gmail.com',
            'fwinona2@yahoo.co.uk',
            'florence.nseula1@gmail.com',
            'stembo76@gmail.com',
            'ndolezimusongole@gmail.com',
            'geraldnamwaza@gmail.com',
            'milazitrudy@gmail.com',
            'cgm5268@gmail.com',
            'matembag73@gmail.com',
            'mbongichiundira@gmail.com',
            'jacqulline780@gmail.com',
            'jthamangira@gmail.com',
            'janetchawinga@gmail.com',
            'khomboka@gmail.com',
            'jkaunde6@gmail.com',
            'ngondojoseph1977@gmail.com',
            'jkanchowa@yahoo.co.uk',
            'mwaijude@gmail.com',
            'judithakuzikechikhula@gmail.com',
            'kamalowase@gmail.com',
            'kettienyirenda02@gmail.com',
            'kululikak@gmail.com',
            'sukasukalexman@gmail.com',
            'nyausisya@gmail.com',
            'lucynjawala12@gmail.com',
            'mnyengani@gmail.com',
            'm.phikiso@gmail.com',
            'lusumac3@gmail.com',
            'nangwale45@gmail.com',
            'mimtie4life@gmail.com',
            'charitykaunde80@gmail.com',
            'wendiegondwe@gmail.com',
            'ngomapatricia52@gmail.com',
            'openjichitakunye@gmail.com',
            'aginna07@gmail.com',
            'renniemranga1986@gmail.com',
            'shentonbanda@googlemail.com',
            'naguga@hotmail.com',
            'tafwa2017@gmail.com',
            'tammymakwele@gmail.com',
            'tamandap@gmail.com',
            'tam.chisambo@gmail.com',
            'ttayahk@gmail.com',
            'temwachimkandawire@gmail.com',
            'thandieqg@gmail.com',
            'namoyo15@gmail.com',
            'ngwiratowera@gmail.com',
            'ulandawawo@gmail.com',
            'chikhula@gmail.com',
            'kamundiy@gmail.com',
            'zakeyu@gmail.com',
            'agneskandodo1@gmail.com',
            'aidazephaniah@gmail.com',
            'amandakulugomba@googlemail.com',
            'anita1972uk@yahoo.co.uk',
            'arthurchiumia@gmail.com',
            'asantemtalimanja80@gmail.com',
            'mndololaura@gmail.com',
            'abitijenala@yahoo.co.uk',
            'bmhango2010@gmail.com',
            'wakudyanayejer@gmail.com',
            'cathychunda@googlemail.com',
            'makungulan@gmail.com',
            'carolmkiwa@hotmail.com',
            'cfredsonphiri@gmail.com',
            'chawanangandeya2@gmail.com',
            'clarachiumia@gmail.com',
            'ladyndave08@gmail.com',
            'deborahmagwira@gmail.com',
            'dodoma39@gmail.com',
            'emmanueltithini@gmail.com',
            'leonnings77@gmail.com',
            'florencelugode@gmail.com',
            'gmarufu8@gmail.com',
            'gmartwina@gmail.com',
            'glorymair@gmail.com',
            'gbh5@hotmail.com',
            'gotani02@gmail.com',
            'blessingkalokola@yahoo.com',
            'gwegwegwe1@gmail.com',
            'fianahand1@gmail.com',
            'happymarykareke@gmail.com',
            'henrykapalamula@gmail.com',
            'hildahswalewisamo@gmail.com',
            'i.chunga@gmail.com',
            'isaiascamama@gmail.com',
            'jeankalema2@gmail.com',
            'jennykalimachao@gmail.com',
            'josephlameck3@gmail.com',
            'jtenthani@gmail.com',
            'karendana127@gmail.com',
            'lilokavangamary@gmail.com',
            'kapembek@rocketmail.com',
            'nancytemwa89@gmail.com',
            'kentemwendele@gmail.com',
            'chavurakhamula@gmail.com',
            'khawadangani@gmail.com',
            'lindiwe1965@gmail.com',
            'lydiahndala12@gmail.com',
            'chandapan9@gmail.com',
            'lwazimavuka@gmail.com',
            'malewasilver2019@gmail.com',
            'francismalunga78@gmail.com',
            'mamgondwe@gmail.com',
            'mapale88@gmail.com',
            'mapeto077@gmail.com',
            'matildahphiri@gmail.com',
            'mazb12@gmail.com',
            'mthupikondoknowell@gmail.com',
            'mwakikhomowashaba@gmail.com',
            'mwalisy@gmail.com',
            'mybanna4u@gmail.com',
            'nyemelezapatrick@gmail.com',
            'phirose18@gmail.com',
            'rosephiri.kamuzu@gmail.com',
            'patricia.comandina@gmail.com',
            'patriciaokeyo@gmail.com',
            'peterchunga34@gmail.com',
            'joycechikuwa20@gmail.com',
            'robinamvula6@gmail.com',
            'davienkosi@gmail.com',
            'salomonsam92@gmail.com',
            'magsalaunyolo@gmail.com',
            'sandraphiri001@gmail.com',
            'schingota@gmail.com',
            'sukasukashila14@gmail.com',
            'syllviasauti@gmail.com',
            'taviraemile@gmail.com',
            'thandwazog@gmail.com',
            'tonyawaileelson@gmail.com',
            'tripo2000@gmail.com',
            'alicesaviwa@hotmail.com',
            'macnkatala@yahoo.co.uk',
            'isabellem.taylor@hotmail.com',
            'ken.dondo@btinternet.com',
            'verinicalynne@btinternet.com',
            'yohanesboston@gmail.com'
        );

        foreach($email_addresses as $email) {

            $user = User::where('email', $email)->first();
            event(new Registered($user));

        }
    }

    public function rules(): array
    {
        return [
            '*.email' => 'required|email',
            '*.created_at' => 'sometimes|date_format:m/d/Y H:i:s|date_format:m/d/Y H:i',
        ];
    }
}
